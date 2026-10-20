<?php 

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\CandidateProfile;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = DB::transaction(function () use ($request) {
                // 1. Tạo user mới
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    'dob' => $request->dob, 
                    'role' => $request->role,
                    'status' => 'active',
                    'failed_attempts' => 0
                ]);

                // 2. Tự động tạo hồ sơ tương ứng
                if ($request->role === 'candidate') {
                    CandidateProfile::create([
                        'user_id' => $user->id
                    ]);
                } else {
                    Company::create([
                        'user_id' => $user->id,
                        'name' => $request->name . ' Company',
                    ]);
                }

                return $user;
            });

            $user->makeHidden(['password', 'remember_token']);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tài khoản thành công!',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng ký thất bại, vui lòng thử lại sau!',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', strtolower($request->email))->first();

        // FR-02.2: Kiểm tra user có tồn tại
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác',
            ], 401);
        }

        // FR-02.5: Kiểm tra tài khoản bị Admin khóa
        if ($user->status === 'locked') {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa bởi quản trị viên',
            ], 403);
        }

        // Kiểm tra nếu thời gian khóa tạm thời đã hết thì đặt lại bộ đếm
        if ($user->locked_until && $user->locked_until->isPast()) {
            $user->update([
                'failed_attempts' => 0,
                'locked_until' => null,
            ]);
        }

        // FR-02.4: Kiểm tra tài khoản bị tạm khóa do nhập sai nhiều lần
        if ($user->locked_until && $user->locked_until->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản bị tạm khóa do nhập sai nhiều lần, vui lòng khôi phục tài khoản',
            ], 403);
        }

        // Kiểm tra mật khẩu
        if (!Hash::check($request->password, $user->password)) {
            $user->increment('failed_attempts');

            // FR-02.3 & FR-02.4: Sau 5 lần đăng nhập sai liên tiếp, tạm khóa 15 phút
            if ($user->failed_attempts >= 5) {
                $user->update([
                    'locked_until' => now()->addMinutes(15),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản bị tạm khóa do nhập sai nhiều lần, vui lòng khôi phục tài khoản',
                ], 403);
            }

            // FR-02.2: Hiển thị thông báo nếu email hoặc mật khẩu không chính xác
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác',
            ], 401);
        }

        // Đăng nhập đúng: đặt lại bộ đếm số lần sai về 0 và xóa khóa tạm thời
        $user->update([
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);

        if ($user->isCandidate()) {
            $user->load(['candidateProfile.skills']);
        } elseif ($user->isEmployer()) {
            $user->load('company');
        }

        // FR-02.6: Cấp phát token & tự động điều hướng theo vai trò
        $token = $user->createToken('auth_token')->plainTextToken;

        $redirectUrl = match ($user->role) {
            'candidate' => '/jobs',
            'employer' => '/employer/my-jobs',
            'admin' => '/admin/dashboard',
            default => '/',
        };

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'redirect_url' => $redirectUrl,
            ],
        ], 200);
    }

}