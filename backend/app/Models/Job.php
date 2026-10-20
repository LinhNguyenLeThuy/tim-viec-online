<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $table = 'jobs';

    protected $fillable = [
        'company_id',
        'category_id',
        'title',
        'job_level',
        'quantity',
        'description',
        'requirements',
        'benefits',
        'salary_min',
        'salary_max',
        'is_negotiable',
        'job_type',
        'city',
        'address',
        'deadline',
        'status',
        'is_reported',
        'rejection_reason',
        'views_count',
    ];

    // Quan hệ với Nhà tuyển dụng (Company)
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Quan hệ với Danh mục ngành nghề (Category)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Quan hệ nhiều-nhiều với Kỹ năng (Skills qua bảng job_skills)
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_skills', 'job_id', 'skill_id');
    }
}