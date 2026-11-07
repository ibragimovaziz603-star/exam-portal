<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    // Ruxsat berilgan ustunlar (exam_id shu yerda bo'lishi SHART!)
    protected $fillable = [
        'exam_id', 
        'question_text', 
        'type'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }
}