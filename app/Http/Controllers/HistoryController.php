<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\StudentResult;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    private function myExam($code)
    {
        return Exam::with('studentResults')
            ->where('exam_code', $code)
            ->where(fn($q) => $q->where('user_id', auth()->id())->orWhereNull('user_id'))
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'joined');

        $joined = StudentResult::with('exam')
            ->where('user_id', auth()->id())
            ->whereNotNull('finished_at')
            ->latest('finished_at')
            ->get();

        $created = collect();
        if (auth()->user()->role == 'teacher') {
            $created = Exam::with('studentResults')->withCount('questions')
                ->where(fn($q) => $q->where('user_id', auth()->id())->orWhereNull('user_id'))
                ->latest()->get();
        }

        return view('history', compact('tab', 'joined', 'created'));
    }

    public function show($token)
    {
        $result = StudentResult::with('exam.questions.options', 'answers')
            ->where('token', $token)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // YANGI: Ballni qayta hisoblash
        if ($result->finished_at) {
            $result->recalcScore();
        }

        $chosen = $result->answers->pluck('option_id', 'question_id');
        $back = route('history');

        return view('history_show', compact('result', 'chosen', 'back'));
    }

    public function examShow($code)
    {
        $exam = $this->myExam($code);
        return view('history_exam', compact('exam'));
    }

    public function participantShow($code, $token)
    {
        $exam = $this->myExam($code);
        $result = StudentResult::with('exam.questions.options', 'answers')
            ->where('token', $token)->where('exam_id', $exam->id)->firstOrFail();

        // YANGI: Ballni qayta hisoblash
        if ($result->finished_at) {
            $result->recalcScore();
        }

        $chosen = $result->answers->pluck('option_id', 'question_id');
        $back = route('history.exam', $code);

        return view('history_show', compact('result', 'chosen', 'back'));
    }
}