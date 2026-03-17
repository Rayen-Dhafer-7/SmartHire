<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB; 
use PDO;
use PDOException;

class CompanyController extends Controller
{
    private function pdo()
    {
        return DB::connection()->getPdo();
    }

public function register(Request $request)
{
    \Log::info('Company Registration Request:', $request->all());
    
    $validator = Validator::make($request->all(), [
        'companyName' => 'required|string|max:255',
        'email'       => 'required|email',
        'password'    => 'required|string',
        'location'    => 'required|string|max:255',
        'industry'    => 'required|string',
        'logo'        => 'nullable|image|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();

        // Check email
        $check = $pdo->prepare("SELECT id FROM companies WHERE email = ?");
        $check->execute([$request->email]);
        if ($check->fetch()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['email' => ['Email already exists']]
            ], 422);
        }

        // Handle logo upload to S3
        $logoUrl = null;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            Storage::disk('s3')->putFileAs('companies/logos', $file, $filename, 'public');
            $logoUrl = 'https://smarthire-uploads.s3.amazonaws.com/companies/logos/' . $filename;

            \Log::info('Logo uploaded to S3:', ['url' => $logoUrl]);
        }

        // Generate custom company ID
        $companyId = 'CMP_' . uniqid();

        // Insert company
        $stmt = $pdo->prepare(
            "INSERT INTO companies (id, companyName, email, password, location, industry, logoUrl) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $companyId,
            $request->companyName,
            $request->email,
            Hash::make($request->password),
            $request->location,
            $request->industry,
            $logoUrl
        ]);

        // Create UrlsCompte entry
        $urlsCompteId = 'URL_' . uniqid();
        $urlsStmt = $pdo->prepare(
            "INSERT INTO UrlsCompte (id, user_id, user_type) VALUES (?, ?, 'company')"
        );
        $urlsStmt->execute([$urlsCompteId, $companyId]);

        return response()->json([
            'status' => 'success',
            'message' => 'Company registered successfully',
            'company_id' => $companyId,
            'companyName' => $request->companyName,
            'email' => $request->email,
            'logoUrl' => $logoUrl
        ], 201);

    } catch (PDOException $e) {
        \Log::error('Database error:', ['error' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'message' => 'Database connection failed'], 500);
    }
}

public function updateinfo(Request $request)
{
    \Log::info('Company Update Request:', $request->all());
    
    $validator = Validator::make($request->all(), [
        'companyName' => 'required|string|max:255',
        'email' => 'required|email',
        'location' => 'required|string|max:255',
        'industry' => 'required|string',
        'logo' => 'nullable|image|max:2048',
        'url_github' => 'nullable',
        'url_linkedin' => 'nullable',
        'url_facebook' => 'nullable',
        'url_instagram' => 'nullable',
        'url_twitter' => 'nullable',
        'url_website' => 'nullable',
        'url_gmail' => 'nullable',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $companyId = $this->getCompanyIdFromToken($request->bearerToken());

        // Get current logo
        $getLogoStmt = $pdo->prepare("SELECT logoUrl FROM companies WHERE id = ?");
        $getLogoStmt->execute([$companyId]);
        $currentLogoUrl = $getLogoStmt->fetchColumn();
        $newLogoUrl = $currentLogoUrl;

        if ($request->hasFile('logo')) {
            \Log::info('New logo detected');
            $file = $request->file('logo');

            // Delete old logo from S3 if exists
            if ($currentLogoUrl && str_contains($currentLogoUrl, 's3.amazonaws.com')) {
                $oldKey = parse_url($currentLogoUrl, PHP_URL_PATH);
                $oldKey = ltrim($oldKey, '/');
                Storage::disk('s3')->delete($oldKey);
                \Log::info('Old S3 logo deleted:', ['key' => $oldKey]);
            }

            // Upload new logo to S3
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            Storage::disk('s3')->putFileAs('companies/logos', $file, $filename, 'public');
            $newLogoUrl = 'https://smarthire-uploads.s3.amazonaws.com/companies/logos/' . $filename;

            \Log::info('New logo uploaded to S3:', ['url' => $newLogoUrl]);
        }

        // Update company info
        $stmt = $pdo->prepare("
            UPDATE companies SET 
                companyName = ?, 
                email = ?, 
                location = ?, 
                industry = ?, 
                logoUrl = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $request->companyName,
            $request->email,
            $request->location,
            $request->industry,
            $newLogoUrl,
            $companyId
        ]);

        // Update URLs
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM UrlsCompte WHERE user_id = ? AND user_type = 'company'");
        $checkStmt->execute([$companyId]);
        $exists = $checkStmt->fetchColumn() > 0;

        if ($exists) {
            $urlsStmt = $pdo->prepare("
                UPDATE UrlsCompte SET
                    url_website = ?,
                    url_linkedin = ?,
                    url_twitter = ?,
                    url_facebook = ?,
                    url_instagram = ?,
                    url_gmail = ?
                WHERE user_id = ? AND user_type = 'company'
            ");
            $urlsStmt->execute([
                $request->url_website,
                $request->url_linkedin,
                $request->url_twitter,
                $request->url_facebook,
                $request->url_instagram,
                $request->url_gmail,
                $companyId
            ]);
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Company info updated successfully',
            'logoUrl' => $newLogoUrl
        ]);

    } catch (\Exception $e) {
        \Log::error('Company update error:', ['message' => $e->getMessage()]);
        return response()->json([
            'status' => 'error', 
            'message' => 'Server error: ' . $e->getMessage()
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
        $companyId = $this->getCompanyIdFromToken($request->bearerToken());
        
        // Get current password
        $stmt = $pdo->prepare("SELECT password FROM companies WHERE id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch();
        
        if (!$company) {
            return response()->json(['status' => 'error', 'message' => 'Company not found'], 404);
        }
        
        // Check old password
        if (!Hash::check($request->oldPassword, $company['password'])) {
            return response()->json(['status' => 'error', 'message' => 'Old password is incorrect'], 400);
        }
        

        
        // Update to new password
        $updateStmt = $pdo->prepare("UPDATE companies SET password = ? WHERE id = ?");
        $updateStmt->execute([Hash::make($request->newPassword), $companyId]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
        
    } catch (PDOException $e) {
        return response()->json(['status' => 'error', 'message' => 'Database error'], 500);
    }
}






public function getinfo(Request $request)
{
 

    try {
        $pdo = $this->pdo();
        
        // Get token from header
        $token = $request->bearerToken();
   
        if (!$token) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Token required',
                'debug' => 'No Authorization header found'
            ], 401);
        }
       
        // Get company ID from token
        \Log::info('Decoding token...');
        $companyId = $this->getCompanyIdFromToken($token);
        
        \Log::info('Token decoded successfully:', [
            'companyId' => $companyId
        ]);

        // Get company info
        $stmt = $pdo->prepare("SELECT companyName, email, location, industry, logoUrl FROM companies WHERE id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch();
        
        if (!$company) {
            \Log::warning('Company not found in DB:', ['companyId' => $companyId]);
            return response()->json([
                'status' => 'error', 
                'message' => 'Company not found',
                'debug' => ['companyId' => $companyId]
            ], 404);
        }
        
        // Get URLs
        $urlsStmt = $pdo->prepare("SELECT url_github, url_linkedin, url_facebook, url_instagram, url_twitter, url_website, url_gmail FROM UrlsCompte WHERE user_id = ? AND user_type = 'company'");
        $urlsStmt->execute([$companyId]);
        $urls = $urlsStmt->fetch() ?: [];
        
        // Get current date for comparison
        $today = date('Y-m-d');
        
        // Count old posts (deadline < today) - EXPIRED POSTS
        $oldPostsStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM posts 
            WHERE company_id = ? 
            AND deadline < ? 
            AND status = 'active'
        ");
        $oldPostsStmt->execute([$companyId, $today]);
        $oldPostsCount = $oldPostsStmt->fetchColumn();
        
        // Count in-progress posts (deadline >= today) - ACTIVE POSTS
        $inProgressPostsStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM posts 
            WHERE company_id = ? 
            AND deadline >= ? 
            AND status = 'active'
        ");
        $inProgressPostsStmt->execute([$companyId, $today]);
        $inProgressPostsCount = $inProgressPostsStmt->fetchColumn();
        
        \Log::info('Company info fetched:', [
            'company' => $company,
            'has_urls' => !empty($urls),
            'old_posts' => $oldPostsCount,
            'in_progress_posts' => $inProgressPostsCount
        ]);
        
        return response()->json([
            'status' => 'success',
            'company' => $company,
            'urls' => $urls,
            'old_posts' => (int)$oldPostsCount,
            'in_progress_posts' => (int)$inProgressPostsCount,
            'debug' => [
                'token_decoded' => true,
                'company_id' => $companyId
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('GETINFO Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Database error',
            'debug' => [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]
        ], 500);
    }
}

private function getCompanyIdFromToken($token)
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
        if (!isset($decoded->role) || $decoded->role !== 'company') {
            throw new \Exception('Not a company. Role: ' . ($decoded->role ?? 'none'));
        }
        
        if (!isset($decoded->sub) || empty($decoded->sub)) {
            throw new \Exception('No user ID in token');
        }
        
        // Return company ID
        return $decoded->sub;
        
    } catch (\Exception $e) {
        \Log::error('Token decode failed:', [
            'error' => $e->getMessage(),
            'token_sample' => substr($token ?? '', 0, 50)
        ]);
        
        throw new \Exception('Invalid token: ' . $e->getMessage());
    }
}








public function savePost(Request $request)
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
        
        // Get company ID from token
        $companyId = $this->getCompanyIdFromToken($token);
        
        \Log::info('Saving job post for company:', ['companyId' => $companyId]);
        
        // Begin transaction
        $pdo->beginTransaction();
        
        try {
            // Generate custom post ID like: POST_65f3a2b8c1d23
            $postId = 'POST_' . uniqid();
            
            // Insert post with custom ID
            $stmt = $pdo->prepare("
                INSERT INTO posts (id, company_id, title, description, deadline, post_date, job_type, workers_needed, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            
            $stmt->execute([
                $postId,
                $companyId,
                $request->title,
                $request->description,
                $request->deadline,
                $request->postDate,
                $request->type,
                $request->count
            ]);
            
            // Insert skills if any
            if (!empty($request->skillsArray) && is_array($request->skillsArray)) {
                $skillStmt = $pdo->prepare("
                    INSERT INTO job_skills (id, post_id, skill_name) 
                    VALUES (?, ?, ?)
                ");
                
                foreach ($request->skillsArray as $skill) {
                    if (!empty(trim($skill))) {
                        // Generate custom skill ID like: SKILL_65f3a2b8c1d24
                        $skillId = 'SKILL_' . uniqid();
                        $skillStmt->execute([$skillId, $postId, trim($skill)]);
                    }
                }
            }
            
            $pdo->commit();
            
            \Log::info('Job post saved successfully:', [
                'post_id' => $postId,
                'company_id' => $companyId
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Job post created successfully',
                'post_id' => $postId
            ], 201);
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (\Exception $e) {
        \Log::error('Save Post Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to save job post',
            'debug' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

public function getPosts(Request $request)
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
        
        // Get company ID from token
        $companyId = $this->getCompanyIdFromToken($token);
        
        \Log::info('Getting active posts for company:', ['companyId' => $companyId]);
        
        // Get current date
        $today = date('Y-m-d');
        
        // Get posts with deadline >= today (active posts)
        $stmt = $pdo->prepare("
            SELECT 
                p.*,
                (SELECT COUNT(DISTINCT worker_id) FROM test_attempts WHERE post_id = p.id) as applicants_count
            FROM posts p
            WHERE p.company_id = ? 
            AND p.deadline >= ? 
            AND p.status = 'active'
            ORDER BY p.post_date DESC
        ");
        
        $stmt->execute([$companyId, $today]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get skills for each post
        foreach ($posts as &$post) {
            $skillStmt = $pdo->prepare("
                SELECT skill_name FROM job_skills 
                WHERE post_id = ? 
                ORDER BY skill_name
            ");
            $skillStmt->execute([$post['id']]);
            $skills = $skillStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $post['skills'] = $skills;
            $post['skills_count'] = count($skills);
            $post['applicants'] = (int)$post['applicants_count'];
            
            // Remove the raw applicants_count if you don't want it
            unset($post['applicants_count']);
        }
        
        return response()->json([
            'status' => 'success',
            'posts' => $posts,
            'count' => count($posts)
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('Get Posts Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to get posts',
            'debug' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

public function getPostsOld(Request $request)
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
        
        // Get company ID from token
        $companyId = $this->getCompanyIdFromToken($token);
        
        \Log::info('Getting old posts for company:', ['companyId' => $companyId]);
        
        // Get current date
        $today = date('Y-m-d');
        
        // Get posts with deadline < today (expired posts)
        $stmt = $pdo->prepare("
            SELECT 
                p.*,
                (SELECT COUNT(DISTINCT worker_id) FROM test_attempts WHERE post_id = p.id) as applicants_count
            FROM posts p
            WHERE p.company_id = ? 
            AND p.deadline < ? 
            ORDER BY p.deadline DESC
        ");
        
        $stmt->execute([$companyId, $today]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get skills for each post
        foreach ($posts as &$post) {
            $skillStmt = $pdo->prepare("
                SELECT skill_name FROM job_skills 
                WHERE post_id = ? 
                ORDER BY skill_name
            ");
            $skillStmt->execute([$post['id']]);
            $skills = $skillStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $post['skills'] = $skills;
            $post['skills_count'] = count($skills);
            $post['applicants'] = (int)$post['applicants_count'];
            $post['status_label'] = 'Expired';
            
            // Remove the raw applicants_count if you don't want it
            unset($post['applicants_count']);
        }
        
        return response()->json([
            'status' => 'success',
            'posts' => $posts,
            'count' => count($posts)
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('Get Posts Old Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to get old posts',
            'debug' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

public function getpostdetails(Request $request)
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
        
        // Get company ID from token
        $companyId = $this->getCompanyIdFromToken($token);
        
        // Get post ID from request
        $postId = $request->post_id;
        
        if (!$postId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post ID required'
            ], 400);
        }
        
        \Log::info('Getting post details:', [
            'company_id' => $companyId,
            'post_id' => $postId
        ]);
        
        // First verify the post belongs to this company
        $verifyStmt = $pdo->prepare("
            SELECT id, title, post_date, deadline
            FROM posts 
            WHERE id = ? AND company_id = ?
        ");
        $verifyStmt->execute([$postId, $companyId]);
        $post = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found or access denied'
            ], 404);
        }
        
        // Get all applicants for this post with their test results and details
        $applicantsStmt = $pdo->prepare("
            SELECT 
                w.id as worker_id,
                w.fullName as name,
                w.email,
                w.photoUrl,
                w.location,
                w.industry,
                ta.id as test_attempt_id,
                ta.time_taken,
                ta.total_correct,
                ta.total_questions,
                ta.final_score,
                ta.created_at as test_date,
                (
                    SELECT url_github FROM UrlsCompte 
                    WHERE user_id = w.id AND user_type = 'worker'
                    LIMIT 1
                ) as github,
                (
                    SELECT url_linkedin FROM UrlsCompte 
                    WHERE user_id = w.id AND user_type = 'worker'
                    LIMIT 1
                ) as linkedin,
                (
                    SELECT url_website FROM UrlsCompte 
                    WHERE user_id = w.id AND user_type = 'worker'
                    LIMIT 1
                ) as website,
                (
                    SELECT url_gmail FROM UrlsCompte 
                    WHERE user_id = w.id AND user_type = 'worker'
                    LIMIT 1
                ) as gmail,
                (
                    SELECT file_path FROM WorkerCV 
                    WHERE worker_id = w.id 
                    ORDER BY uploaded_at DESC 
                    LIMIT 1
                ) as cv_link
            FROM test_attempts ta
            INNER JOIN workers w ON ta.worker_id = w.id
            WHERE ta.post_id = ?
            ORDER BY ta.final_score DESC, ta.created_at DESC
        ");
        
        $applicantsStmt->execute([$postId]);
        $applicants = $applicantsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // ========== FIX PHOTO URLs ==========
        foreach ($applicants as &$applicant) {
            if (!empty($applicant['photoUrl'])) {
                // Remove /storage/public/ from URL
                $applicant['photoUrl'] = str_replace('/storage/public/', '/storage/', $applicant['photoUrl']);
                
                // Ensure port 8000 is present for localhost
                if (strpos($applicant['photoUrl'], 'http://localhost/') === 0) {
                    $applicant['photoUrl'] = str_replace('http://localhost/', 'http://localhost:8000/', $applicant['photoUrl']);
                }
            }
        }
        
        // Get skill results for each applicant
        foreach ($applicants as &$applicant) {
            $skillResultsStmt = $pdo->prepare("
                SELECT 
                    skill_name as name,
                    correct_answers as score,
                    score_percentage,
                    level
                FROM skill_results 
                WHERE test_attempt_id = ? 
                ORDER BY skill_name
            ");
            $skillResultsStmt->execute([$applicant['test_attempt_id']]);
            $skills = $skillResultsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate totalcore (sum of all skill scores) and max possible
            $totalcore = 0;
            $maxPossible = count($skills) * 12; // 12 questions per skill
            
            // Format skills for frontend
            foreach ($skills as &$skill) {
                // Keep original score (0-12)
                $skill['score'] = (int)$skill['score'];
                $skill['score_percentage'] = $skill['score_percentage'];
                $skill['level'] = $skill['level'];
                
                // Add to totalcore
                $totalcore += $skill['score'];
            }
            
            $applicant['skills'] = $skills;
            $applicant['totalcore'] = $totalcore . '/' . $maxPossible. ' Questions ' ; // Format as "15/36"
            
            // Parse time taken
            $timeParts = explode('/', $applicant['time_taken']);
            $timeUsed = $timeParts[0] ?? '00:00';
            $timeTotal = $timeParts[1] ?? '00:00';
            
            // Format response
            $applicant['finalScore'] = round($applicant['final_score']);
            $applicant['timeUsed'] = $timeUsed;
            $applicant['timeTotal'] = $timeTotal;
            $applicant['cvLink'] = $applicant['cv_link'] ?? '#';
            $applicant['github'] = $applicant['github'] ?? '#';
            $applicant['linkedin'] = $applicant['linkedin'] ?? '#';
            $applicant['website'] = $applicant['website'] ?? null;
            $applicant['email'] = $applicant['email'];
            
            // Use fixed photoUrl or fallback to avatar
            if (empty($applicant['photoUrl'])) {
                $applicant['photoUrl'] = 'https://ui-avatars.com/api/?name=' . urlencode($applicant['name']);
            }
            
            // Remove unnecessary fields
            unset($applicant['worker_id']);
            unset($applicant['test_attempt_id']);
            unset($applicant['total_correct']);
            unset($applicant['total_questions']);
            unset($applicant['final_score']);
            unset($applicant['test_date']);
            unset($applicant['cv_link']);
            unset($applicant['score_percentage']);
            unset($applicant['gmail']);
        }
        
        // Format post info - ONLY title, posted_date, deadline
        $postInfo = [
            'title' => $post['title'],
            'posted_date' => date('M d, Y', strtotime($post['post_date'])),
            'deadline' => date('M d, Y', strtotime($post['deadline']))
        ];
        
        return response()->json([
            'status' => 'success',
            'post' => $postInfo,
            'applicants' => $applicants,
            'count' => count($applicants)
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('Get Post Details Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to get post details',
            'debug' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}
}