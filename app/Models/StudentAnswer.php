<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $fillable = [
        'student_result_id', 'question_id', 'option_id',
        'code_answer', 'ai_verdict', 'ai_feedback',
        'grade'
    ];
}