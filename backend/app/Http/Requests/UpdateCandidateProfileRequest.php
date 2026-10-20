<?php
namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateCandidateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $user = $this->user();
        return [
            'name' => 'sometimes|required|string|min:2|max:100',
            'phone' => [
                'nullable',
                'regex:/^(03|05|07|08|09)[0-9]{8}$/'
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:rfc',
                'max:100',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
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
            'title' => 'nullable|string|max:100',
            'experience_years' => [
                'required',
                'integer',
                'min:0',
                'max:50',
                function ($attribute, $value,$fail) {
                    $dob = $this->input('dob',$this->user()->dob);
                    if ($dob) {
                        $age = Carbon::parse($dob)->age;
                        if ($value > $age) {$fail("Số năm kinh nghiệm ({$value} năm) không hợp lý với độ tuổi hiện tại (Ứng viên mới {$age} tuổi).");
                        }
                    }
                },
            ],
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'social_links' => 'nullable|array', 
            'skill_ids' => 'nullable|array|max:10',    
            'skill_ids.*' => 'exists:skills,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Họ tên không được để trống.',
            'name.min' => 'Họ tên phải có ít nhất 2 ký tự.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Địa chỉ email không đúng định dạng chuẩn.',
            'email.unique' => 'Email này đã được sử dụng bởi tài khoản khác.',
            'phone.regex' => 'Số điện thoại không hợp lệ (phải gồm 10 số và đúng đầu số Việt Nam).',
            'experience_years.required' => 'Vui lòng nhập số năm kinh nghiệm.',
            'skill_ids.max' => 'Bạn chỉ được chọn tối đa 10 kỹ năng chuyên môn.',
            'skill_ids.*.exists' => 'Kỹ năng được chọn không tồn tại trong hệ thống.',
            'dob.required' => 'Ngày sinh là bắt buộc đối với tài khoản ứng viên.',
            'dob.date' => 'Ngày sinh không đúng định dạng ngày tháng.',
        ];
    }
}