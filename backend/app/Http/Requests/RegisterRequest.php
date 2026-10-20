<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:32',
                'regex:/[a-z]/',      
                'regex:/[A-Z]/',      
                'regex:/[0-9]/',      
                'regex:/[@$!%*?&]/',  
            ],
            'phone' => ['nullable', 'regex:/^(03|05|07|08|09)[0-9]{8}$/'],
            'role' => ['required', 'in:candidate,employer'],
                // Kiểm tra điều kiện độ tuổi cho Candidate (phải từ 15 tuổi trở lên)
            'dob' => $this->role === 'candidate' 
                    ? [
                        'required', 
                        'date', 
                        function ($attribute, $value, $fail) {
                            $age = \Carbon\Carbon::parse($value)->age;
                            if ($age < 15) {
                                $fail('Ứng viên bắt buộc phải từ 15 tuổi trở lên theo quy định.');
                            }
                        }
                    ] 
                    : ['nullable', 'date'],
            ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Họ tên là thông tin bắt buộc.',
            'name.string' => 'Họ tên phải là chuỗi ký tự.',
            'name.min' => 'Họ tên phải có ít nhất :min ký tự.',
            'name.max' => 'Họ tên không được vượt quá :max ký tự.',
            'email.required' => 'Địa chỉ email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng chuẩn.',
            'email.max' => 'Email không được vượt quá :max ký tự.',
            'email.unique' => 'Email đã được sử dụng.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá :max ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất một chữ cái thường, một chữ cái in hoa, một chữ số và một ký tự đặc biệt.',
            'phone.regex' => 'Số điện thoại không hợp lệ (phải có 10 chữ số và bắt đầu bằng 03, 05, 07, 08, 09).',
            'role.required' => 'Vui lòng chọn vai trò tài khoản.',
            'role.in' => 'Vai trò không hợp lệ (chỉ nhận candidate hoặc employer).',
            'dob.required' => 'Ngày sinh là bắt buộc đối với tài khoản ứng viên.',
            'dob.date' => 'Ngày sinh không đúng định dạng ngày tháng.',
        ];
    }
}