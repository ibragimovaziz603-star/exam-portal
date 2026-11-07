<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'title', 'type', 'exam_code', 'duration_minutes',
        'expires_at', 'is_started', 'started_at', 'pdf_file',
        'user_id',
        'pick_tests',
        'pick_masalas', // <-- Shu yerga qo'shildi
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'started_at' => 'datetime',
        'is_started' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    public function questions()      { return $this->hasMany(Question::class); }
    public function studentResults() { return $this->hasMany(StudentResult::class); }

    public function remainingSeconds(): int
    {
        if (!$this->started_at) return $this->duration_minutes * 60;
        $end = $this->started_at->copy()->addMinutes($this->duration_minutes);
        return max(0, $end->timestamp - now()->timestamp);
    }
}