<?php
use App\Http\Controllers\AuthController;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
use App\Http\Controllers\CandidateProfileController;
Route::middleware(['auth:sanctum'])->prefix('candidate')->group(function () {
    Route::get('/profile', [CandidateProfileController::class, 'show']);
    Route::put('/profile', [CandidateProfileController::class, 'update']);
    Route::post('/cv', [CandidateProfileController::class, 'uploadCv']);
    Route::get('/cv', [CandidateProfileController::class, 'getCv']);
    Route::delete('/cv', [CandidateProfileController::class, 'deleteCv']);
    Route::post('/avatar', [CandidateProfileController::class, 'uploadAvatar']);
    Route::delete('/avatar', [CandidateProfileController::class, 'deleteAvatar']);
});
use App\Http\Controllers\JobController;
Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{id}', [JobController::class, 'show']);
use App\Http\Controllers\AiAnalysisController;
Route::middleware(['auth:sanctum'])->prefix('applications')->group(function () {
    Route::post('/{applicationId}/ai-analyze', [AiAnalysisController::class, 'analyzeAndStore']);
    Route::get('/{applicationId}/ai-analysis', [AiAnalysisController::class, 'show']);
});
use App\Http\Controllers\AdminController;
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Kiểm duyệt tin tuyển dụng (FR-09.1, FR-09.2)
    Route::get('/jobs/reported', [AdminController::class, 'getReportedJobs']);
    Route::delete('/jobs/{id}', [AdminController::class, 'deleteReportedJob']);

    // Quản lý người dùng & Khóa tài khoản (FR-09.4)
    Route::get('/users', [AdminController::class, 'users']);
    Route::patch('/users/{id}/toggle-lock', [AdminController::class, 'toggleLockUser']);
    Route::get('/users/{user_id}', [AdminController::class, 'userDetail']);
    // Quản lý danh mục ngành nghề & kỹ năng (FR-09.5)
    Route::get('/categories', [AdminController::class, 'categories']);
    Route::post('/categories', [AdminController::class, 'storeCategory']);
    Route::put('/categories/{id}', [AdminController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [AdminController::class, 'destroyCategory']);
    Route::get('/skills', [AdminController::class, 'skills']);
    Route::post('/skills', [AdminController::class, 'storeSkill']);
    Route::put('/skills/{id}', [AdminController::class, 'updateSkill']);
    Route::delete('/skills/{id}', [AdminController::class, 'destroySkill']);
    // Thống kê hệ thống
    Route::get('/stats', [AdminController::class, 'stats']);
    // Quản lý tin tuyển dụng
    Route::get('/jobs', [AdminController::class, 'jobs']);
    Route::get('/jobs/{job_id}', [AdminController::class, 'jobDetail']);
});