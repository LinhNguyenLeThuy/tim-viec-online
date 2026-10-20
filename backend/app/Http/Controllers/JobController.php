<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchJobRequest;
use App\Models\Job;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    public function index(SearchJobRequest $request): JsonResponse
    {
        // Chỉ lấy các tin đang mở tuyển ('active') và chưa hết hạn deadline
        $query = Job::with(['company', 'category', 'skills'])
            ->where('status', 'active')
            ->whereDate('deadline', '>=', now());

        // FR-05.1: Tìm kiếm theo từ khóa (Khớp Tiêu đề việc làm, Tên công ty hoặc Kỹ năng)
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhereHas('company', function ($c) use ($keyword) {
                      $c->where('name', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('skills', function ($s) use ($keyword) {
                      $s->where('name', 'like', "%{$keyword}%");
                  });
            });
        }

        // FR-05.2: Bộ lọc Ngành nghề
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // FR-05.2: Bộ lọc Tỉnh/Thành phố
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        // FR-05.2: Bộ lọc Hình thức làm việc (full_time, part_time, remote, internship)
        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        // FR-05.2 & FR-05.3: Quy tắc lọc mức lương theo số tiền thực tế
        if ($request->filled('salary_range')) {
            $range = $request->salary_range;
            switch ($range) {
                case 'under_10m':
                    // Lương tối đa < 10 triệu
                    $query->where('is_negotiable', 0)
                          ->where('salary_max', '<', 10000000);
                    break;
                case '10m_20m':
                    // Lương tối đa >= 10 triệu VÀ Lương tối thiểu <= 20 triệu
                    $query->where('is_negotiable', 0)
                          ->where('salary_max', '>=', 10000000)
                          ->where('salary_min', '<=', 20000000);
                    break;
                case 'above_20m':
                    // Lương tối thiểu > 20 triệu
                    $query->where('is_negotiable', 0)
                          ->where('salary_min', '>', 20000000);
                    break;
                case 'negotiable':
                    // Chỉ lấy tin lương thỏa thuận
                    $query->where('is_negotiable', 1);
                    break;
            }
        }

        // Lọc theo danh sách kỹ năng (nếu có)
        if ($request->filled('skill_ids')) {
            $skillIds = $request->skill_ids;
            $query->whereHas('skills', function ($q) use ($skillIds) {
                $q->whereIn('skills.id', $skillIds);
            }, '=', count($skillIds)); // Đảm bảo thỏa mãn tất cả các kỹ năng được chọn
        }

        // FR-05.4: Sắp xếp mặc định theo thời gian cập nhật/đẩy tin mới nhất (updated_at / bump_at) giảm dần
        $query->orderBy('updated_at', 'desc');

        // Phân trang kết quả (mặc định 10 items/trang)
        $perPage = $request->input('per_page', 10);
        $jobs = $query->paginate($perPage);

        // FR-05.5: Kiểm tra nếu không có kết quả
        if ($jobs->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Không tìm thấy việc làm phù hợp với tiêu chí tìm kiếm.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách việc làm thành công.',
            'data' => $jobs
        ], 200);
    }

    /**
     * Xem chi tiết một tin tuyển dụng
     */
    public function show($id): JsonResponse
    {
        $job = Job::with(['company', 'category', 'skills'])->find($id);

        if (!$job || $job->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Công việc không tồn tại hoặc đã ngừng tuyển dụng.'
            ], 404);
        }

        // Tăng lượt xem tin (views_count)
        $job->increment('views_count');

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết việc làm thành công.',
            'data' => $job
        ], 200);
    }
}