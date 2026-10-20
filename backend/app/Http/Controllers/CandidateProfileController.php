<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateCandidateProfileRequest;
use App\Http\Requests\UploadCvRequest;
use App\Models\CandidateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateProfileController extends Controller
{
    //  Lấy thông tin chi tiết hồ sơ cá nhân
    public function show(Request $request)
    {
        $user = $request->user();
        
        $profile = CandidateProfile::with(['skills', 'user'])
            ->where('user_id', $user->id)
            ->first();
        if (!$profile) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy thông tin hồ sơ ứng viên.'
        ], 404);
    }
        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin hồ sơ thành công',
            'data' => $profile
        ]);
    }

    // Cập nhật thông tin cá nhân và kỹ năng
    public function update(UpdateCandidateProfileRequest $request)
    {
        $user =$request->user();
        
        $profile = CandidateProfile::where('user_id',$user->id)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin hồ sơ ứng viên để cập nhật.'
            ], 404);
        }

        try {
            DB::transaction(function () use ($user, $profile,$request) {
                // 1. Cập nhật thông tin cá nhân cơ bản vào bảng `users`
                $user->update($request->only([
                    'name',
                    'phone',
                    'email',
                    'dob'
                ]));

                // 2. Cập nhật thông tin chi tiết vào bảng `candidate_profiles`
                $profile->update($request->only([
                    'title',
                    'experience_years',
                    'bio',
                    'address',
                    'city',
                    'social_links'
                ]));

                // 3. Đồng bộ danh sách kỹ năng qua bảng trung gian `candidate_skills`
                if ($request->has('skill_ids')) {
                    $profile->skills()->sync($request->skill_ids);
                }
            });

            // Tải lại dữ liệu mới nhất (gồm cả thông tin user đã đổi tên/sđt và các skills mới)
            $profile->load(['skills', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin cá nhân và hồ sơ thành công!',
                'data' => $profile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thất bại, vui lòng thử lại sau.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadCv(UploadCvRequest $request)
    {
        $user = $request->user();
        $profile = CandidateProfile::where('user_id', $user->id)->firstOrFail();
        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');
            // Xóa file CV cũ nếu có
            if ($profile->cv_file_path && Storage::disk('public')->exists($profile->cv_file_path)) {
                Storage::disk('public')->delete($profile->cv_file_path);
            }
            // Lưu file vào storage/app/public/resumes
            $path = $file->store('resumes', 'public');
            // Cập nhật cv_file_path trong DB
            $profile->cv_file_path = $path;
            $profile->save();
            $cvUrl = asset('storage/' . $path);
            return response()->json([
                'success' => true,
                'message' => 'Tải lên CV thành công!',
                'data' => [
                    'cv_file_path' => $path,
                    'cv_url' => $cvUrl
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy file tải lên.'
        ], 400);
    }
}