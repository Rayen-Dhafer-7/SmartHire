<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use PDO;
use PDOException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\DB; 

class WorkerController extends Controller
{
    private function pdo()
    {
        return DB::connection()->getPdo();
    }

public function register(Request $request)
{
    \Log::info('Worker Registration Request:', $request->all());
    
    $validator = Validator::make($request->all(), [
        'fullName' => 'required|string|max:255',
        'email'    => 'required|email',
        'password' => 'required|string',
        'location' => 'nullable|string|max:255',
        'industry' => 'nullable|string',
        'profile'  => 'nullable|image|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $pdo = $this->pdo();

        // Check if email already exists
        $check = $pdo->prepare("SELECT id FROM workers WHERE email = ?");
        $check->execute([$request->email]);
        if ($check->fetch()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['email' => ['Email already exists']]
            ], 422);
        }

        // Handle profile photo upload to S3
        $photoUrl = null;
        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Upload to S3
            $file->storeAs('workers/photos', $filename, 's3');

            // Get public HTTPS URL
            $photoUrl = Storage::disk('s3')->url('workers/photos/' . $filename);

            \Log::info('Profile photo uploaded to S3:', [
                'filename' => $filename,
                'url' => $photoUrl
            ]);
        }

        // Insert worker
        $workerId = 'WRK_' . uniqid();
        $stmt = $pdo->prepare(
            "INSERT INTO workers (id, fullname, email, password, photoUrl) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $workerId,
            $request->fullName,
            $request->email,
            Hash::make($request->password),
            $photoUrl
        ]);

        // Insert into UrlsCompte
        $urlsCompteId = 'URL_' . uniqid();
        $urlsStmt = $pdo->prepare(
            "INSERT INTO UrlsCompte (id, user_id, user_type) VALUES (?, ?, 'worker')"
        );
        $urlsStmt->execute([$urlsCompteId, $workerId]);

        // Insert empty WorkerCV record
        $workerCvId = 'CV_' . uniqid();
        $cvStmt = $pdo->prepare(
            "INSERT INTO WorkerCV (id, worker_id) VALUES (?, ?)"
        );
        $cvStmt->execute([$workerCvId, $workerId]);

        return response()->json([
            'status' => 'success',
            'message' => 'Worker registered successfully',
            'worker_id' => $workerId,
            'fullname' => $request->fullName,
            'email' => $request->email,
            'photoUrl' => $photoUrl
        ], 201);

    } catch (PDOException $e) {
        \Log::error('Database error:', ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed'
        ], 500);
    }
}

    public function getinfo(Request $request)
{
   

    try {
        
        $pdo = $this->pdo();

        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['status' => 'error', 'message' => 'Token required'], 401);
        }

        $workerId = $this->getWorkerIdFromToken($token);
       
        // Worker info
        $stmt = $pdo->prepare("SELECT fullname, email, photoUrl, location, industry FROM workers WHERE id = ?");
        $stmt->execute([$workerId]);
        $worker = $stmt->fetch();

        if (!$worker) {
            return response()->json(['status' => 'error', 'message' => 'Worker not found'], 404);
        }

        // URLs
        $urlsStmt = $pdo->prepare("
            SELECT url_linkedin, url_github, url_website, url_gmail
            FROM UrlsCompte 
            WHERE user_id = ? AND user_type = 'worker'
        ");
        $urlsStmt->execute([$workerId]);
        $urls = $urlsStmt->fetch() ?: [];

        // CV
        $cvStmt = $pdo->prepare("
            SELECT id, file_path, original_name, file_size, uploaded_at
            FROM WorkerCV 
            WHERE worker_id = ?
        ");
        $cvStmt->execute([$workerId]);
        $cv = $cvStmt->fetch();

        return response()->json([
            'status' => 'success',
            'worker' => $worker,
            'urls' => $urls,
            'cv' => $cv
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Server error',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function updateinfo(Request $request)
{
    \Log::info('--- WORKER UPDATE INFO START ---');
    \Log::info('Request data:', $request->all());
    \Log::info('Has profile file:', ['profile' => $request->hasFile('profile')]);

    $validator = Validator::make($request->all(), [
        'fullName' => 'required|string|max:255',
        'email' => 'required|email',
        'location' => 'nullable',
        'industry' => 'nullable',
        'profile' => 'nullable|image|max:2048',
        'url_linkedin' => 'nullable',
        'url_github' => 'nullable',
        'url_website' => 'nullable',
        'url_gmail' => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        // ================= PHOTO =================
        $photoStmt = $pdo->prepare("SELECT photoUrl FROM workers WHERE id = ?");
        $photoStmt->execute([$workerId]);
        $currentPhoto = $photoStmt->fetchColumn();

        $newPhotoUrl = $currentPhoto;
 
        if ($request->hasFile('profile')) {
            \Log::info('New profile photo detected');

            // Delete old photo if exists
            if ($currentPhoto) {
                $oldFilename = basename($currentPhoto);
                $oldFile = 'public/workers/photos/' . $oldFilename;
                \Log::info('Deleting old photo:', ['path' => $oldFile]);
                Storage::delete($oldFile);
                
                // Also delete from public storage
                $oldPublicFile = public_path('storage/workers/photos/' . $oldFilename);
                if (file_exists($oldPublicFile)) {
                    unlink($oldPublicFile);
                    \Log::info('Deleted old photo from public storage');
                }
            }

            $file = $request->file('profile');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            \Log::info('Storing new photo:', ['filename' => $filename]);

            // ✅ STORE IN STORAGE (correct path)
            $path = $file->storeAs('workers/photos', $filename, 'public');
            
            // ✅ GENERATE CLEAN URL
            $newPhotoUrl = asset('storage/workers/photos/' . $filename);

            // ✅ AUTO-COPY TO PUBLIC STORAGE (THIS IS CRITICAL!)
            $sourcePath = storage_path('app/public/workers/photos/' . $filename);
            $targetPath = public_path('storage/workers/photos/' . $filename);
            $targetDir = dirname($targetPath);
            
            // Create directory if it doesn't exist
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
                \Log::info('Created directory:', ['dir' => $targetDir]);
            }
            
            // Copy file to public storage
            if (!file_exists($targetPath)) {
                copy($sourcePath, $targetPath);
                chmod($targetPath, 0644);
                \Log::info('File auto-copied to public storage:', ['path' => $targetPath]);
            }

            \Log::info('New photo stored and published:', ['url' => $newPhotoUrl]);
        }

        // ================= UPDATE WORKER =================
        $stmt = $pdo->prepare("
            UPDATE workers 
            SET fullname = ?, email = ?, photoUrl = ?, location = ?, industry = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $request->fullName,
            $request->email,
            $newPhotoUrl,
            $request->location,
            $request->industry,
            $workerId
        ]);

        // ================= URLS =================
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM UrlsCompte WHERE user_id = ? AND user_type = 'worker'");
        $checkStmt->execute([$workerId]);

        if ($checkStmt->fetchColumn() > 0) {
            $urlsStmt = $pdo->prepare("
                UPDATE UrlsCompte SET
                    url_linkedin = ?,
                    url_github = ?,
                    url_website = ?,
                    url_gmail = ?
                WHERE user_id = ? AND user_type = 'worker'
            ");

            $urlsStmt->execute([
                $request->url_linkedin,
                $request->url_github,
                $request->url_website,
                $request->url_gmail,
                $workerId
            ]);
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Worker updated successfully',
            'data' => [
                'fullname' => $request->fullName,
                'email' => $request->email,
                'photoUrl' => $newPhotoUrl,
                'location' => $request->location,
                'industry' => $request->industry
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('WORKER UPDATE ERROR', [
            'message' => $e->getMessage(),
            'line' => $e->getLine()
        ]);

        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

public function uploadCV(Request $request)
{
    
    \Log::info('--- CV UPLOAD START ---');
    \Log::info('Has CV file:', ['cv' => $request->hasFile('cv')]);

    $validator = Validator::make($request->all(), [
        'cv' => 'required|file',
    ]);

    if ($validator->fails()) {
        \Log::error('CV validation failed:', $validator->errors()->toArray());
        return response()->json([
            'status' => 'error', 
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        \Log::info('Connecting to database...');
        $pdo = $this->pdo();

        \Log::info('Decoding token...');
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());
        
        if (!$workerId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token'
            ], 401);
        }
        
        \Log::info('Worker ID:', ['worker_id' => $workerId]);

        // ================= CV UPLOAD =================
        $cvFile = $request->file('cv');
        $cvFilename = time() . '_' . uniqid() . '_' . $cvFile->getClientOriginalName();
        \Log::info('Saving CV file:', [
            'filename' => $cvFilename,
            'original_name' => $cvFile->getClientOriginalName(),
            'size' => $cvFile->getSize(),
            'mime_type' => $cvFile->getMimeType()
        ]);

        // Store CV file
        $cvPath = $cvFile->storeAs('public/workers/cv', $cvFilename);
        $cvStoragePath = Storage::url($cvPath);

        \Log::info('CV stored at:', ['storage_path' => $cvStoragePath]);

        // Check if worker already has a CV
        $cvCheckStmt = $pdo->prepare("SELECT id, file_path FROM WorkerCV WHERE worker_id = ?");
        $cvCheckStmt->execute([$workerId]);
        $existingCv = $cvCheckStmt->fetch();

        \Log::info('Existing CV:', ['cv' => $existingCv]);

        if ($existingCv) {
            \Log::info('Updating existing CV');

  
        if ($existingCv['file_path']) {
            // Extract just the filename from the URL
            $pathParts = explode('/', $existingCv['file_path']);
            $filename = end($pathParts);
            
            $oldFilePath = 'public/workers/cv/' . $filename;
            \Log::info('Deleting old CV file from storage:', ['path' => $oldFilePath]);
            Storage::delete($oldFilePath);
        }

            $cvStmt = $pdo->prepare("
                UPDATE WorkerCV SET 
                    file_path = ?,
                    original_name = ?,
                    file_size = ?,
                    uploaded_at = NOW()
                WHERE worker_id = ?
            ");

            $cvStmt->execute([
                $cvStoragePath,
                $cvFile->getClientOriginalName(),
                $cvFile->getSize(),
                $workerId
            ]);

            $cvId = $existingCv['id'];
            \Log::info('CV updated', ['cv_id' => $cvId]);
        } else {
            \Log::info('Inserting new CV');

            $cvId = 'CV_' . uniqid();
            $cvStmt = $pdo->prepare("
                INSERT INTO WorkerCV 
                (id, worker_id, file_path, original_name, file_size, uploaded_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $cvStmt->execute([
                $cvId,
                $workerId,
                $cvStoragePath,
                $cvFile->getClientOriginalName(),
                $cvFile->getSize()
            ]);

            \Log::info('CV inserted', ['cv_id' => $cvId]);
        }

        \Log::info('--- CV UPLOAD SUCCESS ---');

        return response()->json([
            'status' => 'success', 
            'message' => 'CV uploaded successfully',
            'data' => [
                'id' => $cvId,
                'original_name' => $cvFile->getClientOriginalName(),
                'file_path' => $cvStoragePath,
                'file_size' => $cvFile->getSize(),
                'uploaded_at' => date('Y-m-d H:i:s')
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('CV UPLOAD ERROR', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to upload CV: ' . $e->getMessage()
        ], 500);
    }
}



public function updatepass(Request $request)
{

    $validator = Validator::make($request->all(), [
        'oldPassword' => 'required|string',
        'newPassword' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        $stmt = $pdo->prepare("SELECT password FROM workers WHERE id = ?");
        $stmt->execute([$workerId]);
        $worker = $stmt->fetch();

        if (!$worker || !Hash::check($request->oldPassword, $worker['password'])) {
            return response()->json(['status' => 'error', 'message' => 'Old password incorrect'], 400);
        }

        $updateStmt = $pdo->prepare("UPDATE workers SET password = ? WHERE id = ?");
        $updateStmt->execute([Hash::make($request->newPassword), $workerId]);

        return response()->json(['status' => 'success', 'message' => 'Password updated']);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Server error'], 500);
    }
}



public function deleteCv(Request $request, $cvId)
{
   

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        // Check CV ownership
        $stmt = $pdo->prepare("
            SELECT file_path 
            FROM WorkerCV 
            WHERE id = ? AND worker_id = ?
        ");
        $stmt->execute([$cvId, $workerId]);
        $cv = $stmt->fetch();

        if (!$cv) {
            return response()->json([
                'status' => 'error',
                'message' => 'CV not found or not authorized'
            ], 404);
        }

        // Delete file from storage
        if (!empty($cv['file_path'])) {
            $storagePath = str_replace('/storage/', 'public/', $cv['file_path']);
            Storage::delete($storagePath);
        }

        // Delete record
        $deleteStmt = $pdo->prepare("
            DELETE FROM WorkerCV 
            WHERE id = ? AND worker_id = ?
        ");
        $deleteStmt->execute([$cvId, $workerId]);

        return response()->json([
            'status' => 'success',
            'message' => 'CV deleted successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}




public function getCvText(Request $request)
{
    

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());
        
        if (!$workerId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token'
            ], 401);
        }

        $cvUrl = $request->input('path');
        
        if (!$cvUrl) {
            return response()->json([
                'status' => 'error',
                'message' => 'CV path is required'
            ], 400);
        }

        // Convert URL to storage path
        $relativePath = str_replace(
            url('/storage') . '/',
            '',
            $cvUrl
        );

        $fullPath = storage_path('app/public/' . $relativePath);

        if (!file_exists($fullPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'CV file not found'
            ], 404);
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fullPath);
        
        // Get the raw text
        $rawText = $pdf->getText();
        
        // Clean the text to ensure UTF-8 encoding
        $cleanText = $this->cleanPdfText($rawText);
        
        // Delete old data
        $this->deleteOldWorkerData($pdo, $workerId);
        
        // Extract and save data using Groq API
        $extractedData = $this->extractDataWithGroq($cleanText, $workerId, $pdo);
        
        if ($extractedData['status'] === 'error') {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to extract structured data: ' . $extractedData['message']
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'CV processed successfully'
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    } catch (\Throwable $e) {
        // Log error for debugging
        \Log::error('CV Parser Error:', [
            'message' => $e->getMessage(),
            'path' => $fullPath ?? 'N/A'
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to extract CV text: ' . $e->getMessage()
        ], 500, [], JSON_UNESCAPED_UNICODE);
    }
}

private function cleanPdfText(string $text): string
{
    // Remove non-UTF-8 characters
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    
    // Replace invalid UTF-8 characters
    $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
    
    // Remove control characters except newlines and tabs
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
    
    // Remove BOM (Byte Order Mark) if present
    $text = str_replace("\xEF\xBB\xBF", '', $text);
    
    // Normalize whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Trim and return
    return trim($text);
}

private function deleteOldWorkerData($pdo, $workerId)
{
    try {
        // First, check what columns exist in each table
        $tables = [
            'WorkerSkills' => ['worker_id'],
            'WorkerCertifications' => ['worker_id'], 
            'WorkerEducation' => ['worker_id'],
            'WorkerExperience' => ['worker_id'],
            'WorkerProjects' => ['worker_id'],
            'WorkerProjectTechnologies' => ['project_id'], // This one doesn't have worker_id directly
            'WorkerProjectPoints' => ['project_id'] // This one doesn't have worker_id directly
        ];
        
        foreach ($tables as $table => $column) {
            if ($column[0] === 'worker_id') {
                // For tables with worker_id column
                $stmt = $pdo->prepare("DELETE FROM $table WHERE worker_id = ?");
                $stmt->execute([$workerId]);
            } else {
                // For project-related tables, we need to delete based on project IDs
                if ($table === 'WorkerProjectTechnologies' || $table === 'WorkerProjectPoints') {
                    // First get all project IDs for this worker
                    $stmt = $pdo->prepare("SELECT id FROM WorkerProjects WHERE worker_id = ?");
                    $stmt->execute([$workerId]);
                    $projectIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                    
                    if (!empty($projectIds)) {
                        // Create placeholders for IN clause
                        $placeholders = str_repeat('?,', count($projectIds) - 1) . '?';
                        $stmt = $pdo->prepare("DELETE FROM $table WHERE project_id IN ($placeholders)");
                        $stmt->execute($projectIds);
                    }
                }
            }
        }
        
        // Also delete from WorkerProjects table
        $stmt = $pdo->prepare("DELETE FROM WorkerProjects WHERE worker_id = ?");
        $stmt->execute([$workerId]);
        
        \Log::info("Deleted old data for worker: $workerId");
        
    } catch (\Throwable $e) {
        // If there's an error with worker_id, try alternative column names
        if (strpos($e->getMessage(), 'worker_id') !== false) {
            \Log::warning("worker_id column not found, trying alternative columns");
            $this->deleteOldWorkerDataAlternative($pdo, $workerId);
        } else {
            \Log::error("Error deleting old worker data: " . $e->getMessage());
            throw $e;
        }
    }
}

private function deleteOldWorkerDataAlternative($pdo, $workerId)
{
    try {
        // Try different column names
        $possibleColumns = ['worker_id', 'user_id', 'employee_id', 'workerId'];
        
        foreach ($possibleColumns as $column) {
            try {
                // Test if column exists in WorkerSkills table
                $testStmt = $pdo->prepare("SELECT COUNT(*) FROM WorkerSkills WHERE $column = ?");
                $testStmt->execute([$workerId]);
                
                // If no error, this column exists
                $tablesWithWorkerId = [
                    'WorkerSkills',
                    'WorkerCertifications', 
                    'WorkerEducation',
                    'WorkerExperience',
                    'WorkerProjects'
                ];
                
                foreach ($tablesWithWorkerId as $table) {
                    $stmt = $pdo->prepare("DELETE FROM $table WHERE $column = ?");
                    $stmt->execute([$workerId]);
                }
                
                // Handle project-related tables
                $stmt = $pdo->prepare("SELECT id FROM WorkerProjects WHERE $column = ?");
                $stmt->execute([$workerId]);
                $projectIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                if (!empty($projectIds)) {
                    // Delete from WorkerProjectTechnologies
                    $placeholders = str_repeat('?,', count($projectIds) - 1) . '?';
                    $stmt = $pdo->prepare("DELETE FROM WorkerProjectTechnologies WHERE project_id IN ($placeholders)");
                    $stmt->execute($projectIds);
                    
                    // Delete from WorkerProjectPoints
                    $stmt = $pdo->prepare("DELETE FROM WorkerProjectPoints WHERE project_id IN ($placeholders)");
                    $stmt->execute($projectIds);
                }
                
                \Log::info("Deleted old data using column: $column for worker: $workerId");
                return;
                
            } catch (\Throwable $e) {
                // Column doesn't exist, try next one
                continue;
            }
        }
        
        throw new \Exception("Could not find worker identifier column in tables");
        
    } catch (\Throwable $e) {
        \Log::error("Error in alternative delete method: " . $e->getMessage());
        throw $e;
    }
}


private function extractDataWithGroq(string $cvText, string $workerId, $pdo)
{
    try {
        $GROQ_API_URL = env('GROQ_API_URL');
        $API_KEY = env('GROQ_API_KEY');
        $MODEL = env('GROQ_MODEL');
        
        $prompt = "Extract structured information from this CV/resume text and return ONLY valid JSON. 
        Structure should be exactly:
        {
            \"industry\": \"short professional summary/bio (2-3 sentences max)\",
            \"skills\": [\"skill1\", \"skill2\", ...],
            \"certifications\": [
                {\"name\": \"cert name\", \"issuer\": \"issuer name\", \"issue_date\": \"YYYY/MM\"}
            ],
            \"education\": [
                {\"degree\": \"degree name\", \"institution\": \"school name\", \"location\": \"city, country\", \"start_year\": \"YYYY\", \"end_year\": \"YYYY\"}
            ],
            \"experience\": [
                {\"title\": \"job title\", \"company\": \"company name\", \"location\": \"city, country\", \"employment_type\": \"Onsite/Remote/Hybrid\", \"start_date\": \"YYYY/MM\", \"end_date\": \"YYYY/MM or Present\", \"description\": \"bullet points separated by newlines\"}
            ],
            \"projects\": [
                {\"name\": \"project name\", \"technologies\": [\"tech1\", \"tech2\"], \"points\": [\"point1\", \"point2\", \"point3\", \"point4\", \"point5\", \"point6\"]}  // ← ICI: tous les points doivent être inclus
            ]
        }
        
        Rules:
        1. Extract a professional summary/industry from the CV (about me, bio, objective section)
        2. For industry, write 2-3 sentences summarizing professional background
        3. Only extract information that is clearly present
        4. For skills, list specific technical/professional skills
        5. For dates, format as YYYY/MM or YYYY
        6. Keep descriptions concise
        7. Return empty arrays if no data found
        8. IMPORTANT: For the \"projects\" array, you MUST include EVERY bullet point under each project. Do NOT truncate them.
        
        CV Text: " . substr($cvText, 0, 15000); // Just increased limit for projects
        
        $client = new \GuzzleHttp\Client();
        
        $response = $client->post($GROQ_API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $API_KEY,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a CV parsing assistant. Extract information and return valid JSON only.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 8000, // Seulement augmenté pour les projets
                'response_format' => ['type' => 'json_object']
            ],
            'timeout' => 60 // Seulement augmenté pour les projets
        ]);
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid response from Groq API');
        }
        
        $jsonContent = $responseData['choices'][0]['message']['content'];
        $extractedData = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to clean JSON if malformed
            $jsonContent = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonContent);
            $extractedData = json_decode($jsonContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON from Groq: ' . json_last_error_msg());
            }
        }
        
        // Log the number of project points for debugging
        if (!empty($extractedData['projects'])) {
            foreach ($extractedData['projects'] as $index => $project) {
                $pointCount = isset($project['points']) ? count($project['points']) : 0;
                \Log::info("Project {$index} - {$project['name']}: extracted {$pointCount} points");
            }
        }
        
        // Save extracted data to database
        $saveResult = $this->saveExtractedData($pdo, $workerId, $extractedData);
        
        return [
            'status' => 'success',
            'data' => $saveResult
        ];
        
    } catch (\Throwable $e) {
        \Log::error('Groq API Error: ' . $e->getMessage());
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

private function saveExtractedData($pdo, $workerId, $data)
{
    $saved = [];
    
    try {
        // ================= SAVE INDUSTRY/BIO =================
        if (!empty($data['industry'])) {
            $stmt = $pdo->prepare("UPDATE workers SET industry = ? WHERE id = ?");
            $stmt->execute([trim($data['industry']), $workerId]);
            $saved['industry'] = true;
            \Log::info('Industry/bio updated from CV:', ['industry' => substr($data['industry'], 0, 100) . '...']);
        }
        
        // First, determine the correct column name for worker ID
        $workerIdColumn = $this->getWorkerIdColumnName($pdo, 'WorkerSkills');
        
        // Save Skills
        if (!empty($data['skills'])) {
            foreach ($data['skills'] as $skill) {
                $skillId = uniqid('skill_');
                $stmt = $pdo->prepare("
                    INSERT INTO WorkerSkills (id, $workerIdColumn, skill_name, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$skillId, $workerId, trim($skill)]);
            }
            $saved['skills'] = count($data['skills']);
        }
        
        // Save Certifications
        if (!empty($data['certifications'])) {
            foreach ($data['certifications'] as $cert) {
                $certId = uniqid('cert_');
                $stmt = $pdo->prepare("
                    INSERT INTO WorkerCertifications (id, $workerIdColumn, name, issuer, issue_date, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $certId, 
                    $workerId, 
                    $cert['name'] ?? '',
                    $cert['issuer'] ?? '',
                    $cert['issue_date'] ?? ''
                ]);
            }
            $saved['certifications'] = count($data['certifications']);
        }
        
        // Save Education
        if (!empty($data['education'])) {
            foreach ($data['education'] as $edu) {
                $eduId = uniqid('edu_');
                $stmt = $pdo->prepare("
                    INSERT INTO WorkerEducation (id, $workerIdColumn, degree, institution, location, start_year, end_year, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $eduId,
                    $workerId,
                    $edu['degree'] ?? '',
                    $edu['institution'] ?? '',
                    $edu['location'] ?? '',
                    $edu['start_year'] ?? '',
                    $edu['end_year'] ?? ''
                ]);
            }
            $saved['education'] = count($data['education']);
        }
        
        // Save Experience
        if (!empty($data['experience'])) {
            foreach ($data['experience'] as $exp) {
                $expId = uniqid('exp_');
                $stmt = $pdo->prepare("
                    INSERT INTO WorkerExperience (id, $workerIdColumn, title, company, location, employment_type, start_date, end_date, description, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $expId,
                    $workerId,
                    $exp['title'] ?? '',
                    $exp['company'] ?? '',
                    $exp['location'] ?? '',
                    $exp['employment_type'] ?? 'Onsite',
                    $exp['start_date'] ?? '',
                    $exp['end_date'] ?? '',
                    $exp['description'] ?? ''
                ]);
            }
            $saved['experience'] = count($data['experience']);
        }
        
        // Save Projects
        if (!empty($data['projects'])) {
            foreach ($data['projects'] as $project) {
                $projectId = uniqid('proj_');
                
                // Save main project
                $stmt = $pdo->prepare("
                    INSERT INTO WorkerProjects (id, $workerIdColumn, project_name, description, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $projectId,
                    $workerId,
                    $project['name'] ?? '',
                    '' // Description can be empty or combine points
                ]);
                
                // Save technologies
                if (!empty($project['technologies'])) {
                    foreach ($project['technologies'] as $tech) {
                        $techId = uniqid('tech_');
                        $stmt = $pdo->prepare("
                            INSERT INTO WorkerProjectTechnologies (id, project_id, technology, created_at) 
                            VALUES (?, ?, ?, NOW())
                        ");
                        $stmt->execute([$techId, $projectId, trim($tech)]);
                    }
                }
                
                // Save project points
                if (!empty($project['points'])) {
                    foreach ($project['points'] as $point) {
                        $pointId = uniqid('point_');
                        $stmt = $pdo->prepare("
                            INSERT INTO WorkerProjectPoints (id, project_id, point_text, created_at) 
                            VALUES (?, ?, ?, NOW())
                        ");
                        $stmt->execute([$pointId, $projectId, trim($point)]);
                    }
                }
            }
            $saved['projects'] = count($data['projects']);
        }
        
        return $saved;
        
    } catch (\Throwable $e) {
        \Log::error('Error saving extracted data: ' . $e->getMessage());
        throw $e;
    }
}





public function getPosts(Request $request)
{
    

    try {
        $pdo = $this->pdo();

        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Token required'
            ], 401);
        }

        // Get worker ID from token
        $workerId = $this->getWorkerIdFromToken($token);

        // Get current date
        $today = date('Y-m-d');

        // Get all active posts that the worker has NOT taken yet
        $stmt = $pdo->prepare("
            SELECT 
                p.id as post_id,
                p.title,
                p.description,
                p.deadline,
                p.post_date,
                p.job_type,
                p.workers_needed,
                p.status,
                c.id as company_id,
                c.companyName,
                c.email as company_email,
                c.location,
                c.industry,
                c.logoUrl
            FROM posts p
            INNER JOIN companies c ON p.company_id = c.id
            WHERE p.deadline >= ?
            AND p.status = 'active'
            AND p.id NOT IN (
                SELECT post_id 
                FROM test_attempts 
                WHERE worker_id = ?
            )
            ORDER BY p.post_date DESC
        ");
        
        $stmt->execute([$today, $workerId]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get skills and social URLs for each post
        foreach ($posts as &$post) {
            // Get skills
            $skillStmt = $pdo->prepare("
                SELECT skill_name FROM job_skills 
                WHERE post_id = ? 
                ORDER BY skill_name
            ");
            $skillStmt->execute([$post['post_id']]);
            $skills = $skillStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get company social URLs
            $urlsStmt = $pdo->prepare("
                SELECT url_github, url_linkedin, url_facebook, url_instagram, url_twitter, url_website, url_gmail 
                FROM UrlsCompte 
                WHERE user_id = ? AND user_type = 'company'
            ");
            $urlsStmt->execute([$post['company_id']]);
            $urls = $urlsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // Format post to match frontend structure
            $formattedPost = [
                'id' => $post['post_id'],
                'company' => $post['companyName'],
                'location' => $post['location'],
                'title' => $post['title'],
                'description' => $post['description'],
                'date' => date('M d, Y', strtotime($post['post_date'])),
                'deadline' => date('M d, Y', strtotime($post['deadline'])),
                'workersNeeded' => (int)$post['workers_needed'],
                'type' => $post['job_type'],
                'skills' => $skills,
                'email' => $post['company_email'],
                'logoUrl' => $post['logoUrl'] ?? null, // Added logoUrl here
                'social' => [
                    'instagram' => $urls['url_instagram'] ?? null,
                    'facebook' => $urls['url_facebook'] ?? null,
                    'twitter' => $urls['url_twitter'] ?? null,
                    'linkedin' => $urls['url_linkedin'] ?? null,
                    'website' => $urls['url_website'] ?? null,
                    'email' => $urls['url_gmail'] ?? null
                ]
            ];
            
            $post = $formattedPost;
        }
        
        return response()->json([
            'status' => 'success',
            'posts' => $posts,
            'count' => count($posts)
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('Worker Get Posts Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to get job posts',
            'debug' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

public function getPostsMatche(Request $request)
{
    

    try {
        $pdo = $this->pdo();

        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Token required'
            ], 401);
        }

        // Get worker ID from token
        $workerId = $this->getWorkerIdFromToken($token);

        // Get current date
        $today = date('Y-m-d');





        // Get worker's skills
        $skillsStmt = $pdo->prepare("SELECT skill_name FROM WorkerSkills WHERE worker_id = ?");
        $skillsStmt->execute([$workerId]);
        $workerSkills = $skillsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($workerSkills)) {
            return response()->json([
                'status' => 'success',
                'posts' => [],
                'count' => 0,
                'message' => 'No skills found for worker'
            ], 200);
        }

        // Get worker's industry/bio
        $industryStmt = $pdo->prepare("SELECT industry FROM workers WHERE id = ?");
        $industryStmt->execute([$workerId]);
        $workerIndustry = $industryStmt->fetchColumn();

        // Get worker's experience descriptions
        $expStmt = $pdo->prepare("SELECT description FROM WorkerExperience WHERE worker_id = ? AND description IS NOT NULL");
        $expStmt->execute([$workerId]);
        $experiences = $expStmt->fetchAll(PDO::FETCH_COLUMN);
        $workerExperienceText = implode("\n\n", $experiences);

        // Build placeholders for skills IN clause
        $placeholders = implode(',', array_fill(0, count($workerSkills), '?'));

        // Get posts that match at least one worker skill AND worker hasn't taken yet
        $stmt = $pdo->prepare("
            SELECT DISTINCT
                p.id as post_id,
                p.title,
                p.description,
                p.deadline,
                p.post_date,
                p.job_type,
                p.workers_needed,
                p.status,
                c.id as company_id,
                c.companyName,
                c.email as company_email,
                c.location,
                c.industry as company_industry,
                c.logoUrl
            FROM posts p
            INNER JOIN companies c ON p.company_id = c.id
            INNER JOIN job_skills js ON p.id = js.post_id
            WHERE p.deadline >= ?
            AND p.status = 'active'
            AND js.skill_name IN ($placeholders)
            AND p.id NOT IN (
                SELECT post_id 
                FROM test_attempts 
                WHERE worker_id = ?
            )
            ORDER BY p.post_date DESC
        ");

        $params = array_merge([$today], $workerSkills, [$workerId]);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($posts)) {
            return response()->json([
                'status' => 'success',
                'posts' => [],
                'count' => 0,
                'message' => 'No matching posts found'
            ], 200);
        }

        // Get skills and social URLs for each post
        $matchedPosts = [];
        
        foreach ($posts as $post) {
            // Get post skills
            $skillStmt = $pdo->prepare("
                SELECT skill_name FROM job_skills 
                WHERE post_id = ? 
                ORDER BY skill_name
            ");
            $skillStmt->execute([$post['post_id']]);
            $postSkills = $skillStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get company social URLs
            $urlsStmt = $pdo->prepare("
                SELECT url_github, url_linkedin, url_facebook, url_instagram, url_twitter, url_website, url_gmail 
                FROM UrlsCompte 
                WHERE user_id = ? AND user_type = 'company'
            ");
            $urlsStmt->execute([$post['company_id']]);
            $urls = $urlsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            // Use Groq to evaluate match quality
            $matchResult = $this->evaluatePostMatchWithGroq(
                $post['title'],
                $post['description'],
                $post['company_industry'],
                $postSkills,
                $workerSkills,
                $workerIndustry,
                $workerExperienceText
            );

            // Only include posts that are a good match
            if ($matchResult['is_match']) {
                $formattedPost = [
                    'id' => $post['post_id'],
                    'company' => $post['companyName'],
                    'location' => $post['location'],
                    'title' => $post['title'],
                    'description' => $post['description'],
                    'date' => date('M d, Y', strtotime($post['post_date'])),
                    'deadline' => date('M d, Y', strtotime($post['deadline'])),
                    'workersNeeded' => (int)$post['workers_needed'],
                    'type' => $post['job_type'],
                    'skills' => $postSkills,
                    'email' => $post['company_email'],
                    'logoUrl' => $post['logoUrl'] ?? null,
                    'social' => [
                        'instagram' => $urls['url_instagram'] ?? null,
                        'facebook' => $urls['url_facebook'] ?? null,
                        'twitter' => $urls['url_twitter'] ?? null,
                        'linkedin' => $urls['url_linkedin'] ?? null,
                        'website' => $urls['url_website'] ?? null,
                        'email' => $urls['url_gmail'] ?? null
                    ],
                    'match_score' => $matchResult['score'],
                    'match_reason' => $matchResult['reason']
                ];
                
                $matchedPosts[] = $formattedPost;
            }
        }
        
        return response()->json([
            'status' => 'success',
            'posts' => $matchedPosts,
            'count' => count($matchedPosts)
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('Worker Get Posts Match Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to get matched job posts',
            'debug' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

private function evaluatePostMatchWithGroq($postTitle, $postDescription, $postIndustry, $postSkills, $workerSkills, $workerIndustry, $workerExperienceText)
{
        $GROQ_API_URL = env('GROQ_API_URL');
        $API_KEY = env('GROQ_API_KEY');
        $MODEL = env('GROQ_MODEL');
    
    $prompt = "You are a strict and accurate job matching expert. Analyze if this worker is genuinely qualified for this job.
    
    JOB POST:
    Title: {$postTitle}
    Full Description: {$postDescription}
    Industry: {$postIndustry}
    Required Skills: " . implode(', ', $postSkills) . "
    
    WORKER PROFILE:
    Worker Skills: " . implode(', ', $workerSkills) . "
    Worker Industry/Bio: " . ($workerIndustry ?: 'Not specified') . "
    
    WORKER EXPERIENCE (Read carefully):
    {$workerExperienceText}
    
    CRITICAL EVALUATION RULES:
    
    1. EXPERIENCE LEVEL CHECK:
       - Scan job description for words like: senior, lead, expert, +X years, X+ years
       - If job requires 5+ years or 'senior' but worker is student/intern/junior → is_match = FALSE
       - If worker's experience shows they held senior positions before → consider it
    
    2. STUDENT DETECTION:
       - Look for words in experience: student, intern, studying, university, college, graduate, bachelor, master
       - If worker is a student and job requires professional experience → is_match = FALSE
    
    3. SKILLS CHECK:
       - Skills match is secondary to experience
       - Even if skills match, if experience level is wrong → is_match = FALSE
    
    4. INDUSTRY ALIGNMENT:
       - Check if worker's industry/bio matches job industry
    
    Return ONLY valid JSON with this exact structure:
    {
        \"is_match\": true/false,
        \"score\": 0-100,
        \"reason\": \"Specific explanation focusing on experience level\",
        \"experience_analysis\": \"Brief summary of worker's experience level found\",
        \"job_requirement\": \"What experience level job requires\"
    }
    
    Examples of good reasons:
    - \"Job requires senior developer with 5+ years, but worker is a student with no professional experience\"
    - \"Worker has 6 years as senior developer matching the senior requirement\"
    - \"Job asks for 3+ years experience, worker has only intern experience (6 months)\"
    - \"Worker has relevant skills but experience level (junior) doesn't match senior requirement\"
    
    Be CRITICAL. It's better to reject unqualified workers than to accept them.";
    
    try {
        $client = new \GuzzleHttp\Client();
        
        $response = $client->post($GROQ_API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $API_KEY,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a strict job matching expert. Always check experience level first. Return valid JSON only.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 500,
                'response_format' => ['type' => 'json_object']
            ],
            'timeout' => 20
        ]);
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid response from Groq API');
        }
        
        $jsonContent = $responseData['choices'][0]['message']['content'];
        $result = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON from Groq');
        }
        
        return [
            'is_match' => $result['is_match'] ?? false,
            'score' => $result['score'] ?? 0,
            'reason' => $result['reason'] ?? 'Unable to evaluate match',
            'experience_analysis' => $result['experience_analysis'] ?? '',
            'job_requirement' => $result['job_requirement'] ?? ''
        ];
        
    } catch (\Exception $e) {
        \Log::error('Groq Match Evaluation Error: ' . $e->getMessage());
        
        // Intelligent fallback with experience detection
        $matchingSkills = array_intersect($workerSkills, $postSkills);
        $skillMatchPercentage = count($postSkills) > 0 
            ? (count($matchingSkills) / count($postSkills)) * 100 
            : 0;
        
        // Detect if job requires senior/experienced
        $seniorKeywords = ['senior', 'lead', 'sr', 'experienced', '5\+', '\+5', 'years'];
        $requiresSenior = false;
        $extractedYears = 0;
        
        foreach ($seniorKeywords as $keyword) {
            if (preg_match("/$keyword/i", $postTitle . ' ' . $postDescription)) {
                $requiresSenior = true;
                // Try to extract years
                if (preg_match('/(\d+)\+?\s*years?/i', $postDescription, $matches)) {
                    $extractedYears = (int)$matches[1];
                }
                break;
            }
        }
        
        // Detect if worker is student
        $studentKeywords = ['student', 'intern', 'studying', 'university', 'college', 'graduate', 'bachelor', 'master', 'trainee'];
        $isStudent = false;
        $experienceLevel = 'unknown';
        
        $textToCheck = strtolower($workerExperienceText . ' ' . $workerIndustry);
        foreach ($studentKeywords as $keyword) {
            if (strpos($textToCheck, $keyword) !== false) {
                $isStudent = true;
                $experienceLevel = 'student/intern';
                break;
            }
        }
        
        // Check for senior in worker experience
        $workerHasSenior = false;
        if (!$isStudent) {
            foreach ($seniorKeywords as $keyword) {
                if (strpos($textToCheck, $keyword) !== false) {
                    $workerHasSenior = true;
                    $experienceLevel = 'experienced/senior';
                    break;
                }
            }
        }
        
        // DECISION LOGIC
        $is_match = false;
        $reason = '';
        $score = $skillMatchPercentage;
        
        if ($requiresSenior && $isStudent) {
            $reason = "Job requires " . ($extractedYears ?: "senior") . " experience but worker appears to be a student";
            $score = $skillMatchPercentage * 0.2; // Reduce score significantly
        } 
        elseif ($requiresSenior && !$workerHasSenior && !$isStudent) {
            $reason = "Job requires senior-level but worker's experience doesn't indicate seniority";
            $score = $skillMatchPercentage * 0.4;
        }
        elseif ($requiresSenior && $workerHasSenior) {
            $is_match = true;
            $reason = "Worker has senior-level experience matching job requirements";
            $score = max($skillMatchPercentage, 70); // Boost score if senior match
        }
        elseif (!$requiresSenior && $skillMatchPercentage >= 60) {
            $is_match = true;
            $reason = "Skills match job requirements at " . round($skillMatchPercentage) . "%";
        }
        else {
            $reason = "Insufficient skills or experience match";
        }
        
        return [
            'is_match' => $is_match,
            'score' => round($score, 2),
            'reason' => $reason,
            'experience_analysis' => "Worker is " . $experienceLevel,
            'job_requirement' => $requiresSenior ? "Requires senior/experienced" : "Standard position"
        ];
    }
}


 
public function getTakenPosts(Request $request)
{
    

    try {
        $pdo = $this->pdo();

        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Token required'
            ], 401);
        }

        // Get worker ID from token
        $workerId = $this->getWorkerIdFromToken($token);
        
        // Get current date
        $today = date('Y-m-d');

        // Get all posts that the worker has taken with their final scores
        $stmt = $pdo->prepare("
            SELECT 
                p.id as post_id,
                p.title,
                p.description,
                p.deadline,
                p.post_date,
                p.job_type,
                p.workers_needed,
                c.id as company_id,
                c.companyName,
                c.email as company_email,
                c.location,
                c.industry,
                c.logoUrl,
                ta.id as test_attempt_id,
                ta.time_taken,
                ta.total_correct,
                ta.total_questions,
                ta.final_score,
                ta.created_at as test_date,
                CASE
                    WHEN p.deadline < ? THEN 
                        CASE
                            WHEN DATEDIFF(?, p.deadline) > 10 THEN 'Terminated'
                            ELSE CONCAT('Expired ', DATEDIFF(?, p.deadline), ' days ago')
                        END
                    WHEN p.deadline >= ? AND p.deadline <= DATE_ADD(?, INTERVAL 5 DAY) THEN 
                        CASE
                            WHEN DATEDIFF(p.deadline, ?) = 0 THEN 'Ending today'
                            WHEN DATEDIFF(p.deadline, ?) = 1 THEN 'Ending tomorrow'
                            ELSE CONCAT('Ending in ', DATEDIFF(p.deadline, ?), ' days')
                        END
                    ELSE 'Active'
                END as deadline_message,
                DATE_FORMAT(p.deadline, '%M %d, %Y') as deadline_formatted,
                DATE_FORMAT(p.post_date, '%M %d, %Y') as post_date_formatted
            FROM posts p
            INNER JOIN companies c ON p.company_id = c.id
            INNER JOIN test_attempts ta ON p.id = ta.post_id
            WHERE ta.worker_id = ?
            ORDER BY ta.created_at DESC
        ");
        
        $stmt->execute([
            $today, $today, $today,  // For expired cases
            $today, $today,           // For ending soon cases
            $today, $today, $today,   // For ending soon day calculations
            $workerId
        ]);
        
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get skills, social URLs, and skill results for each post
        foreach ($posts as &$post) {
            // Get skills
            $skillStmt = $pdo->prepare("
                SELECT skill_name FROM job_skills 
                WHERE post_id = ? 
                ORDER BY skill_name
            ");
            $skillStmt->execute([$post['post_id']]);
            $skills = $skillStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get skill results for this test attempt
            $skillResultsStmt = $pdo->prepare("
                SELECT 
                    skill_name,
                    correct_answers,
                    total_questions,
                    score_percentage,
                    level
                FROM skill_results 
                WHERE test_attempt_id = ? 
                ORDER BY skill_name
            ");
            $skillResultsStmt->execute([$post['test_attempt_id']]);
            $skillResults = $skillResultsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get company social URLs
            $urlsStmt = $pdo->prepare("
                SELECT url_github, url_linkedin, url_facebook, url_instagram, url_twitter, url_website, url_gmail 
                FROM UrlsCompte 
                WHERE user_id = ? AND user_type = 'company'
            ");
            $urlsStmt->execute([$post['company_id']]);
            $urls = $urlsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // Format post to match frontend structure
            $formattedPost = [
                'id' => $post['post_id'],
                'company' => $post['companyName'],
                'location' => $post['location'],
                'title' => $post['title'],
                'description' => $post['description'],
                'date' => $post['post_date_formatted'] ?? date('M d, Y', strtotime($post['post_date'])),
                'deadline' => $post['deadline_formatted'] ?? date('M d, Y', strtotime($post['deadline'])),
                'deadline_message' => $post['deadline_message'],
                'deadline_formatted' => $post['deadline_formatted'],
                'post_date_formatted' => $post['post_date_formatted'],
                'workersNeeded' => (int)$post['workers_needed'],
                'type' => $post['job_type'],
                'skills' => $skills,
                'email' => $post['company_email'],
                'logoUrl' => $post['logoUrl'] ?? null,
                'social' => [
                    'instagram' => $urls['url_instagram'] ?? null,
                    'facebook' => $urls['url_facebook'] ?? null,
                    'twitter' => $urls['url_twitter'] ?? null,
                    'linkedin' => $urls['url_linkedin'] ?? null,
                    'website' => $urls['url_website'] ?? null,
                    'email' => $urls['url_gmail'] ?? null
                ],
                'test' => [
                    'attempt_id' => $post['test_attempt_id'],
                    'time_taken' => $post['time_taken'],
                    'total_correct' => (int)$post['total_correct'],
                    'total_questions' => (int)$post['total_questions'],
                    'final_score' => (float)$post['final_score'],
                    'test_date' => date('M d, Y', strtotime($post['test_date'])),
                    'skill_results' => $skillResults
                ]
            ];
            
            $post = $formattedPost;
        }
        
        return response()->json([
            'status' => 'success',
            'posts' => $posts,
            'count' => count($posts)
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('Worker Get Taken Posts Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to get taken job posts',
            'debug' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}



 










public function geretest(Request $request)
{
   

    try {
        \Log::info('========== GERETEST API CALLED ==========');
        \Log::info('Request data:', $request->all());
        
        $pdo = $this->pdo();
        
        // Get token from header
        $token = $request->bearerToken();
        \Log::info('Token received: ' . ($token ? 'Yes' : 'No'));
        
        if (!$token) {
            \Log::error('No token provided');
            return response()->json([
                'status' => 'error', 
                'message' => 'Token required'
            ], 401);
        }
        
        // Get worker ID from token
        try {
            $workerId = $this->getWorkerIdFromToken($token);
            \Log::info('Worker ID from token: ' . $workerId);
        } catch (\Exception $e) {
            \Log::error('Failed to get worker ID from token: ' . $e->getMessage());
            throw $e;
        }
        
        // Get request data
        $postId = $request->post_id;
        $title = $request->title;
        $description = $request->description;
        $skills = $request->skills;
        
        \Log::info('Request data:', [
            'post_id' => $postId,
            'title' => $title,
            'description_length' => strlen($description),
            'skills' => $skills,
            'skills_count' => count($skills)
        ]);
        
        // Validate required fields
        if (!$postId) {
            \Log::error('Missing post_id');
            throw new \Exception('post_id is required');
        }
        if (!$title) {
            \Log::error('Missing title');
            throw new \Exception('title is required');
        }
        if (!$skills || !is_array($skills) || count($skills) === 0) {
            \Log::error('Missing or invalid skills');
            throw new \Exception('skills array is required');
        }
        
        // Generate questions for each skill using Groq
        $questions = [];
        
        foreach ($skills as $index => $skill) {
            \Log::info('Generating questions for skill ' . ($index + 1) . '/' . count($skills) . ': ' . $skill);
            
            try {
                $skillQuestions = $this->generateQuestionsWithGroq($skill, $title, $description);
                
                // Format to match frontend structure
                $questions[] = [
                    'skill' => $skill,
                    'mcqs' => $skillQuestions['mcqs'],
                    'debugging' => $skillQuestions['debugging'],
                    'scenario' => $skillQuestions['scenario']
                ];
                
                \Log::info('Successfully generated questions for: ' . $skill);
            } catch (\Exception $e) {
                \Log::error('Failed to generate questions for skill ' . $skill . ': ' . $e->getMessage());
                throw new \Exception('Failed to generate questions for ' . $skill . ': ' . $e->getMessage());
            }
        }
        
        $totalTime = count($skills) * 10 * 60; // 10 minutes per skill in seconds
        \Log::info('Test generated successfully:', [
            'skills_processed' => count($questions),
            'total_time' => $totalTime,
            'post_id' => $postId
        ]);
        
        \Log::info('========== GERETEST API COMPLETED ==========');
        
        return response()->json([
            'status' => 'success',
            'questions' => $questions,
            'total_time' => $totalTime,
            'post_id' => $postId
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('========== GERETEST API ERROR ==========');
        \Log::error('Error message: ' . $e->getMessage());
        \Log::error('Error trace: ' . $e->getTraceAsString());
        \Log::error('========================================');
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to generate test questions: ' . $e->getMessage()
        ], 500);
    }
}

private function generateQuestionsWithGroq($skill, $jobTitle, $jobDescription)
{
    \Log::info('--- Calling Groq API for skill: ' . $skill . ' ---');
    
        $GROQ_API_URL = env('GROQ_API_URL');
        $API_KEY = env('GROQ_API_KEY');
        $MODEL = env('GROQ_MODEL');
    
    $prompt = "Generate a technical assessment for the skill '{$skill}' for a job position: '{$jobTitle}'.
    
    Job Description: {$jobDescription}
    
    Generate 12 questions divided into 3 categories:
    1. 4 Multiple Choice Questions (MCQs) - Each with 4 options
    2. 4 Debugging/Code Reasoning Questions - Provide incorrect code snippet and ask what's wrong
    3. 4 Scenario-based Questions - Real-world scenarios
    
    IMPORTANT FORMAT RULES:
    - MCQs: MUST include ONLY 'text' and 'options' (array of 4 strings) - NO 'correct' field
    - Debugging: MUST include 'text' and 'code' ONLY (no options, no correct field)
    - Scenario: MUST include 'text' ONLY (no options, no correct field, no code)
    
    Return ONLY valid JSON with this exact structure:
    {
        \"mcqs\": [
            {\"text\": \"question text\", \"options\": [\"option1\", \"option2\", \"option3\", \"option4\"]},
            ...
        ],
        \"debugging\": [
            {\"text\": \"What is wrong with this code?\", \"code\": \"code snippet here\"},
            ...
        ],
        \"scenario\": [
            {\"text\": \"scenario question text here\"},
            ...
        ]
    }
    
    Rules:
    - Make questions challenging but fair for a {$skill} developer
    - For MCQs: DO NOT include a 'correct' field - just the question text and 4 options
    - For debugging: provide real code issues WITHOUT options - the user must identify the problem
    - For scenarios: provide real-world problems WITHOUT options - the user must explain solution
    - Return 4 questions of each type (12 total)
    
    Generate questions now:";
    
    \Log::info('Groq prompt prepared, length: ' . strlen($prompt));
    
    try {
        $client = new \GuzzleHttp\Client();
        \Log::info('Sending request to Groq API...');
        
        $response = $client->post($GROQ_API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $API_KEY,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a technical assessment generator. Create challenging, accurate test questions. Return valid JSON only. 
                        IMPORTANT: 
                        - MCQs: have text and options array ONLY - NO correct field
                        - Debugging: have text and code ONLY
                        - Scenarios: have text ONLY'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 4000,
                'response_format' => ['type' => 'json_object']
            ],
            'timeout' => 30
        ]);
        
        \Log::info('Groq API response received, status: ' . $response->getStatusCode());
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            \Log::error('Invalid Groq response structure', $responseData);
            throw new \Exception('Invalid response from Groq API');
        }
        
        $jsonContent = $responseData['choices'][0]['message']['content'];
        \Log::info('Groq JSON content received, length: ' . strlen($jsonContent));
        
        $questions = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::warning('Invalid JSON from Groq, attempting to clean...');
            \Log::warning('JSON error: ' . json_last_error_msg());
            
            // Try to clean JSON if malformed
            $jsonContent = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonContent);
            $questions = json_decode($jsonContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Failed to parse JSON even after cleaning');
                \Log::error('JSON content sample: ' . substr($jsonContent, 0, 500));
                throw new \Exception('Invalid JSON from Groq: ' . json_last_error_msg());
            }
        }
        
        // Validate that all required sections exist
        if (!isset($questions['mcqs'])) {
            \Log::error('Groq response missing mcqs section');
            throw new \Exception('Groq response missing mcqs section');
        }
        if (!isset($questions['debugging'])) {
            \Log::error('Groq response missing debugging section');
            throw new \Exception('Groq response missing debugging section');
        }
        if (!isset($questions['scenario'])) {
            \Log::error('Groq response missing scenario section');
            throw new \Exception('Groq response missing scenario section');
        }
        
        // Validate each section has exactly 4 questions
        if (count($questions['mcqs']) !== 4) {
            \Log::error('Groq mcqs count: ' . count($questions['mcqs']) . ', expected 4');
            throw new \Exception('Groq response must have exactly 4 MCQs');
        }
        if (count($questions['debugging']) !== 4) {
            \Log::error('Groq debugging count: ' . count($questions['debugging']) . ', expected 4');
            throw new \Exception('Groq response must have exactly 4 debugging questions');
        }
        if (count($questions['scenario']) !== 4) {
            \Log::error('Groq scenario count: ' . count($questions['scenario']) . ', expected 4');
            throw new \Exception('Groq response must have exactly 4 scenario questions');
        }
        
        // Remove 'correct' field from MCQs if Groq incorrectly adds it
        foreach ($questions['mcqs'] as $index => $mcq) {
            // Keep only text and options
            $cleanMcq = [
                'text' => $mcq['text'],
                'options' => $mcq['options']
            ];
            // Ensure options is array of 4 strings
            if (!is_array($cleanMcq['options']) || count($cleanMcq['options']) !== 4) {
                \Log::error('MCQ must have exactly 4 options at index ' . $index);
                throw new \Exception('MCQ must have exactly 4 options');
            }
            // Remove any other fields (including correct)
            $questions['mcqs'][$index] = $cleanMcq;
        }
        
        // Clean debugging questions - remove options and correct if present
        foreach ($questions['debugging'] as $index => $debug) {
            // Keep only text and code
            $cleanDebug = ['text' => $debug['text']];
            if (isset($debug['code'])) {
                $cleanDebug['code'] = $debug['code'];
            }
            // Remove any other fields
            $questions['debugging'][$index] = $cleanDebug;
        }
        
        // Clean scenario questions - keep only text
        foreach ($questions['scenario'] as $index => $scenario) {
            $questions['scenario'][$index] = ['text' => $scenario['text']];
        }
        
        \Log::info('Groq questions validated successfully for skill: ' . $skill);
        \Log::info('--- Groq API completed for skill: ' . $skill . ' ---');
        
        return $questions;
        
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        \Log::error('Groq API connection error: ' . $e->getMessage());
        throw new \Exception('Cannot connect to Groq API: ' . $e->getMessage());
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        \Log::error('Groq API client error: ' . $e->getMessage());
        $response = $e->getResponse();
        $responseBody = $response ? $response->getBody()->getContents() : 'No response body';
        \Log::error('Groq API error response: ' . $responseBody);
        throw new \Exception('Groq API error: ' . $e->getMessage());
    } catch (\GuzzleHttp\Exception\ServerException $e) {
        \Log::error('Groq API server error: ' . $e->getMessage());
        throw new \Exception('Groq API server error: ' . $e->getMessage());
    } catch (\Throwable $e) {
        \Log::error('Unexpected error in Groq API call: ' . $e->getMessage());
        throw $e;
    }
}



public function savetest(Request $request)
{
    

    try {
        $pdo = $this->pdo();
        
        // Get token from header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Token required'
            ], 401);
        }
        
        // Get worker ID from token
        $workerId = $this->getWorkerIdFromToken($token);
        
        // Get request data - ONLY what we need
        $postId = $request->post_id;
        $timeFormatted = $request->time_formatted;
        $answers = $request->answers;
        $questions = $request->questions;
        
        \Log::info('Saving test results:', [
            'worker_id' => $workerId,
            'post_id' => $postId,
            'time' => $timeFormatted
        ]);
        
        // Get company ID from posts table
        $companyStmt = $pdo->prepare("SELECT company_id FROM posts WHERE id = ?");
        $companyStmt->execute([$postId]);
        $companyId = $companyStmt->fetchColumn();
        
        if (!$companyId) {
            throw new \Exception('Post not found or has no company');
        }
        
        // Extract skills from questions array
        $skills = [];
        foreach ($questions as $question) {
            if (isset($question['skill'])) {
                $skills[] = $question['skill'];
            }
        }
        $skills = array_unique($skills); // Remove duplicates
        
        // Calculate ALL scores using Groq
        $skillResults = $this->calculateScoresWithGroq($answers, $questions, $skills);
        
        // Calculate overall totals from Groq results
        $totalCorrect = 0;
        $totalQuestions = count($skills) * 12; // 12 questions per skill
        
        foreach ($skillResults as $skill) {
            $totalCorrect += $skill['correct'];
        }
        
        $finalScore = round(($totalCorrect / $totalQuestions) * 100, 2);
        
        \Log::info('Scores calculated by Groq:', [
            'total_correct' => $totalCorrect,
            'total_questions' => $totalQuestions,
            'final_score' => $finalScore . '%',
            'skill_results' => $skillResults
        ]);
        
        // Begin transaction
        $pdo->beginTransaction();
        
        try {
            // Generate test attempt ID
            $testAttemptId = 'TEST_' . uniqid();
            
            // Insert test attempt (overall results)
            $attemptStmt = $pdo->prepare("
                INSERT INTO test_attempts 
                (id, post_id, worker_id, time_taken, total_correct, total_questions, final_score)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $attemptStmt->execute([
                $testAttemptId,
                $postId,
                $workerId,
                $timeFormatted,
                $totalCorrect,
                $totalQuestions,
                $finalScore
            ]);
            
            // Insert skill results (per skill)
            $skillStmt = $pdo->prepare("
                INSERT INTO skill_results 
                (id, test_attempt_id, skill_name, correct_answers, total_questions, score_percentage, level)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($skillResults as $skill) {
                $skillResultId = 'SKL_' . uniqid();
                
                $skillStmt->execute([
                    $skillResultId,
                    $testAttemptId,
                    $skill['name'],
                    $skill['correct'],
                    12,
                    $skill['percentage'],
                    $skill['level']
                ]);
            }
            
            $pdo->commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Test results saved successfully',
                'test_attempt_id' => $testAttemptId,
                'skill_results' => $skillResults,
                'overall_score' => $finalScore,
                'total_correct' => $totalCorrect,
                'total_questions' => $totalQuestions
            ], 200);
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (\Exception $e) {
        \Log::error('Save Test Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to save test results: ' . $e->getMessage()
        ], 500);
    }
}

private function calculateScoresWithGroq($answers, $questions, $skills)
{
        $GROQ_API_URL = env('GROQ_API_URL');
        $API_KEY = env('GROQ_API_KEY');
        $MODEL = env('GROQ_MODEL');
    
    $skillResults = [];
    
    foreach ($skills as $skill) {
        $correctCount = 0;
        $totalQuestions = 12;
        
        // Find questions for this skill
        $skillQuestions = null;
        foreach ($questions as $q) {
            if ($q['skill'] === $skill) {
                $skillQuestions = $q;
                break;
            }
        }
        
        if (!$skillQuestions) {
            continue;
        }
        
        $userAnswers = $answers[$skill] ?? null;
        
        // ============= EVALUATE MCQs =============
        if (isset($skillQuestions['mcqs']) && is_array($skillQuestions['mcqs'])) {
            foreach ($skillQuestions['mcqs'] as $index => $mcq) {
                if ($index >= 4) break;
                
                $userAnswer = $userAnswers['mcq'][$index] ?? null;
                $selectedOption = $userAnswer !== null ? $mcq['options'][$userAnswer] : 'No answer';
                
                // Skip if no answer
                if ($userAnswer === null) {
                    continue;
                }
                
                // Create prompt for this specific MCQ
                $prompt = "You are a technical expert. Evaluate if this answer is correct.
                
                Question: {$mcq['text']}
                
                Options:
                0: {$mcq['options'][0]}
                1: {$mcq['options'][1]}
                2: {$mcq['options'][2]}
                3: {$mcq['options'][3]}
                
                User selected option {$userAnswer}: {$selectedOption}
                
                Is this correct? Answer with ONLY 'true' or 'false'.";
                
                try {
                    $client = new \GuzzleHttp\Client();
                    $response = $client->post($GROQ_API_URL, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $API_KEY,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => $MODEL,
                            'messages' => [
                                [
                                    'role' => 'system',
                                    'content' => 'You are a fair, accurate technical evaluator. Answer only with true or false.'
                                ],
                                [
                                    'role' => 'user',
                                    'content' => $prompt
                                ]
                            ],
                            'temperature' => 0.1,
                            'max_tokens' => 10
                        ],
                        'timeout' => 10
                    ]);
                    
                    $responseData = json_decode($response->getBody()->getContents(), true);
                    $result = trim(strtolower($responseData['choices'][0]['message']['content']));
                    
                    if ($result === 'true') {
                        $correctCount++;
                    }
                    
                } catch (\Exception $e) {
                    \Log::error('Groq MCQ evaluation failed: ' . $e->getMessage());
                }
            }
        }
        
        // ============= EVALUATE DEBUGGING =============
        if (isset($skillQuestions['debugging']) && is_array($skillQuestions['debugging'])) {
            foreach ($skillQuestions['debugging'] as $index => $debug) {
                if ($index >= 4) break;
                
                $userAnswer = $userAnswers['debug'][$index] ?? '';
                
                if (empty($userAnswer)) {
                    continue;
                }
                
                // Create prompt for debugging question
                $prompt = "You are a technical expert. Evaluate if this debugging answer is correct.
                
                Code with issue:
                {$debug['code']}
                
                Question: {$debug['text']}
                
                User's answer: {$userAnswer}
                
                Is this answer correct? Answer with ONLY 'true' or 'false'.";
                
                try {
                    $client = new \GuzzleHttp\Client();
                    $response = $client->post($GROQ_API_URL, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $API_KEY,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => $MODEL,
                            'messages' => [
                                [
                                    'role' => 'system',
                                    'content' => 'You are a fair, accurate technical evaluator. Answer only with true or false.'
                                ],
                                [
                                    'role' => 'user',
                                    'content' => $prompt
                                ]
                            ],
                            'temperature' => 0.1,
                            'max_tokens' => 10
                        ],
                        'timeout' => 10
                    ]);
                    
                    $responseData = json_decode($response->getBody()->getContents(), true);
                    $result = trim(strtolower($responseData['choices'][0]['message']['content']));
                    
                    if ($result === 'true') {
                        $correctCount++;
                    }
                    
                } catch (\Exception $e) {
                    \Log::error('Groq Debugging evaluation failed: ' . $e->getMessage());
                }
            }
        }
        
        // ============= EVALUATE SCENARIO =============
        if (isset($skillQuestions['scenario']) && is_array($skillQuestions['scenario'])) {
            foreach ($skillQuestions['scenario'] as $index => $scenario) {
                if ($index >= 4) break;
                
                $userAnswer = $userAnswers['scenario'][$index] ?? '';
                
                if (empty($userAnswer)) {
                    continue;
                }
                
                // Create prompt for scenario question
                $prompt = "You are a technical expert. Evaluate if this scenario answer is correct.
                
                Scenario: {$scenario['text']}
                
                User's answer: {$userAnswer}
                
                Is this answer correct? Consider if it's technically accurate and addresses the problem.
                Answer with ONLY 'true' or 'false'.";
                
                try {
                    $client = new \GuzzleHttp\Client();
                    $response = $client->post($GROQ_API_URL, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $API_KEY,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => $MODEL,
                            'messages' => [
                                [
                                    'role' => 'system',
                                    'content' => 'You are a fair, accurate technical evaluator. Answer only with true or false.'
                                ],
                                [
                                    'role' => 'user',
                                    'content' => $prompt
                                ]
                            ],
                            'temperature' => 0.1,
                            'max_tokens' => 10
                        ],
                        'timeout' => 10
                    ]);
                    
                    $responseData = json_decode($response->getBody()->getContents(), true);
                    $result = trim(strtolower($responseData['choices'][0]['message']['content']));
                    
                    if ($result === 'true') {
                        $correctCount++;
                    }
                    
                } catch (\Exception $e) {
                    \Log::error('Groq Scenario evaluation failed: ' . $e->getMessage());
                }
            }
        }
        
        // Calculate percentage and level
        $percentage = round(($correctCount / $totalQuestions) * 100, 2);
        
        $level = 'Beginner';
        if ($percentage >= 86) $level = 'Expert';
        else if ($percentage >= 61) $level = 'Advanced';
        else if ($percentage >= 31) $level = 'Intermediate';
        
        $skillResults[] = [
            'name' => $skill,
            'correct' => $correctCount,
            'percentage' => $percentage,
            'level' => $level
        ];
    }
    
    return $skillResults;
}








private function getWorkerIdColumnName($pdo, $tableName)
{
    // Common column names for worker/user identifier
    $possibleColumns = ['worker_id', 'user_id', 'workerId', 'userId', 'employee_id'];
    
    try {
        // Get column names from table
        $stmt = $pdo->prepare("DESCRIBE $tableName");
        $stmt->execute();
        $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        // Check which column exists
        foreach ($possibleColumns as $column) {
            if (in_array($column, $columns)) {
                return $column;
            }
        }
        
        // If none found, default to worker_id
        return 'worker_id';
        
    } catch (\Throwable $e) {
        \Log::warning("Could not determine column name for $tableName: " . $e->getMessage());
        return 'worker_id';
    }
}


































private function getWorkerIdFromToken($token)
{
    try {
        
        \Log::info('JWT secret in decode:', ['length' => strlen(env('JWT_SECRET')), 'value' => env('JWT_SECRET')]);

        $jwtSecret = config('app.jwt_secret');
        
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
































/// ================= SKILLS =================

public function addSkill(Request $request)
{
    

    $validator = Validator::make($request->all(), [
        'skill' => 'required|string|max:100'
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        // Check if skill already exists for this worker
        $checkStmt = $pdo->prepare("SELECT id FROM WorkerSkills WHERE worker_id = ? AND skill_name = ?");
        $checkStmt->execute([$workerId, $request->skill]);
        
        if ($checkStmt->fetch()) {
            return response()->json(['status' => 'error', 'message' => 'Skill already exists'], 400);
        }

        $stmt = $pdo->prepare("
            INSERT INTO WorkerSkills (id, worker_id, skill_name) 
            VALUES (?, ?, ?)
        ");

        $skillId = 'SKILL_' . uniqid();
        $stmt->execute([$skillId, $workerId, $request->skill]);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill added successfully',
            'skill_id' => $skillId,
            'skill_name' => $request->skill
        ]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

public function removeSkill(Request $request, $skillId) // Changed parameter
{
    

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        $stmt = $pdo->prepare("
            DELETE FROM WorkerSkills 
            WHERE id = ? AND worker_id = ?
        "); // Changed to use id instead of skill_name

        $stmt->execute([$skillId, $workerId]);
        
        if ($stmt->rowCount() > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Skill removed successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Skill not found'
            ], 404);
        }

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

// ================= EXPERIENCE =================

public function addExperience(Request $request)
{
    

    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:200',
        'company' => 'required|string|max:200',
        'location' => 'nullable|string|max:200',
        'employment_type' => 'nullable|string|max:50',
        'start_date' => 'nullable|string',
        'end_date' => 'nullable|string',
        'description' => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        $stmt = $pdo->prepare("
            INSERT INTO WorkerExperience 
            (id, worker_id, title, company, location, employment_type, start_date, end_date, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $experienceId = 'EXP_' . uniqid();
        $stmt->execute([
            $experienceId,
            $workerId,
            $request->title,
            $request->company,
            $request->location,
            $request->employment_type ?? 'Onsite',
            $request->start_date,
            $request->end_date,
            $request->description
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Experience added successfully',
            'experience_id' => $experienceId
        ]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

public function removeExperience(Request $request, $experienceId)
{
    

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        $stmt = $pdo->prepare("
            DELETE FROM WorkerExperience 
            WHERE id = ? AND worker_id = ?
        ");

        $stmt->execute([$experienceId, $workerId]);
        
        if ($stmt->rowCount() > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Experience removed successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Experience not found or not authorized'
            ], 404);
        }

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

// ================= PROJECTS =================

public function addProject(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:200',
        'technologies' => 'nullable|array',
        'points' => 'nullable|array'
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        // Start transaction
        $pdo->beginTransaction();

        // Insert project
        $projectStmt = $pdo->prepare("
            INSERT INTO WorkerProjects (id, worker_id, project_name, description) 
            VALUES (?, ?, ?, ?)
        ");

        $projectId = 'PROJ_' . uniqid();
        $projectStmt->execute([
            $projectId,
            $workerId,
            $request->name,
            $request->description ?? ''
        ]);

        // Insert technologies
        if (!empty($request->technologies) && is_array($request->technologies)) {
            $techStmt = $pdo->prepare("
                INSERT INTO WorkerProjectTechnologies (id, project_id, technology) 
                VALUES (?, ?, ?)
            ");

            foreach ($request->technologies as $technology) {
                $techId = 'TECH_' . uniqid();
                $techStmt->execute([$techId, $projectId, trim($technology)]);
            }
        }

        // Insert points
        if (!empty($request->points) && is_array($request->points)) {
            $pointStmt = $pdo->prepare("
                INSERT INTO WorkerProjectPoints (id, project_id, point_text) 
                VALUES (?, ?, ?)
            ");

            foreach ($request->points as $point) {
                $pointId = 'POINT_' . uniqid();
                $pointStmt->execute([$pointId, $projectId, trim($point)]);
            }
        }

        $pdo->commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Project added successfully',
            'project_id' => $projectId
        ]);

    } catch (\Exception $e) {
        $pdo->rollBack();
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

public function removeProject(Request $request, $projectId)
{
    

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        // Verify ownership
        $checkStmt = $pdo->prepare("SELECT id FROM WorkerProjects WHERE id = ? AND worker_id = ?");
        $checkStmt->execute([$projectId, $workerId]);
        
        if (!$checkStmt->fetch()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found or not authorized'
            ], 404);
        }

        // Start transaction
        $pdo->beginTransaction();

        // Delete related technologies
        $deleteTechStmt = $pdo->prepare("DELETE FROM WorkerProjectTechnologies WHERE project_id = ?");
        $deleteTechStmt->execute([$projectId]);

        // Delete related points
        $deletePointsStmt = $pdo->prepare("DELETE FROM WorkerProjectPoints WHERE project_id = ?");
        $deletePointsStmt->execute([$projectId]);

        // Delete project
        $deleteProjectStmt = $pdo->prepare("DELETE FROM WorkerProjects WHERE id = ?");
        $deleteProjectStmt->execute([$projectId]);

        $pdo->commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Project removed successfully'
        ]);

    } catch (\Exception $e) {
        $pdo->rollBack();
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

// ================= CERTIFICATIONS =================

public function addCertification(Request $request)
{
    

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:200',
        'issuer' => 'nullable|string|max:200',
        'issue_date' => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        $stmt = $pdo->prepare("
            INSERT INTO WorkerCertifications (id, worker_id, name, issuer, issue_date) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $certId = 'CERT_' . uniqid();
        $stmt->execute([
            $certId,
            $workerId,
            $request->name,
            $request->issuer,
            $request->issue_date
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Certification added successfully',
            'certification_id' => $certId
        ]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

public function removeCertification(Request $request, $certificationId)
{
    

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        $stmt = $pdo->prepare("
            DELETE FROM WorkerCertifications 
            WHERE id = ? AND worker_id = ?
        ");

        $stmt->execute([$certificationId, $workerId]);
        
        if ($stmt->rowCount() > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Certification removed successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Certification not found or not authorized'
            ], 404);
        }

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

// ================= EDUCATION =================

public function addEducation(Request $request)
{
    

    $validator = Validator::make($request->all(), [
        'degree' => 'required|string|max:200',
        'institution' => 'required|string|max:200',
        'location' => 'nullable|string|max:200',
        'start_year' => 'nullable|string|max:20',
        'end_year' => 'nullable|string|max:20'
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        $stmt = $pdo->prepare("
            INSERT INTO WorkerEducation 
            (id, worker_id, degree, institution, location, start_year, end_year) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $educationId = 'EDU_' . uniqid();
        $stmt->execute([
            $educationId,
            $workerId,
            $request->degree,
            $request->institution,
            $request->location,
            $request->start_year,
            $request->end_year
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Education added successfully',
            'education_id' => $educationId
        ]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

public function removeEducation(Request $request, $educationId)
{
    

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        $stmt = $pdo->prepare("
            DELETE FROM WorkerEducation 
            WHERE id = ? AND worker_id = ?
        ");

        $stmt->execute([$educationId, $workerId]);
        
        if ($stmt->rowCount() > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Education removed successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Education not found or not authorized'
            ], 404);
        }

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}



// ================= GET ALL DATA (Updated to include IDs) =================

public function getAllProfileData(Request $request)
{
    

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        // Get skills with ID
        $skillsStmt = $pdo->prepare("SELECT id, skill_name FROM WorkerSkills WHERE worker_id = ? ORDER BY created_at");
        $skillsStmt->execute([$workerId]);
        $skills = $skillsStmt->fetchAll();

        // Get experience with ID
        $expStmt = $pdo->prepare("
            SELECT id, title, company, location, employment_type, start_date, end_date, description 
            FROM WorkerExperience 
            WHERE worker_id = ? 
            ORDER BY start_date DESC
        ");
        $expStmt->execute([$workerId]);
        $experience = $expStmt->fetchAll();

        // Get education with ID
        $eduStmt = $pdo->prepare("
            SELECT id, degree, institution, location, start_year, end_year 
            FROM WorkerEducation 
            WHERE worker_id = ? 
            ORDER BY end_year DESC
        ");
        $eduStmt->execute([$workerId]);
        $education = $eduStmt->fetchAll();

        // Get certifications with ID
        $certStmt = $pdo->prepare("
            SELECT id, name, issuer, issue_date 
            FROM WorkerCertifications 
            WHERE worker_id = ? 
            ORDER BY issue_date DESC
        ");
        $certStmt->execute([$workerId]);
        $certifications = $certStmt->fetchAll();

        // Get projects with ID
        $projectsStmt = $pdo->prepare("
            SELECT id, project_name, description 
            FROM WorkerProjects 
            WHERE worker_id = ? 
            ORDER BY created_at DESC
        ");
        $projectsStmt->execute([$workerId]);
        $projects = $projectsStmt->fetchAll();

        foreach ($projects as &$project) {
            // Get technologies for this project
            $techStmt = $pdo->prepare("
                SELECT technology 
                FROM WorkerProjectTechnologies 
                WHERE project_id = ?
            ");
            $techStmt->execute([$project['id']]);
            $project['technologies'] = $techStmt->fetchAll(PDO::FETCH_COLUMN, 0);

            // Get points for this project
            $pointsStmt = $pdo->prepare("
                SELECT point_text 
                FROM WorkerProjectPoints 
                WHERE project_id = ?
            ");
            $pointsStmt->execute([$project['id']]);
            $project['points'] = $pointsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'skills' => $skills, // Now includes id and skill_name
                'experience' => $experience,
                'education' => $education,
                'certifications' => $certifications,
                'projects' => $projects
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}


 

}