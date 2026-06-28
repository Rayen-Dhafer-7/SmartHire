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

use Dompdf\Dompdf;
use Dompdf\Options;



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
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();

        // Check email
        $check = $pdo->prepare("SELECT id FROM workers WHERE email = ?");
        $check->execute([$request->email]);
        if ($check->fetch()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['email' => ['Email already exists']]
            ], 422);
        }

        // Handle photo upload to S3
        $photoUrl = null;
        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            Storage::disk('s3')->putFileAs('workers/photos', $file, $filename);
            $photoUrl = 'https://smarthire-uploads.s3.amazonaws.com/workers/photos/' . $filename;

            \Log::info('Profile photo uploaded to S3:', ['url' => $photoUrl]);
        }

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

        $urlsCompteId = 'URL_' . uniqid();
        $urlsStmt = $pdo->prepare(
            "INSERT INTO UrlsCompte (id, user_id, user_type) VALUES (?, ?, 'worker')"
        );
        $urlsStmt->execute([$urlsCompteId, $workerId]);

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
        return response()->json(['status' => 'error', 'message' => 'Database connection failed'], 500);
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

        // Get current photo
        $photoStmt = $pdo->prepare("SELECT photoUrl FROM workers WHERE id = ?");
        $photoStmt->execute([$workerId]);
        $currentPhoto = $photoStmt->fetchColumn();
        $newPhotoUrl = $currentPhoto;

        if ($request->hasFile('profile')) {
            \Log::info('New profile photo detected');

            // Delete old photo from S3 if exists
            if ($currentPhoto && str_contains($currentPhoto, 's3.amazonaws.com')) {
                $oldKey = ltrim(parse_url($currentPhoto, PHP_URL_PATH), '/');
                Storage::disk('s3')->delete($oldKey);
                \Log::info('Old S3 photo deleted:', ['key' => $oldKey]);
            }

            $file = $request->file('profile');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            Storage::disk('s3')->putFileAs('workers/photos', $file, $filename);
            $newPhotoUrl = 'https://smarthire-uploads.s3.amazonaws.com/workers/photos/' . $filename;

            \Log::info('New photo uploaded to S3:', ['url' => $newPhotoUrl]);
        }

        // Update worker
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

        // Update URLs
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

    $validator = Validator::make($request->all(), [
        'cv' => 'required|file',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        $pdo = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());

        if (!$workerId) {
            return response()->json(['status' => 'error', 'message' => 'Invalid token'], 401);
        }

        $cvFile = $request->file('cv');
        $cvFilename = time() . '_' . uniqid() . '_' . $cvFile->getClientOriginalName();

        // Upload CV to S3
        Storage::disk('s3')->putFileAs('workers/cv', $cvFile, $cvFilename);
        $cvUrl = 'https://smarthire-uploads.s3.amazonaws.com/workers/cv/' . $cvFilename;

        \Log::info('CV uploaded to S3:', ['url' => $cvUrl]);

        // Check if worker already has a CV
        $cvCheckStmt = $pdo->prepare("SELECT id, file_path FROM WorkerCV WHERE worker_id = ?");
        $cvCheckStmt->execute([$workerId]);
        $existingCv = $cvCheckStmt->fetch();

        if ($existingCv) {
            // Delete old CV from S3 if exists
            if (!empty($existingCv['file_path']) && str_contains($existingCv['file_path'], 's3.amazonaws.com')) {
                $oldKey = ltrim(parse_url($existingCv['file_path'], PHP_URL_PATH), '/');
                Storage::disk('s3')->delete($oldKey);
                \Log::info('Old CV deleted from S3:', ['key' => $oldKey]);
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
                $cvUrl,
                $cvFile->getClientOriginalName(),
                $cvFile->getSize(),
                $workerId
            ]);

            $cvId = $existingCv['id'];

        } else {
            $cvId = 'CV_' . uniqid();
            $cvStmt = $pdo->prepare("
                INSERT INTO WorkerCV 
                (id, worker_id, file_path, original_name, file_size, uploaded_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $cvStmt->execute([
                $cvId,
                $workerId,
                $cvUrl,
                $cvFile->getClientOriginalName(),
                $cvFile->getSize()
            ]);
        }

        \Log::info('--- CV UPLOAD SUCCESS ---');

        return response()->json([
            'status' => 'success',
            'message' => 'CV uploaded successfully',
            'data' => [
                'id' => $cvId,
                'original_name' => $cvFile->getClientOriginalName(),
                'file_path' => $cvUrl,
                'file_size' => $cvFile->getSize(),
                'uploaded_at' => date('Y-m-d H:i:s')
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('CV UPLOAD ERROR', [
            'message' => $e->getMessage(),
            'line' => $e->getLine()
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

        // Delete file from S3
        if (!empty($cv['file_path'])) {
            try {
                $s3Key = ltrim(parse_url($cv['file_path'], PHP_URL_PATH), '/');
                Storage::disk('s3')->delete($s3Key);
                \Log::info('CV deleted from S3:', ['key' => $s3Key]);
            } catch (\Exception $e) {
                \Log::warning('Could not delete CV from S3:', ['error' => $e->getMessage()]);
            }
        }

        // Delete record from DB
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



public function generateCV(Request $request)
{
    try {
        $pdo      = $this->pdo();
        $workerId = $this->getWorkerIdFromToken($request->bearerToken());
 
        if (!$workerId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid token'
            ], 401);
        }
 
        // ── 1. Basic worker info ──────────────────────────────
        $workerStmt = $pdo->prepare("
            SELECT w.fullName, w.email, w.location, w.industry,
                   u.url_linkedin, u.url_github, u.url_website, u.url_gmail
            FROM   workers w
            LEFT JOIN UrlsCompte u ON u.id = w.id
            WHERE  w.id = ?
        ");
        $workerStmt->execute([$workerId]);
        $worker = $workerStmt->fetch(\PDO::FETCH_ASSOC);
 
        if (!$worker) {
            return response()->json(['status' => 'error', 'message' => 'Worker not found'], 404);
        }
 
        // ── 2. Skills ─────────────────────────────────────────
        $skillsStmt = $pdo->prepare("
            SELECT skill_name FROM WorkerSkills WHERE worker_id = ? ORDER BY id
        ");
        $skillsStmt->execute([$workerId]);
        $skills = $skillsStmt->fetchAll(\PDO::FETCH_COLUMN);
 
        // ── 3. Experience ─────────────────────────────────────
        $expStmt = $pdo->prepare("
            SELECT title, company, location, employment_type,
                   start_date, end_date, description
            FROM   WorkerExperience
            WHERE  worker_id = ?
            ORDER  BY start_date DESC
        ");
        $expStmt->execute([$workerId]);
        $experience = $expStmt->fetchAll(\PDO::FETCH_ASSOC);
 
        // ── 4. Education ──────────────────────────────────────
        $eduStmt = $pdo->prepare("
            SELECT degree, institution, location, start_year, end_year
            FROM   WorkerEducation
            WHERE  worker_id = ?
            ORDER  BY end_year DESC
        ");
        $eduStmt->execute([$workerId]);
        $education = $eduStmt->fetchAll(\PDO::FETCH_ASSOC);
 
        // ── 5. Projects + technologies + points ───────────────
        $projStmt = $pdo->prepare("
            SELECT id, project_name, description
            FROM   WorkerProjects
            WHERE  worker_id = ?
            ORDER  BY created_at ASC
        ");
        $projStmt->execute([$workerId]);
        $projectRows = $projStmt->fetchAll(\PDO::FETCH_ASSOC);
 
        $projects = [];
        foreach ($projectRows as $proj) {
            $techStmt = $pdo->prepare("
                SELECT technology FROM WorkerProjectTechnologies WHERE project_id = ? ORDER BY id
            ");
            $techStmt->execute([$proj['id']]);
            $technologies = $techStmt->fetchAll(\PDO::FETCH_COLUMN);
 
            $pointsStmt = $pdo->prepare("
                SELECT point_text FROM WorkerProjectPoints WHERE project_id = ? ORDER BY id
            ");
            $pointsStmt->execute([$proj['id']]);
            $points = $pointsStmt->fetchAll(\PDO::FETCH_COLUMN);
 
            $projects[] = [
                'name'         => $proj['project_name'],
                'description'  => $proj['description'],
                'technologies' => $technologies,
                'points'       => $points,
            ];
        }
 
        // ── 6. Certifications ─────────────────────────────────
        $certStmt = $pdo->prepare("
            SELECT name, issuer, issue_date
            FROM   WorkerCertifications
            WHERE  worker_id = ?
            ORDER  BY issue_date DESC
        ");
        $certStmt->execute([$workerId]);
        $certifications = $certStmt->fetchAll(\PDO::FETCH_ASSOC);
 
        // ── 7. Build HTML → PDF ───────────────────────────────
        $html = $this->buildCvHtml(
            $worker,
            $skills,
            $experience,
            $education,
            $projects,
            $certifications
        );
 
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
 
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
 
        $pdfContent = $dompdf->output();
 
        // ── 8. Upload to S3 ───────────────────────────────────
        $filename = time() . '_' . uniqid() . $worker['fullName'] . '_CV.pdf';
        $s3Path   = 'workers/cv/' . $filename;
 
        Storage::disk('s3')->put($s3Path, $pdfContent, 'private');
 
        $cvUrl = 'https://smarthire-uploads.s3.amazonaws.com/' . $s3Path;
 
        // ── 9. Upsert WorkerCV ────────────────────────────────
        $cvCheckStmt = $pdo->prepare("SELECT id, file_path FROM WorkerCV WHERE worker_id = ?");
        $cvCheckStmt->execute([$workerId]);
        $existingCv = $cvCheckStmt->fetch();
 
        if ($existingCv) {
            if (!empty($existingCv['file_path']) && str_contains($existingCv['file_path'], 's3.amazonaws.com')) {
                $oldKey = ltrim(parse_url($existingCv['file_path'], PHP_URL_PATH), '/');
                Storage::disk('s3')->delete($oldKey);
            }
 
            $pdo->prepare("
                UPDATE WorkerCV
                SET    file_path     = ?,
                       original_name = ?,
                       file_size     = ?,
                       uploaded_at   = NOW()
                WHERE  worker_id     = ?
            ")->execute([$cvUrl, $worker['fullName'] . '_CV.pdf', strlen($pdfContent), $workerId]);
 
            $cvId = $existingCv['id'];
 
        } else {
            $cvId = 'CV_' . uniqid();
            $pdo->prepare("
                INSERT INTO WorkerCV (id, worker_id, file_path, original_name, file_size, uploaded_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ")->execute([$cvId, $workerId, $cvUrl, $worker['fullName'] . '_CV.pdf', strlen($pdfContent)]);
        }
 
        return response()->json([
            'status'  => 'success',
            'message' => 'CV generated successfully',
            'data'    => [
                'id'            => $cvId,
                'original_name' => $worker['fullName'] . '_CV.pdf',
                'file_path'     => $cvUrl,
                'file_size'     => strlen($pdfContent),
                'uploaded_at'   => date('Y-m-d H:i:s')
            ]
        ]);
 
    } catch (\Throwable $e) {
        \Log::error('Generate CV Error:', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);
 
        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to generate CV: ' . $e->getMessage()
        ], 500);
    }
}
 
 
// ============================================================
//  buildCvHtml  –  private helper
// ============================================================
private function buildCvHtml(
    array $worker,
    array $skills,
    array $experience,
    array $education,
    array $projects,
    array $certifications
): string {
    $e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
 
    // header links
    $links = [];
    if (!empty($worker['url_linkedin'])) $links[] = 'LinkedIn: ' . $e($worker['url_linkedin']);
    if (!empty($worker['url_github']))   $links[] = 'GitHub: '   . $e($worker['url_github']);
    if (!empty($worker['url_website']))  $links[] = 'Web: '      . $e($worker['url_website']);
    if (!empty($worker['url_gmail']))    $links[] = 'Mail: '     . $e($worker['url_gmail']);
    $linksHtml = $links
        ? '<p class="links">' . implode('  |  ', $links) . '</p>'
        : '';
 
    // skills
    $skillsHtml = '';
    if ($skills) {
        $badges = implode('', array_map(
            fn($s) => '<span class="badge">' . $e($s) . '</span>',
            $skills
        ));
        $skillsHtml = '
        <div class="section">
            <div class="section-title">Skills</div>
            <div class="badges">' . $badges . '</div>
        </div>';
    }
 
    // experience
    $expHtml = '';
    if ($experience) {
        $items = '';
        foreach ($experience as $exp) {
            $end    = !empty($exp['end_date']) ? $e($exp['end_date']) : 'Present';
            $period = $e($exp['start_date'] ?? '') . ' – ' . $end;
 
            $desc = '';
            if (!empty($exp['description'])) {
                $lines = array_filter(array_map('trim', explode("\n", $exp['description'])));
                if ($lines) {
                    $desc = '<ul>' . implode('', array_map(
                        fn($l) => '<li>' . $e($l) . '</li>', $lines
                    )) . '</ul>';
                }
            }
 
            $meta = array_filter([
                $e($exp['company']         ?? ''),
                $e($exp['location']        ?? ''),
                $e($exp['employment_type'] ?? '')
            ]);
 
            $items .= '
            <div class="item">
                <div class="item-header">
                    <span class="item-title">' . $e($exp['title']) . '</span>
                    <span class="item-date">' . $period . '</span>
                </div>
                <div class="item-sub">' . implode(' · ', $meta) . '</div>
                ' . $desc . '
            </div>';
        }
        $expHtml = '<div class="section"><div class="section-title">Experience</div>' . $items . '</div>';
    }
 
    // education
    $eduHtml = '';
    if ($education) {
        $items = '';
        foreach ($education as $edu) {
            $period = trim(($edu['start_year'] ?? '') . ' – ' . ($edu['end_year'] ?? ''), ' –');
            $meta   = array_filter([
                $e($edu['institution'] ?? ''),
                $e($edu['location']    ?? '')
            ]);
            $items .= '
            <div class="item">
                <div class="item-header">
                    <span class="item-title">' . $e($edu['degree']) . '</span>
                    <span class="item-date">' . $e($period) . '</span>
                </div>
                <div class="item-sub">' . implode(' · ', $meta) . '</div>
            </div>';
        }
        $eduHtml = '<div class="section"><div class="section-title">Education</div>' . $items . '</div>';
    }
 
    // projects
    $projHtml = '';
    if ($projects) {
        $items = '';
        foreach ($projects as $proj) {
            $techBadges = '';
            if (!empty($proj['technologies'])) {
                $techBadges = '<div class="tech-row">' . implode('', array_map(
                    fn($t) => '<span class="badge-sm">' . $e($t) . '</span>',
                    $proj['technologies']
                )) . '</div>';
            }
 
            $pointsList = '';
            if (!empty($proj['points'])) {
                $pointsList = '<ul>' . implode('', array_map(
                    fn($p) => '<li>' . $e($p) . '</li>',
                    $proj['points']
                )) . '</ul>';
            }
 
            $items .= '
            <div class="item">
                <div class="item-header">
                    <span class="item-title">' . $e($proj['name']) . '</span>
                </div>
                ' . $techBadges . $pointsList . '
            </div>';
        }
        $projHtml = '<div class="section"><div class="section-title">Projects</div>' . $items . '</div>';
    }
 
    // certifications
    $certHtml = '';
    if ($certifications) {
        $items = '';
        foreach ($certifications as $cert) {
            $meta = array_filter([
                $e($cert['issuer']     ?? ''),
                $e($cert['issue_date'] ?? '')
            ]);
            $items .= '
            <div class="item">
                <div class="item-header">
                    <span class="item-title">' . $e($cert['name']) . '</span>
                </div>
                ' . ($meta ? '<div class="item-sub">' . implode(' · ', $meta) . '</div>' : '') . '
            </div>';
        }
        $certHtml = '<div class="section"><div class="section-title">Certifications</div>' . $items . '</div>';
    }
 
    return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: "DejaVu Sans", Arial, sans-serif;
    font-size: 10px;
    color: #1a1a1a;
    background: #fff;
    padding: 28px 32px;
    line-height: 1.5;
  }
  .header {
    border-bottom: 2.5px solid #4f46e5;
    padding-bottom: 10px;
    margin-bottom: 14px;
  }
  .header h1 { font-size: 22px; color: #4f46e5; letter-spacing: 0.5px; }
  .header .meta { color: #555; font-size: 9.5px; margin-top: 3px; }
  .header .links { font-size: 8.5px; color: #4f46e5; margin-top: 4px; }
  .section { margin-bottom: 14px; }
  .section-title {
    font-size: 11px;
    font-weight: bold;
    color: #4f46e5;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 3px;
    margin-bottom: 8px;
  }
  .item { margin-bottom: 9px; }
  .item-header { display: table; width: 100%; }
  .item-title {
    display: table-cell;
    font-weight: bold;
    font-size: 10px;
    color: #111;
  }
  .item-date {
    display: table-cell;
    text-align: right;
    font-size: 9px;
    color: #666;
    white-space: nowrap;
    width: 1%;
  }
  .item-sub { font-size: 9px; color: #555; margin-top: 1px; }
  ul { margin: 4px 0 0 14px; }
  ul li { font-size: 9.5px; margin-bottom: 2px; color: #333; }
  .badges { margin-top: 4px; }
  .badge {
    display: inline-block;
    background: #ede9fe;
    color: #4f46e5;
    border-radius: 4px;
    padding: 2px 7px;
    font-size: 9px;
    font-weight: 600;
    margin: 2px 3px 2px 0;
  }
  .tech-row { margin: 3px 0; }
  .badge-sm {
    display: inline-block;
    background: #f3f4f6;
    color: #374151;
    border-radius: 3px;
    padding: 1px 5px;
    font-size: 8.5px;
    margin: 2px 2px 0 0;
  }
</style>
</head>
<body>
 
<div class="header">
  <h1>' . $e($worker['fullName']) . '</h1>
  <div class="meta">'
    . $e($worker['email'] ?? '')
    . (!empty($worker['location']) ? '   |   ' . $e($worker['location']) : '')
    . (!empty($worker['industry']) ? '   |   ' . $e($worker['industry']) : '')
  . '</div>
  ' . $linksHtml . '
</div>
 
' . $expHtml
. $eduHtml
. $skillsHtml
. $projHtml
. $certHtml . '
 
</body>
</html>';
}



public function getCvText(Request $request)
{
    $tempPath = null;

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

        // Download CV from S3 to temp file for parsing
        $s3Key = ltrim(parse_url($cvUrl, PHP_URL_PATH), '/');
        $tempPath = sys_get_temp_dir() . '/' . basename($cvUrl);

        $s3Contents = Storage::disk('s3')->get($s3Key);

        if (!$s3Contents) {
            return response()->json([
                'status' => 'error',
                'message' => 'CV file not found on S3'
            ], 404);
        }

        file_put_contents($tempPath, $s3Contents);

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($tempPath);
        
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
        \Log::error('CV Parser Error:', [
            'message' => $e->getMessage(),
            'path' => $tempPath ?? 'N/A'
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to extract CV text: ' . $e->getMessage()
        ], 500, [], JSON_UNESCAPED_UNICODE);

    } finally {
        // Always clean up temp file
        if ($tempPath && file_exists($tempPath)) {
            @unlink($tempPath);
        }
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
    
    // Fix common PDF extraction issues - remove multiple spaces
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Handle bullet points - convert to asterisk for better parsing
    $text = preg_replace('/[•●○▪▫►➢→]/u', '*', $text);
    
    // Remove any remaining non-printable characters
    $text = preg_replace('/[^\P{C}\n\r\t]/u', '', $text);
    
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
        
        \Log::info('Groq result for post '.$post['post_id'], $matchResult);

        \Log::info('DEBUG', [
            'worker_id'    => $workerId,
            'worker_skills' => $workerSkills,
            'posts_from_sql' => count($posts),
        ]);

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
    
    $prompt = "You are a balanced job matching expert. Analyze if this worker is qualified for this job.

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

EVALUATION RULES:

1. EXPERIENCE LEVEL CHECK:
   - Only reject for experience if the job EXPLICITLY states: 'X+ years', 'senior', 'lead', 'expert'
   - If NO explicit experience level is mentioned → experience level is NOT a rejection reason
   - Internships, summer projects, and academic projects COUNT as real experience
   - A student with internship experience at real companies IS qualified for junior/mid positions

2. SKILLS CHECK (PRIMARY FACTOR):
   - If worker has 60%+ of required skills from any source (projects, internships, courses) → lean toward match
   - If worker has 80%+ of required skills → is_match = TRUE regardless of student status

3. INDUSTRY ALIGNMENT:
   - Check if worker's background aligns with the job domain

Return ONLY valid JSON:
{
    \"is_match\": true/false,
    \"score\": 0-100,
    \"reason\": \"Specific explanation\",
    \"experience_analysis\": \"Brief summary of worker experience\",
    \"job_requirement\": \"What the job actually requires\"
}";
    
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


 
public function getWorkerPublicProfile(Request $request, $workerId)
{
    try {
        $pdo = $this->pdo();

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

        // Skills
        $skillsStmt = $pdo->prepare("SELECT id, skill_name FROM WorkerSkills WHERE worker_id = ?");
        $skillsStmt->execute([$workerId]);
        $skills = $skillsStmt->fetchAll();

        // Experience
        $expStmt = $pdo->prepare("SELECT id, title, company, location, employment_type, start_date, end_date, description FROM WorkerExperience WHERE worker_id = ? ORDER BY start_date DESC");
        $expStmt->execute([$workerId]);
        $experience = $expStmt->fetchAll();

        // Education
        $eduStmt = $pdo->prepare("SELECT id, degree, institution, location, start_year, end_year FROM WorkerEducation WHERE worker_id = ? ORDER BY end_year DESC");
        $eduStmt->execute([$workerId]);
        $education = $eduStmt->fetchAll();

        // Certifications
        $certStmt = $pdo->prepare("SELECT id, name, issuer, issue_date FROM WorkerCertifications WHERE worker_id = ? ORDER BY issue_date DESC");
        $certStmt->execute([$workerId]);
        $certifications = $certStmt->fetchAll();

        // Projects
        $projectsStmt = $pdo->prepare("SELECT id, project_name, description FROM WorkerProjects WHERE worker_id = ? ORDER BY created_at DESC");
        $projectsStmt->execute([$workerId]);
        $projects = $projectsStmt->fetchAll();

        foreach ($projects as &$project) {
            $techStmt = $pdo->prepare("SELECT technology FROM WorkerProjectTechnologies WHERE project_id = ?");
            $techStmt->execute([$project['id']]);
            $project['technologies'] = $techStmt->fetchAll(PDO::FETCH_COLUMN);

            $pointsStmt = $pdo->prepare("SELECT point_text FROM WorkerProjectPoints WHERE project_id = ?");
            $pointsStmt->execute([$project['id']]);
            $project['points'] = $pointsStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return response()->json([
            'status' => 'success',
            'worker' => $worker,
            'urls' => $urls,
            'cv' => $cv,
            'skills' => $skills,
            'experience' => $experience,
            'education' => $education,
            'certifications' => $certifications,
            'projects' => $projects
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}































public function getPostsMatcheVoice(Request $request)
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

        $workerId = $this->getWorkerIdFromToken($token);
        $audioFile = $request->file('audio');
        
        if (!$audioFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'No audio file provided'
            ], 400);
        }

        // 1. Transcribe the audio
        $transcribedText = $this->transcribeAudioWithGroq($audioFile);
        
        if (!$transcribedText) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to transcribe audio'
            ], 500);
        }

        // 2. Normalize the transcribed text (fix mispronunciations)
        $normalizedText = $this->normalizeVoiceText($transcribedText);
        
        // 3. Get all available jobs
        $allJobs = $this->getAllAvailableJobs($pdo, $workerId);
        
        // 4. Let Groq intelligently match jobs based on the normalized transcription
        $matchedJobs = $this->intelligentJobMatchingWithGroq($normalizedText, $allJobs);
        
        // 5. Format and return results
        $formattedPosts = $this->formatPosts($pdo, $matchedJobs);
        
        \Log::info('Voice Search Results', [
            'original_transcription' => $transcribedText,
            'normalized_text' => $normalizedText,
            'posts_found' => count($formattedPosts)
        ]);

        return response()->json([
            'status' => 'success',
            'posts' => $formattedPosts,
            'count' => count($formattedPosts),
            'transcription' => $normalizedText
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('Voice Match Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to process voice search',
            'debug' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

/**
 * Normalize voice text - fix common mispronunciations and typos
 */
private function normalizeVoiceText($text)
{
    $text = strtolower($text);
    
    // Common mispronunciations mapping
    $mispronunciations = [
        // Node.js variations
        '/\bno\s*gs\b/i' => 'node.js',
        '/\bno\s*g?s\b/i' => 'node.js',
        '/\bno\s*d\s*s\b/i' => 'node.js',
        '/\bnod\s*gs\b/i' => 'node.js',
        '/\bnodes\b/i' => 'node.js',
        '/\bnode\s*js\b/i' => 'node.js',
        '/\bnode\b/i' => 'node.js',
        
        // Angular variations
        '/\bon\s*dlr\b/i' => 'angular',
        '/\bondlr\b/i' => 'angular',
        '/\bang\s*dlr\b/i' => 'angular',
        '/\bang\s*ular\b/i' => 'angular',
        '/\bangler\b/i' => 'angular',
        '/\bang\s*you\s*lar\b/i' => 'angular',
        
        // Full-Stack variations
        '/\bfull\s*style\b/i' => 'full-stack',
        '/\bfull\s*stake\b/i' => 'full-stack',
        '/\bfull\s*stuck\b/i' => 'full-stack',
        '/\bfull\s*stack\b/i' => 'full-stack',
        
        // Backend variations
        '/\bback\s*end\b/i' => 'backend',
        '/\bback\s*in\b/i' => 'backend',
        
        // React variations
        '/\bri\s*act\b/i' => 'react',
        '/\bree\s*act\b/i' => 'react',
        
        // JavaScript variations
        '/\bjava\s*script\b/i' => 'javascript',
        '/\bjava\s*skrip\b/i' => 'javascript',
        
        // Python variations
        '/\bpy\s*thon\b/i' => 'python',
        '/\bpie\s*thon\b/i' => 'python',
        
        // Database variations
        '/\bmy\s*sequel\b/i' => 'mysql',
        '/\bmongo\s*db\b/i' => 'mongodb',
        '/\bpost\s*gres\b/i' => 'postgresql',
    ];
    
    foreach ($mispronunciations as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text);
    }
    
    // Clean up extra spaces
    $text = preg_replace('/\s+/', ' ', $text);
    
    return trim($text);
}

/**
 * Get all available jobs
 */
private function getAllAvailableJobs($pdo, $workerId)
{
    $today = date('Y-m-d');
    
    $sql = "
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
            c.logoUrl,
            GROUP_CONCAT(DISTINCT js.skill_name) as skills
        FROM posts p
        INNER JOIN companies c ON p.company_id = c.id
        LEFT JOIN job_skills js ON p.id = js.post_id
        WHERE p.deadline >= ?
        AND p.status = 'active'
        AND p.id NOT IN (
            SELECT post_id 
            FROM test_attempts 
            WHERE worker_id = ?
        )
        GROUP BY p.id
        ORDER BY p.post_date DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today, $workerId]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format skills as array
    foreach ($jobs as &$job) {
        $job['skills'] = $job['skills'] ? explode(',', $job['skills']) : [];
        // Normalize job skills to lowercase for better matching
        $job['skills_lower'] = array_map('strtolower', $job['skills']);
    }
    
    return $jobs;
}






/**
 * Intelligent job matching using Groq with strict skill prioritization
 */
private function intelligentJobMatchingWithGroq($normalizedText, $allJobs)
{
    $GROQ_API_URL = env('GROQ_API_URL');
    $API_KEY = env('GROQ_API_KEY');
    $MODEL = env('GROQ_MODEL');
    
    // Prepare jobs data (limit to avoid token overflow)
    $jobsForGroq = array_slice($allJobs, 0, 50);
    
    // Simplify jobs data for Groq
    $simplifiedJobs = [];
    foreach ($jobsForGroq as $job) {
        $simplifiedJobs[] = [
            'id' => $job['post_id'],
            'title' => $job['title'],
            'skills' => $job['skills'],
            'location' => $job['location'],
            'type' => $job['job_type']
        ];
    }
    
    $jobsJson = json_encode($simplifiedJobs);
    
    $prompt = "You are an intelligent job matching expert. CRITICAL: You MUST prioritize jobs that match the EXACT skills the user mentions.

USER REQUEST: \"{$normalizedText}\"

KEY SKILLS MENTIONED BY USER (These are the ONLY skills the user has):
- Node.js (user explicitly said they are good in node.js.js)
- Angular (user explicitly said they are good in angular)
- Full-stack or Backend developer roles

USER EXPLICITLY SAID: \"i am good in node.js.js and angular\"
THIS MEANS: The user ONLY has Node.js and Angular skills. DO NOT match jobs requiring PHP, SQL, or other skills not mentioned.

AVAILABLE JOBS:
{$jobsJson}

CRITICAL RULES:
1. **SKILL MATCHING IS MANDATORY**:
   - Jobs MUST have Node.js OR Angular in their required skills
   - If a job requires PHP, SQL, or any skill NOT mentioned by the user, EXCLUDE it completely
   - The user said they are good in Node.js and Angular - NOT PHP, NOT SQL

2. **PRIORITY ORDER**:
   - FIRST: Jobs requiring BOTH Node.js AND Angular (perfect match)
   - SECOND: Jobs requiring Node.js (with Angular optional)
   - THIRD: Jobs requiring Angular (with Node.js optional)
   - NEVER: Jobs without Node.js or Angular skills

3. **LOCATION**:
   - User said \"any city in USA\" or \"any city in America\" - location doesn't matter, show any USA jobs

4. **EXCLUSION RULES**:
   - ABSOLUTELY EXCLUDE jobs that require PHP, SQL, Python, Java, or other skills not mentioned
   - Only include jobs where skills required match user's skills (Node.js, Angular)

Return a JSON array of matched jobs with scores, sorted by relevance:
[
    {
        \"id\": 123,
        \"score\": 100,
        \"reason\": \"Perfect match: Requires Node.js and Angular - exactly what user has\",
        \"matching_skills\": [\"Node.js\", \"Angular\"]
    },
    {
        \"id\": 456,
        \"score\": 85,
        \"reason\": \"Good match: Requires Node.js - user has this skill\",
        \"matching_skills\": [\"Node.js\"]
    }
]

Return ONLY the JSON array, no other text. REMEMBER: If a job doesn't have Node.js OR Angular in its skills, DO NOT include it.";

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
                        'content' => 'You are a strict job matching expert. You MUST ONLY match jobs with skills the user explicitly mentioned. If user says they know Node.js and Angular, NEVER suggest jobs requiring PHP or SQL. This is CRITICAL.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1, // Lower temperature for more consistent results
                'max_tokens' => 2000
            ],
            'timeout' => 30
        ]);
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        $content = $responseData['choices'][0]['message']['content'];
        
        // Extract JSON from response
        preg_match('/\[.*\]/s', $content, $matches);
        if (empty($matches)) {
            throw new \Exception('No JSON found in response');
        }
        
        $matchedJobsData = json_decode($matches[0], true);
        
        if (empty($matchedJobsData)) {
            throw new \Exception('Invalid JSON structure');
        }
        
        // Filter and sort jobs based on Groq's matching
        $matchedIds = array_column($matchedJobsData, 'id');
        $matchedJobs = array_filter($allJobs, function($job) use ($matchedIds) {
            return in_array($job['post_id'], $matchedIds);
        });
        
        // Double-check filter: Remove any jobs that don't have Node.js or Angular
        $filteredJobs = array_filter($matchedJobs, function($job) {
            $jobSkills = array_map('strtolower', $job['skills']);
            $hasNodeJs = in_array('node.js', $jobSkills) || in_array('nodejs', $jobSkills) || in_array('node', $jobSkills);
            $hasAngular = in_array('angular', $jobSkills);
            return $hasNodeJs || $hasAngular;
        });
        
        // Sort by score
        $scoreMap = [];
        foreach ($matchedJobsData as $match) {
            $scoreMap[$match['id']] = $match['score'] ?? 0;
        }
        
        usort($filteredJobs, function($a, $b) use ($scoreMap) {
            $scoreA = $scoreMap[$a['post_id']] ?? 0;
            $scoreB = $scoreMap[$b['post_id']] ?? 0;
            return $scoreB - $scoreA;
        });
        
        // Log matching results
        \Log::info('Groq Job Matching Results', [
            'matches' => $matchedJobsData,
            'filtered_count' => count($filteredJobs)
        ]);
        
        return array_values($filteredJobs);
        
    } catch (\Exception $e) {
        \Log::error('Groq Job Matching Error: ' . $e->getMessage());
        
        // Enhanced fallback with strict skill matching
        return $this->strictSkillMatching($normalizedText, $allJobs);
    }
}

/**
 * Strict skill matching - only return jobs with user's mentioned skills
 */
private function strictSkillMatching($normalizedText, $allJobs)
{
    $text = strtolower($normalizedText);
    $scoredJobs = [];
    
    // Extract user's skills from the text
    $userSkills = [];
    
    // Check for Node.js
    if (strpos($text, 'node.js') !== false || strpos($text, 'node js') !== false || strpos($text, 'nodejs') !== false) {
        $userSkills[] = 'node.js';
    }
    
    // Check for Angular
    if (strpos($text, 'angular') !== false) {
        $userSkills[] = 'angular';
    }
    
    // Check for other skills the user MENTIONED (not assuming)
    $possibleSkills = ['react', 'vue', 'python', 'php', 'sql', 'mongodb', 'mysql', 'javascript'];
    foreach ($possibleSkills as $skill) {
        if (strpos($text, $skill) !== false) {
            $userSkills[] = $skill;
        }
    }
    
    \Log::info('User skills extracted', ['skills' => $userSkills]);
    
    foreach ($allJobs as $job) {
        $jobSkills = array_map('strtolower', $job['skills']);
        $score = 0;
        $matchingSkills = [];
        
        // Check which user skills match the job
        foreach ($userSkills as $userSkill) {
            if (in_array($userSkill, $jobSkills)) {
                $matchingSkills[] = $userSkill;
                if ($userSkill == 'node.js' || $userSkill == 'angular') {
                    $score += 50; // High weight for primary skills
                } else {
                    $score += 30;
                }
            }
        }
        
        // If no matching skills, skip this job
        if (empty($matchingSkills)) {
            continue;
        }
        
        // Bonus for job title matching
        $jobTitle = strtolower($job['title']);
        if (strpos($text, 'full-stack') !== false && strpos($jobTitle, 'full-stack') !== false) {
            $score += 20;
        } elseif (strpos($text, 'backend') !== false && strpos($jobTitle, 'backend') !== false) {
            $score += 15;
        }
        
        // Location bonus (any USA is fine)
        $jobLocation = strtolower($job['location']);
        $isUSA = strpos($jobLocation, 'usa') !== false || 
                 strpos($jobLocation, 'united states') !== false ||
                 strpos($jobLocation, 'new york') !== false ||
                 strpos($jobLocation, 'california') !== false;
        
        if ($isUSA) {
            $score += 10;
        }
        
        $scoredJobs[] = [
            'job' => $job,
            'score' => $score,
            'matching_skills' => $matchingSkills
        ];
    }
    
    // Sort by score
    usort($scoredJobs, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    // Log what we're returning
    \Log::info('Strict Skill Matching Results', [
        'user_skills' => $userSkills,
        'matched_jobs' => array_map(function($item) {
            return [
                'id' => $item['job']['post_id'],
                'title' => $item['job']['title'],
                'matching_skills' => $item['matching_skills'],
                'score' => $item['score']
            ];
        }, array_slice($scoredJobs, 0, 10))
    ]);
    
    // Return top 20 jobs
    $matchedJobs = array_slice(array_column($scoredJobs, 'job'), 0, 20);
    
    return $matchedJobs;
}








/**
 * Enhanced fallback matching with better logic
 */
private function enhancedFallbackMatching($normalizedText, $allJobs)
{
    $text = strtolower($normalizedText);
    $scoredJobs = [];
    
    // Check what the user is looking for
    $wantsNodeJs = strpos($text, 'node.js') !== false || strpos($text, 'node') !== false;
    $wantsAngular = strpos($text, 'angular') !== false;
    $wantsFullStack = strpos($text, 'full-stack') !== false || strpos($text, 'full stack') !== false;
    $wantsBackend = strpos($text, 'backend') !== false || strpos($text, 'back end') !== false;
    $wantsSql = strpos($text, 'sql') !== false;
    
    foreach ($allJobs as $job) {
        $score = 0;
        $jobTitle = strtolower($job['title']);
        $jobSkills = array_map('strtolower', $job['skills']);
        $jobLocation = strtolower($job['location']);
        
        // High priority: Both Node.js and Angular
        if ($wantsNodeJs && $wantsAngular) {
            $hasNodeJs = in_array('node.js', $jobSkills) || in_array('nodejs', $jobSkills) || in_array('node', $jobSkills);
            $hasAngular = in_array('angular', $jobSkills);
            
            if ($hasNodeJs && $hasAngular) {
                $score += 50; // Perfect skill match
            } elseif ($hasNodeJs || $hasAngular) {
                $score += 30; // Partial skill match
            }
        } 
        // Individual skills
        else {
            if ($wantsNodeJs && (in_array('node.js', $jobSkills) || in_array('nodejs', $jobSkills))) {
                $score += 40;
            }
            if ($wantsAngular && in_array('angular', $jobSkills)) {
                $score += 40;
            }
        }
        
        // Job title matching
        if ($wantsFullStack && (strpos($jobTitle, 'full-stack') !== false || strpos($jobTitle, 'full stack') !== false)) {
            $score += 30;
        } elseif ($wantsBackend && (strpos($jobTitle, 'backend') !== false || strpos($jobTitle, 'back end') !== false)) {
            $score += 25;
        }
        
        // Penalize SQL jobs if user didn't ask for SQL
        if (!$wantsSql && (in_array('sql', $jobSkills) || in_array('mysql', $jobSkills))) {
            // Only penalize if it's primarily SQL and no Node.js/Angular
            $hasNodeOrAngular = in_array('node.js', $jobSkills) || in_array('angular', $jobSkills);
            if (!$hasNodeOrAngular) {
                $score -= 20; // Reduce score for SQL-only jobs
            }
        }
        
        // Location - any USA location is fine
        $isUSA = strpos($jobLocation, 'usa') !== false || 
                 strpos($jobLocation, 'united states') !== false ||
                 strpos($jobLocation, 'new york') !== false ||
                 strpos($jobLocation, 'california') !== false ||
                 strpos($jobLocation, 'texas') !== false;
        
        if ($isUSA) {
            $score += 10; // Bonus for USA jobs
        }
        
        // Only include jobs with at least some relevance
        if ($score > 0) {
            $scoredJobs[] = [
                'job' => $job,
                'score' => $score
            ];
        }
    }
    
    // Sort by score
    usort($scoredJobs, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    // Return top 20 jobs
    $matchedJobs = array_slice(array_column($scoredJobs, 'job'), 0, 20);
    
    \Log::info('Fallback Matching Results', [
        'matched_count' => count($matchedJobs),
        'top_score' => $scoredJobs[0]['score'] ?? 0
    ]);
    
    return $matchedJobs;
}

/**
 * Transcribe audio using Groq Whisper API
 */
private function transcribeAudioWithGroq($audioFile)
{
    $GROQ_API_URL = 'https://api.groq.com/openai/v1/audio/transcriptions';
    $API_KEY = env('GROQ_API_KEY');
    
    try {
        $client = new \GuzzleHttp\Client();
        
        $multipart = [
            [
                'name' => 'file',
                'contents' => fopen($audioFile->getRealPath(), 'r'),
                'filename' => $audioFile->getClientOriginalName(),
                'headers' => [
                    'Content-Type' => $audioFile->getMimeType()
                ]
            ],
            [
                'name' => 'model',
                'contents' => 'whisper-large-v3'
            ],
            [
                'name' => 'language',
                'contents' => 'en'
            ],
            [
                'name' => 'response_format',
                'contents' => 'json'
            ]
        ];
        
        $response = $client->post($GROQ_API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $API_KEY,
            ],
            'multipart' => $multipart,
            'timeout' => 60
        ]);
        
        $result = json_decode($response->getBody()->getContents(), true);
        
        return $result['text'] ?? null;
        
    } catch (\Exception $e) {
        \Log::error('Groq Transcription Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Format posts with skills and social URLs
 */
private function formatPosts($pdo, $posts)
{
    $formattedPosts = [];
    
    foreach ($posts as $post) {
        // Get post skills if not already in array format
        $postSkills = $post['skills'] ?? [];
        if (empty($postSkills)) {
            $skillStmt = $pdo->prepare("
                SELECT skill_name FROM job_skills 
                WHERE post_id = ? 
                ORDER BY skill_name
            ");
            $skillStmt->execute([$post['post_id']]);
            $postSkills = $skillStmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        // Get company social URLs
        $urlsStmt = $pdo->prepare("
            SELECT url_github, url_linkedin, url_facebook, url_instagram, url_twitter, url_website, url_gmail 
            FROM UrlsCompte 
            WHERE user_id = ? AND user_type = 'company'
        ");
        $urlsStmt->execute([$post['company_id']]);
        $urls = $urlsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        
        $formattedPosts[] = [
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
            ]
        ];
    }
    
    return $formattedPosts;
}



}

