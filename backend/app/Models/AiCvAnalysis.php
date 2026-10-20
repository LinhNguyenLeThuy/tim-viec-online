<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiCvAnalysis extends Model
{
    use HasFactory;

    protected $table = 'ai_cv_analyses';

    // Bảng này chỉ cần created_at, không cần updated_at theo thiết kế DB
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'application_id',
        'match_score',
        'summary',
        'matched_skills',
        'missing_skills',
        'strengths',
        'recommendations',
    ];

    protected $casts = [
        'matched_skills' => 'array',
        'missing_skills' => 'array',
        'strengths' => 'array',
        'recommendations' => 'array',
    ];

    // Quan hệ với bảng applications
    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}