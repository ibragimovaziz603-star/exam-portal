<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\StudentResult;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function joinPage()
    {
        return view('student.join');
    }

    public function joinExam(Request $request)
    {
        $request->validate([
            'exam_code' => 'required|digits:8',
            'name' => 'required',
            'surname' => 'required',
        ]);

        $exam = Exam::where('exam_code', $request->exam_code)->first();

        if (!$exam) {
            return back()->withErrors(['exam_code' => 'Bunday kodli test topilmadi!'])->withInput();
        }
        if ($exam->is_started) {
            return back()->withErrors(['exam_code' => 'Bu test allaqachon boshlangan!'])->withInput();
        }

        $fullName = trim($request->name . ' ' . $request->surname);
        $userId = auth()->id();

        $existing = StudentResult::where('exam_id', $exam->id)
            ->where('student_name', $fullName)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->whereNull('finished_at')
            ->first();

        if ($existing) {
            if ($exam->is_started) {
                return redirect()->route('student.takeExam', $existing->token);
            }
            return redirect()->route('student.waiting', $existing->token);
        }

        $student = StudentResult::create([
            'exam_id' => $exam->id,
            'student_name' => $fullName,
            'score' => 0,
            'total_questions' => $exam->questions()->count(),
            'warnings' => 0,
            'user_id' => $userId,
        ]);

        return redirect()->route('student.waiting', $student->token);
    }

    public function waitingRoom($token)
    {
        $student = StudentResult::with('exam')->where('token', $token)->firstOrFail();

        if ($student->finished_at) {
            return redirect()->route('student.result', $token)
                ->with('info', 'Siz bu testni allaqachon tugatgansiz.');
        }
        if ($student->exam->is_started) {
            return redirect()->route('student.takeExam', $token);
        }

        return view('student.waiting', compact('student'));
    }

    public function takeExam($token)
    {
        $student = StudentResult::with('exam')->where('token', $token)->firstOrFail();

        if ($student->finished_at) {
            return redirect()->route('student.result', $token)
                ->with('error', 'Siz bu testni allaqachon tugatgansiz. Qayta kirish mumkin emas.');
        }

        $exam = $student->exam;
        if (!$exam->is_started) {
            return redirect()->route('student.waiting', $token);
        }

        $remaining = $exam->remainingSeconds();
        if ($remaining <= 0) {
            $this->finish($student, [], []);
            return redirect()->route('student.result', $token);
        }

        $questions = $student->assignedQuestions();
        $student->update(['total_questions' => $questions->count()]);

        return view('student.take_exam', compact('student', 'exam', 'remaining', 'questions'));
    }

    public function submitExam(Request $request, $token)
    {
        $student = StudentResult::with('exam')->where('token', $token)->firstOrFail();

        if (!$student->finished_at) {
            $this->finish($student, $request->input('answers', []), $request->input('codes', []));
        }

        return redirect()->route('student.result', $token)
            ->with('success', 'Test muvaffaqiyatli yakunlandi!');
    }

    public function result($token)
    {
        $result = StudentResult::with('exam', 'answers')->where('token', $token)->firstOrFail();

        if (!$result->finished_at) {
            return redirect()->route('student.takeExam', $token)
                ->with('error', 'Test hali yakunlanmagan. Iltimos, avval testni tugating.');
        }

        // YANGI: Ballni qayta hisoblash
        $result->recalcScore();

        $chosen = $result->answers->pluck('option_id', 'question_id');
        $back = route('home');

        return view('history_show', compact('result', 'chosen', 'back'));
    }

    public function addWarning($token)
    {
        $student = StudentResult::where('token', $token)->firstOrFail();

        if ($student->finished_at) {
            return response()->json(['warnings' => 3, 'finished' => true]);
        }

        if ($student->warnings < 3) {
            $student->increment('warnings');
        }

        return response()->json([
            'warnings' => min(3, $student->fresh()->warnings),
            'finished' => false,
        ]);
    }

    private function finish(StudentResult $student, array $answers, array $codes): void
    {
        // HIMOYA: Ikki marta hisoblashdan himoya (lockForUpdate bilan)
        $fresh = StudentResult::lockForUpdate()->find($student->id);
        if ($fresh && $fresh->finished_at) {
            return;
        }

        $score = 0;
        $qs = $student->assignedQuestions();

        foreach ($qs as $q) {
            if (
                StudentAnswer::where('student_result_id', $student->id)
                    ->where('question_id', $q->id)->exists()
            ) {
                continue;
            }

            if ($q->type === 'masala') {
                StudentAnswer::create([
                    'student_result_id' => $student->id,
                    'question_id' => $q->id,
                    'code_answer' => trim((string) ($codes[$q->id] ?? '')),
                    'ai_verdict' => null,
                    'grade' => null,
                ]);
                continue;
            }

            $chosenId = (int) ($answers[$q->id] ?? 0);
            $option = $chosenId ? $q->options->firstWhere('id', $chosenId) : null;
            if ($option && $option->is_correct) {
                $score++;
            }

            StudentAnswer::create([
                'student_result_id' => $student->id,
                'question_id' => $q->id,
                'option_id' => $option?->id,
            ]);
        }

        $student->update([
            'score' => $score,
            'total_questions' => $qs->count(),
            'finished_at' => now(),
        ]);
    }
}