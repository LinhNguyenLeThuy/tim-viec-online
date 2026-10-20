<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'logo',
        'website',
        'company_size',
        'address',
        'city',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}