<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Category;
use App\Models\Skill;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Lấy danh sách tin tuyển dụng bị báo cáo vi phạm hoặc toàn bộ tin để kiểm duyệt
     */
    public function getReportedJobs(Request $request): JsonResponse
    {
        $query = Job::with(['company.user', 'category']);

        // Lọc theo trạng thái báo cáo nếu có yêu cầu
        if ($request->boolean('reported_only', true)) {
            $query->where('is_reported', 1);
        }

        $jobs = $query->orderBy('updated_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tin tuyển dụng cần kiểm duyệt thành công.',
            'data' => $jobs
        ], 200);
    }

    /**
     * Xóa tin tuyển dụng vi phạm và thông báo cho Nhà tuyển dụng (FR-09.2)
     */
    public function deleteReportedJob(Request $request, $id): JsonResponse
    {
        $job = Job::with('company.user')->find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Tin tuyển dụng không tồn tại.'
            ], 404);
        }

        $reason = $request->input('reason', 'Tin tuyển dụng vi phạm chính sách kiểm duyệt của hệ thống.');

        try {
            DB::transaction(function () use ($job, $reason) {
                // Gửi thông báo cho nhà tuyển dụng sở hữu tin
                if ($job->company && $job->company->user) {
                    Notification::create([
                        'user_id' => $job->company->user->id,
                        'title' => 'Tin tuyển dụng bị gỡ bỏ',
                        'content' => "Tin tuyển dụng '{$job->title}' đã bị quản trị viên gỡ bỏ. Lý do: {$reason}",
                        'type' => 'job_deleted_by_admin',
                        'data' => ['job_id' => $job->id]
                    ]);
                }

                // Xóa tin tuyển dụng (Dữ liệu liên quan như job_skills, applications sẽ tự động xóa nhờ cascade trên DB)
                $job->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tin tuyển dụng vi phạm và gửi thông báo thành công.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa tin tuyển dụng thất bại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Xem danh sách người dùng, tìm kiếm theo tên/email, lọc theo vai trò và trạng thái
     */
    public function getUsers(Request $request): JsonResponse
    {
        $query = User::query();

        // Tìm kiếm theo từ khóa (tên hoặc email)
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo vai trò (candidate, employer, admin)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Lọc theo trạng thái (active, locked)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách người dùng thành công.',
            'data' => $users
        ], 200);
    }

    /**
     * Khóa hoặc Mở khóa tài khoản người dùng (FR-09.4)
     */
    public function toggleLockUser(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản người dùng.'
            ], 404);
        }

        // Không cho phép admin tự khóa tài khoản chính mình
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể tự khóa tài khoản của chính mình.'
            ], 400);
        }

        // Chuyển đổi trạng thái active <-> locked
        $newStatus = $user->status === 'active' ? 'locked' : 'active';
        
        $user->update([
            'status' => $newStatus,
            // Nếu khóa tài khoản, có thể xóa toàn bộ token phiên làm việc hiện tại để hủy phiên đăng nhập ngay lập tức (FR-09.4)
        ]);

        if ($newStatus === 'locked') {
            $user->tokens()->delete(); // Hủy toàn bộ token đăng nhập hiện tại
        }

        $message = $newStatus === 'locked' ? 'Đã khóa tài khoản thành công.' : 'Đã mở khóa tài khoản thành công.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'user_id' => $user->id,
                'status' => $user->status
            ]
        ], 200);
    }

    // --- Quản lý Categories ---
    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:150|unique:categories,name']);
        $category = Category::create(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm danh mục ngành nghề thành công.',
            'data' => $category
        ], 201);
    }

    public function updateCategory(Request $request, $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['success' => false, 'message' => 'Danh mục không tồn tại.'], 404);

        $request->validate(['name' => 'required|string|max:150|unique:categories,name,' . $id]);
        $category->update(['name' => $request->name]);

        return response()->json(['success' => true, 'message' => 'Cập nhật danh mục thành công.', 'data' => $category]);
    }

    public function destroyCategory($id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['success' => false, 'message' => 'Danh mục không tồn tại.'], 404);

        try {
            $category->delete();
            return response()->json(['success' => true, 'message' => 'Xóa danh mục ngành nghề thành công.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa danh mục đang có tin tuyển dụng liên kết.'], 400);
        }
    }

    // --- Quản lý Skills ---
    public function storeSkill(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100|unique:skills,name']);
        $skill = Skill::create(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm kỹ năng thành công.',
            'data' => $skill
        ], 201);
    }

    public function updateSkill(Request $request, $id): JsonResponse
    {
        $skill = Skill::find($id);
        if (!$skill) return response()->json(['success' => false, 'message' => 'Kỹ năng không tồn tại.'], 404);

        $request->validate(['name' => 'required|string|max:100|unique:skills,name,' . $id]);
        $skill->update(['name' => $request->name]);

        return response()->json(['success' => true, 'message' => 'Cập nhật kỹ năng thành công.', 'data' => $skill]);
    }

    public function destroySkill($id): JsonResponse
    {
        $skill = Skill::find($id);
        if (!$skill) return response()->json(['success' => false, 'message' => 'Kỹ năng không tồn tại.'], 404);

        $skill->delete();
        return response()->json(['success' => true, 'message' => 'Xóa kỹ năng thành công.']);
    }
}