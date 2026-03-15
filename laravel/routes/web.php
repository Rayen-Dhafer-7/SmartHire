<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\AuthController;



Route::options('/{any}', function () {
    return response()->json([], 200, [
        'Access-Control-Allow-Origin' => 'http://localhost:5174',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
    ]);
})->where('any', '.*');




// Route 2: Prefixed route  
Route::prefix('worker')->group(function () {
    Route::post('/register', [WorkerController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/info', [WorkerController::class, 'getinfo']);
    Route::post('/update', [WorkerController::class, 'updateinfo']);
    Route::put('/update-password', [WorkerController::class, 'updatepass']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);


        // New routes for profile sections
    Route::post('/skill/add', [WorkerController::class, 'addSkill']);
    Route::delete('/skill/remove/{skillId}', [WorkerController::class, 'removeSkill']);
    
    Route::post('/experience/add', [WorkerController::class, 'addExperience']);
    Route::delete('/experience/remove/{experienceId}', [WorkerController::class, 'removeExperience']);
    
    Route::post('/project/add', [WorkerController::class, 'addProject']);
    Route::delete('/project/remove/{projectId}', [WorkerController::class, 'removeProject']);
    
    Route::post('/certification/add', [WorkerController::class, 'addCertification']);
    Route::delete('/certification/remove/{certificationId}', [WorkerController::class, 'removeCertification']);
    
    Route::post('/education/add', [WorkerController::class, 'addEducation']);
    Route::delete('/education/remove/{educationId}', [WorkerController::class, 'removeEducation']);
    
    Route::get('/profile/data', [WorkerController::class, 'getAllProfileData']);

    Route::delete('/cv/remove/{cvId}', [WorkerController::class, 'deleteCv']);
    Route::post('/cv/text', [WorkerController::class, 'getCvText']);

    Route::post('/cv/upload', [WorkerController::class, 'uploadCV']);  

    Route::get('/getPosts', [WorkerController::class, 'GetPosts']);
    Route::get('/getPostsMatche', [WorkerController::class, 'getPostsMatche']);

    Route::get('/getTakenPosts', [WorkerController::class, 'getTakenPosts']);

    Route::post('/geretest', [WorkerController::class, 'geretest']);
    Route::post('/savetest', [WorkerController::class, 'savetest']);


});

 Route::prefix('company')->group(function () {
    Route::post('/register', [CompanyController::class, 'register']);
    Route::get('/info', [CompanyController::class, 'getinfo']);
    Route::post('/update', [CompanyController::class, 'updateinfo']);
    Route::put('/update-password', [CompanyController::class, 'updatepass']);

    Route::post('/send-reset-mail', [AuthController::class, 'sendMail']);
    Route::post('/savePost', [CompanyController::class, 'savePost']);
    Route::get('/getPosts', [CompanyController::class, 'GetPosts']);
    Route::get('/getOld', [CompanyController::class, 'GetPostsOld']);
    Route::post('/getpostdetails', [CompanyController::class, 'getpostdetails']);



});





Route::get('/debug-speed', function() {
    $start = microtime(true);
    
    try {
        $pdo = new PDO(
            "mysql:host=" . env('DB_HOST') . ";port=" . env('DB_PORT') . ";dbname=" . env('DB_DATABASE'),
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            [PDO::MYSQL_ATTR_SSL_CA => base_path(env('DB_SSL_CA', 'certs/ca.pem'))]  // ← fixed

        );
        
        $connectTime = microtime(true) - $start;
        
        // Test query speed
        $queryStart = microtime(true);
        $stmt = $pdo->query("SELECT 1");
        $queryTime = microtime(true) - $queryStart;
        
        return response()->json([
            'connect_time_ms' => round($connectTime * 1000, 2),
            'query_time_ms' => round($queryTime * 1000, 2),
            'total_time_ms' => round((microtime(true) - $start) * 1000, 2),
            'aiven_host' => env('DB_HOST')
        ]);
        
    } catch (PDOException $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});


 