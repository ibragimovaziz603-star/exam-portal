<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentResult extends Model
{
    protected $fillable = [
        'exam_id',
        'student_name',
        'score',
        'total_questions',
        'warnings',
        'token',
        'finished_at',
        'user_id',
        'question_ids',
    ];

    protected $casts = [
        'finished_at' => 'datetime',
        'question_ids' => 'array',
        'score' => 'float',
    ];

    private $aqCache = null;

    protected static function booted()
    {
        static::creating(function ($s) {
            if (empty($s->token)) {
                do {
                    $token = (string) random_int(10000000, 99999999);
                } while (static::where('token', $token)->exists());
                $s->token = $token;
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    // Shu o'quvchiga tushgan savollar: tasodifiy tanlanadi, testlar tepada, masalalar pastda
    public function assignedQuestions()
    {
        if ($this->aqCache !== null)
            return $this->aqCache;

        $exam = $this->exam;
        $all = $exam->questions()->with('options')->get();

        if ($this->question_ids === null) {
            $t = $all->where('type', '!=', 'masala')->shuffle();
            $m = $all->where('type', 'masala')->shuffle();
            if (!$this->finished_at) {
                if ($exam->pick_tests !== null)
                    $t = $t->take($exam->pick_tests);
                if ($exam->pick_masalas !== null)
                    $m = $m->take($exam->pick_masalas);
            }
            $this->question_ids = $t->pluck('id')->merge($m->pluck('id'))->values()->all();
            $this->save();
        }

        $byId = $all->keyBy('id');
        return $this->aqCache = collect($this->question_ids)->map(fn($id) => $byId->get($id))->filter()->values();
    }

    public function recalcScore(): void
    {
        $answers = $this->answers()->get()->keyBy('question_id');
        $score = 0;
        foreach ($this->assignedQuestions() as $q) {
            $x = $answers->get($q->id);
            if (!$x)
                continue;
            if ($q->type === 'masala') {
                $score += ((int) $x->grade) / 5;
            } else {
                $o = $q->options->firstWhere('id', $x->option_id);
                if ($o && $o->is_correct)
                    $score++;
            }
        }
        if (abs($score - (float) $this->score) > 0.001) {
            $this->update(['score' => round($score, 2)]);
        }
    }

    public function getScoreTextAttribute()
    {
        $s = rtrim(rtrim(number_format((float) $this->score, 2, '.', ''), '0'), '.');
        return $s === '' ? '0' : $s;
    }
}