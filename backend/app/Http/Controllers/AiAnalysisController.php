<?php

namespace App\Http\Controllers;

use App\Models\AiCvAnalysis;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAnalysisController extends Controller
{
    /**
     * FR-07: Xem kết quả AI phân tích CV cho đơn ứng tuyển
     */
    public function show(Request $request, $applicationId): JsonResponse
    {
        $user = $request->user();

        // Tìm đơn ứng tuyển
        $application = Application::with(['job.company', 'candidateProfile.user'])->find($applicationId);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ ứng tuyển.'
            ], 404);
        }

        // Kiểm tra quyền: Chỉ Ứng viên nộp đơn hoặc Nhà tuyển dụng sở hữu tin tuyển dụng đó mới được xem
        $isOwnerCandidate = $user->role === 'candidate' && $application->candidateProfile->user_id === $user->id;
        $isOwnerEmployer = $user->role === 'employer' && $application->job->company->user_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isOwnerCandidate && !$isOwnerEmployer && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem kết quả phân tích AI của đơn này.'
            ], 403);
        }

        // Lấy kết quả AI phân tích đã lưu vĩnh viễn (FR-07.5)
        $analysis = AiCvAnalysis::where('application_id', $applicationId)->first();

        if (!$analysis) {
            return response()->json([
                'success' => false,
                'message' => 'Hồ sơ này chưa được AI phân tích hoặc đang trong quá trình xử lý.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy kết quả phân tích AI thành công.',
            'data' => [
                'application_id' => $application->id,
                'job_title' => $application->job->title,
                'candidate_name' => $application->candidateProfile->user->name,
                'match_score' => $analysis->match_score,
                'summary' => $analysis->summary,
                'strengths' => $analysis->strengths,
                'matched_skills' => $analysis->matched_skills,
                'missing_skills' => $analysis->missing_skills,
                'recommendations' => $analysis->recommendations,
                'analyzed_at' => $analysis->created_at,
            ]
        ], 200);
    }
}