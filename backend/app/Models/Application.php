<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';

    protected $fillable = [
        'job_id',
        'candidate_id',
        'cv_path',
        'cover_letter',
        'status',
        'reviewed_at',
    ];

    // Quan hệ với tin tuyển dụng (Job)
    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    // Quan hệ với hồ sơ ứng viên (CandidateProfile)
    public function candidateProfile()
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_id');
    }

    // Quan hệ một-một với kết quả AI phân tích CV (AiCvAnalysis)
    public function aiAnalysis()
    {
        return $this->hasOne(AiCvAnalysis::class, 'application_id');
    }
}