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
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Http;

class PlatformController extends Controller
{
    private function pdo()
    {
        return DB::connection()->getPdo();
    }



public function stats(Request $request)
{
    try {
        $totalWorkers = DB::table('workers')->count();
        $totalCompanies = DB::table('companies')->count();

        $totalJobs = DB::table('posts')->count();
        
        // Get current date for comparison
        $today = now();
        
        // Jobs in progress: deadline is today or in the future
        $jobsInProgress = DB::table('posts')
            ->where('deadline', '>=', $today)
            ->count();
        
        // Jobs expired: deadline is in the past
        $jobsExpired = DB::table('posts')
            ->where('deadline', '<', $today)
            ->count();
            
        $totalApplications = DB::table('test_attempts')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $totalWorkers + $totalCompanies,
                'companies' => $totalCompanies,
                'workers' => $totalWorkers,
                'jobs' => $totalJobs,
                'jobs_in_progress' => $jobsInProgress,
                'jobs_expired' => $jobsExpired,
                'applications' => $totalApplications,
                // Additional metrics for better insights
                'application_rate' => $totalJobs > 0 ? round(($totalApplications / $totalJobs) * 100, 1) : 0,
                'active_ratio' => $totalJobs > 0 ? round(($jobsInProgress / $totalJobs) * 100, 1) : 0,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Unable to retrieve statistics',
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function getsentryFront()
{
    $token   = env('SENTRY_AUTH_TOKEN');
    $orgSlug = env('SENTRY_ORG_SLUG', 'rayen-ib');
    $project = env('SENTRY_FRONT_PROJECT', 'javascript-vue');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->get("https://de.sentry.io/api/0/projects/{$orgSlug}/{$project}/issues/", [
        'limit' => 25,
        'query' => 'is:unresolved',
    ]);

    if ($response->failed()) {
        return response()->json([
            'error'  => 'Sentry API error',
            'status' => $response->status(),
            'detail' => $response->body(),
        ], 500);
    }

    return response()->json($response->json());
}

public function getsentryBack()
{
    $token   = env('SENTRY_AUTH_TOKEN');
    $orgSlug = env('SENTRY_ORG_SLUG', 'rayen-ib');
    $project = env('SENTRY_BACK_PROJECT', 'laravel');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->get("https://de.sentry.io/api/0/projects/{$orgSlug}/{$project}/issues/", [
        'limit' => 25,
        'query' => 'is:unresolved',
    ]);

    if ($response->failed()) {
        return response()->json([
            'error'  => 'Sentry API error',
            'status' => $response->status(),
            'detail' => $response->body(),
        ], 500);
    }

    return response()->json($response->json());
}





/**
     * Get all companies
     */
    public function getAllCompanies()
    {
        try {
            $companies = DB::table('companies')
                ->select('id', 'companyName', 'email', 'location', 'industry', 'logoUrl', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $companies,
                'count' => $companies->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch companies'
            ], 500);
        }
    }

    /**
     * Get all workers
     */
    public function getAllWorkers()
    {
        try {
            $workers = DB::table('workers')
                ->select('id', 'fullName', 'email', 'location', 'industry', 'photoUrl', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $workers,
                'count' => $workers->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch workers'
            ], 500);
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