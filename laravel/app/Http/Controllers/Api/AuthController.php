<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PDO;
use PDOException;
use Firebase\JWT\JWT;  // Add this import
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private function pdo()
    {
        return DB::connection()->getPdo();
    }

    public function login(Request $request)
    {

        try {
            
            $request->validate(['email' => 'required|email', 'password' => 'required|string']);
            
            $pdo = $this->pdo();
            
            $stmt = $pdo->prepare("SELECT id, password FROM workers WHERE email = ? LIMIT 1");
            $stmt->execute([$request->email]);
            
            if ($user = $stmt->fetch()) {
                $role = 'worker';
            } else {
                $stmt = $pdo->prepare("SELECT id, password FROM companies WHERE email = ? LIMIT 1");
                $stmt->execute([$request->email]);
                
                if (!($user = $stmt->fetch())) {
                    return response()->json(['error' => 'Invalid credentials'], 401);
                }
                $role = 'company';
            }
            
            if (!Hash::check($request->password, $user['password'])) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // IMPORTANT: Add JWT_SECRET to your .env file
            $jwtSecret = env('JWT_SECRET');
            if (!$jwtSecret) {
                return response()->json(['error' => 'JWT secret not configured'], 500);
            }

            $token = JWT::encode([
                'sub' => $user['id'],
                'role' => $role,
                'exp' => time() + 86400
            ], $jwtSecret, 'HS256');

            return response()->json([
                'token' => $token,
                'role' => $role
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Login failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function resetPassword(Request $request)
    {

    
        try {
            $request->validate([
                'password' => 'required|string'
            ]);
    
            // 🔐 Get token from Authorization header
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['error' => 'Authorization token missing'], 401);
            }
    
            $jwtSecret = env('JWT_SECRET');
            if (!$jwtSecret) {
                return response()->json(['error' => 'JWT secret not configured'], 500);
            }
    
            // 🔓 Decode token
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
    
            if (!isset($decoded->sub) || !isset($decoded->role)) {
                return response()->json(['error' => 'Invalid token'], 401);
            }
    
            $userId = $decoded->sub;
            $role   = $decoded->role;
    
            $pdo = $this->pdo();
            $hashedPassword = Hash::make($request->password);
    
            // 🔁 Update based on role
            if ($role === 'company') {
    
                $stmt = $pdo->prepare("SELECT id FROM companies WHERE id = ? LIMIT 1");
                $stmt->execute([$userId]);
    
                if (!$stmt->fetch()) {
                    return response()->json(['error' => 'Company not found'], 404);
                }
    
                $update = $pdo->prepare(
                    "UPDATE companies SET password = ?, updated_at = NOW() WHERE id = ?"
                );
                $update->execute([$hashedPassword, $userId]);
    
            } else {
                // default → worker
                $stmt = $pdo->prepare("SELECT id FROM workers WHERE id = ? LIMIT 1");
                $stmt->execute([$userId]);
    
                if (!$stmt->fetch()) {
                    return response()->json(['error' => 'Worker not found'], 404);
                }
    
                $update = $pdo->prepare(
                    "UPDATE workers SET password = ?, updated_at = NOW() WHERE id = ?"
                );
                $update->execute([$hashedPassword, $userId]);
            }
    
            // 🔐 Generate NEW token (same as login)
            $newToken = JWT::encode([
                'sub' => $userId,
                'role' => $role,
                'exp' => time() + 86400
            ], $jwtSecret, 'HS256');
    
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully',
                'role' => $role,
                'token' => $newToken
            ]);
    
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Reset password failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    

    

    
    public function sendMail(Request $request)
    {
 
        
        \Log::info('=== sendMail START ===');
        \Log::info('Request received for email: ' . ($request->email ?? 'NULL'));
    
        try {
            $request->validate([
                'email' => 'required|email'
            ]);
            
            \Log::info('✅ Email validation passed');
    
            $pdo = $this->pdo();
            \Log::info('✅ Database connection established');
    
            // 🔍 Check workers table
            $stmt = $pdo->prepare("SELECT id FROM workers WHERE email = ? LIMIT 1");
            $stmt->execute([$request->email]);
            $user = $stmt->fetch();
            $role = 'worker';
            
            \Log::info('🔍 Workers table query executed');
            \Log::info('Found in workers: ' . ($user ? 'YES' : 'NO'));
    
            // 🔍 Check companies if not found
            if (!$user) {
                \Log::info('⏭ Checking companies table...');
                $stmt = $pdo->prepare("SELECT id FROM companies WHERE email = ? LIMIT 1");
                $stmt->execute([$request->email]);
                $user = $stmt->fetch();
                $role = 'company';
                \Log::info('🔍 Companies table query executed');
                \Log::info('Found in companies: ' . ($user ? 'YES' : 'NO'));
            }
    
            if (!$user) {
                \Log::warning('❌ Email not found in any table: ' . $request->email);
                return response()->json(['error' => 'Email not found'], 404);
            }
    
            \Log::info('✅ User found! ID: ' . $user['id'] . ', Role: ' . $role);
    
            $jwtSecret = env('JWT_SECRET');
            \Log::info('JWT_SECRET loaded: ' . ($jwtSecret ? 'YES' : 'NO'));
    
            // ⏱ Token expires in 5 minutes
            $token = JWT::encode([
                'sub' => $user['id'],
                'role' => $role,
                'exp' => time() + 300
            ], $jwtSecret, 'HS256');
            
            \Log::info('✅ JWT token generated');
    
            $resetLink = "http://localhost:5174/{$role}/reset-password?token={$token}&role={$role}";
            \Log::info('Reset link created: ' . $resetLink);
    
            // 🔥 HTML email
            $mailHtml = <<<HTML
                <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 40px auto; background: #ffffff; border: 1px solid #e1e4e8; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 40px; color: #24292e;">
                    <div style="text-align: center; margin-bottom: 24px;">
                        <h1 style="font-size: 28px; font-weight: 700; margin: 0; color: #4f46e5; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            SmartHire
                        </h1>
                    </div>
                    <h2 style="font-size: 22px; font-weight: 600; margin-bottom: 24px; color: #1f2937;">
                        Réinitialisation de votre mot de passe
                    </h2>
                    <p style="font-size: 16px; line-height: 1.5; color: #4b5563;">
                        Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte. Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe. Ce lien expirera dans 5 minutes.
                    </p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{$resetLink}"
                        style="display: inline-block; padding: 14px 28px; font-size: 16px; font-weight: 600; color: #fff; background: linear-gradient(to right, #4f46e5, #0ea5e9); border-radius: 6px; text-decoration: none; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.4); transition: all 0.3s ease;">
                        Réinitialiser le mot de passe
                        </a>
                    </div>
                    <p style="font-size: 14px; color: #6b7280;">
                        Si vous n'avez pas demandé de réinitialisation de mot de passe, vous pouvez ignorer cet email en toute sécurité. Votre compte est sécurisé.
                    </p>
                    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">
                    <div style="text-align: center;">
                        <p style="font-size: 12px; color: #9ca3af;">
                            © 2026 SmartHire. Tous droits réservés.<br>
                            Bembla, Monastir
                        </p>
                    </div>
                </div>
                HTML;
    
            \Log::info('📧 Email HTML prepared, attempting to send...');
    
            // ✉ Send email - FIXED!
            Mail::html($mailHtml, function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Réinitialisation de votre mot de passe');
            });
            
            \Log::info('✅ Mail sent successfully via Mail facade');
            
            return response()->json([
                'message' => 'Reset password email sent successfully',
                'resetLink' => $resetLink // optional for testing
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $ve) {
            \Log::error('❌ Validation error: ' . json_encode($ve->errors()));
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('❌ Exception in sendMail: ' . $e->getMessage());
            \Log::error('❌ File: ' . $e->getFile());
            \Log::error('❌ Line: ' . $e->getLine());
            \Log::error('❌ Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to send reset email',
                'message' => $e->getMessage()
            ], 500);
        } finally {
            \Log::info('=== sendMail END ===');
        }
    }


    private function getWorkerIdFromToken($token)
    {
        try {
            
            \Log::info('JWT secret in decode:', ['length' => strlen(env('JWT_SECRET')), 'value' => env('JWT_SECRET')]);

            $jwtSecret = env('JWT_SECRET');
            
            \Log::info('JWT Secret check:', [
                'has_secret' => !empty($jwtSecret),
                'secret_length' => strlen($jwtSecret ?? '')
            ]);
            
            if (!$jwtSecret) {
                throw new \Exception('JWT_SECRET not set in .env');
            }
            
            // Decode the token
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
    
            \Log::info('Token decoded:', [
                'sub' => $decoded->sub ?? 'none',
                'role' => $decoded->role ?? 'none',
                'exp' => $decoded->exp ?? 'none'
            ]);
            
            // Check if it's a company
            if (!isset($decoded->role) || $decoded->role !== 'worker') {
                throw new \Exception('Not a worker. Role: ' . ($decoded->role ?? 'none'));
            }
            
            if (!isset($decoded->sub) || empty($decoded->sub)) {
                throw new \Exception('No user ID in token');
            }
            
            // Return worker ID
            return $decoded->sub;
            
        } catch (\Exception $e) {
            \Log::error('Token decode failed:', [
                'error' => $e->getMessage(),
                'token_sample' => substr($token ?? '', 0, 50)
            ]);
            
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }



}