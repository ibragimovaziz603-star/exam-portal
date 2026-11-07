<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Option;
use App\Models\StudentResult;
use Illuminate\Support\Facades\DB;
use App\Models\StudentAnswer;

class TeacherController extends Controller
{
    // Test faqat o'z egasiga ochiladi
    private function ownExam($code)
    {
        $exam = Exam::with('questions.options')->where('exam_code', $code)->firstOrFail();
        if ($exam->user_id && $exam->user_id != auth()->id())
            abort(403);
        return $exam;
    }

    public function dashboard()
    {
        $exams = Exam::with('studentResults', 'questions')
            ->where('user_id', auth()->id())
            ->latest()->get();
        return view('teacher.dashboard', compact('exams'));
    }

    public function createExam(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'type' => 'required|in:test,masala,mixed',
            'duration_minutes' => 'required|integer|min:1|max:120',
        ]);

        do {
            $code = random_int(10000000, 99999999);
        } while (Exam::where('exam_code', $code)->exists());

        $exam = Exam::create([
            'title' => $request->title,
            'type' => $request->type,
            'exam_code' => $code,
            'duration_minutes' => (int) $request->duration_minutes,
            'expires_at' => now()->addMinutes(15),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('teacher.manageExam', $exam->exam_code)->with('success', 'Test yaratildi!');
    }

    public function manageExam($code)
    {
        $exam = $this->ownExam($code);
        $exam->load('studentResults');
        return view('teacher.manage_exam', compact('exam'));
    }

    public function addQuestion(Request $request, $code)
    {
        $exam = $this->ownExam($code);
        $kind = $request->input('kind', 'test') === 'masala' ? 'masala' : 'test';

        $question = Question::create([
            'exam_id' => $exam->id,
            'question_text' => $request->question_text,
            'type' => $kind,
        ]);

        if ($kind === 'masala') {
            $question->reference_solution = $request->input('reference_solution');
            $question->save();
            return back()->with('success', 'Masala qo\'shildi!');
        }

        if ($request->has('options')) {
            foreach ($request->options as $index => $opt) {
                if ($opt !== null && $opt !== '') {
                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $opt,
                        'is_correct' => ($index == $request->correct_option),
                    ]);
                }
            }
        }

        return back()->with('success', 'Savol muvaffaqiyatli qo\'shildi!');
    }

    public function startExam($code)
    {
        $exam = $this->ownExam($code);
        $exam->update(['is_started' => true, 'started_at' => now()]);
        return back()->with('success', 'Test boshlandi!');
    }

    public function finishExam($code)
    {
        $exam = $this->ownExam($code);
        $exam->update(['started_at' => now()->subMinutes($exam->duration_minutes)]);
        return back()->with('success', 'Test yakunlandi!');
    }

    public function liveStatus($code)
    {
        $exam = $this->ownExam($code);
        $exam->load('studentResults.answers');
        $total = $exam->questions->count();
        $masalaIds = $exam->questions->where('type', 'masala')->pluck('id');

        return response()->json([
            'students' => $exam->studentResults->map(function ($s) use ($total, $masalaIds) {
                $pending = $s->answers->whereIn('question_id', $masalaIds)->whereNull('grade')->count();
                return [
                    'id' => $s->id,
                    'token' => $s->token,
                    'name' => $s->student_name,
                    'score' => $s->score_text,
                    'total' => $s->question_ids ? count($s->question_ids) : $total,
                    'warnings' => (int) $s->warnings,
                    'finished' => (bool) $s->finished_at,
                    'masala' => $masalaIds->count() > 0,
                    'pending' => $pending,
                ];
            })->values(),
        ]);
    }

    public function gradeList($code, $token)
    {
        $exam = $this->ownExam($code);
        $student = StudentResult::with('answers')->where('token', $token)->where('exam_id', $exam->id)->firstOrFail();

        $masalas = $student->assignedQuestions()->where('type', 'masala')->values();
        $ans = $student->answers->keyBy('question_id');

        return view('teacher.grade_list', compact('exam', 'student', 'masalas', 'ans'));
    }

    public function gradeEdit($code, $token, $qid)
    {
        $exam = $this->ownExam($code);
        $student = StudentResult::where('token', $token)->where('exam_id', $exam->id)->firstOrFail();
        $question = Question::where('exam_id', $exam->id)->where('id', $qid)->firstOrFail();
        $answer = StudentAnswer::where('student_result_id', $student->id)->where('question_id', $qid)->firstOrFail();

        return view('teacher.grade_edit', compact('exam', 'student', 'question', 'answer'));
    }

    public function gradeSet(Request $request, $answerId)
    {
        $request->validate(['grade' => 'required|integer|min:0|max:5']);

        $a = StudentAnswer::findOrFail($answerId);
        $s = StudentResult::with('exam')->findOrFail($a->student_result_id);
        if ($s->exam->user_id && $s->exam->user_id != auth()->id())
            abort(403);

        $g = (int) $request->grade;
        $a->update([
            'grade' => $g,
            'ai_verdict' => $g === 5 ? 'togri' : ($g === 0 ? 'xato' : 'qisman'),
        ]);

        $answers = StudentAnswer::where('student_result_id', $s->id)->get()->keyBy('question_id');
        $score = 0;
        foreach ($s->assignedQuestions() as $q) {
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
        $s->update(['score' => round($score, 2)]);

        return response()->json(['ok' => true, 'grade' => $g, 'score' => $score]);
    }

    public function setPick(Request $request, $code)
    {
        $exam = $this->ownExam($code);

        $request->validate([
            'pick_tests' => 'nullable|integer|min:1',
            'pick_masalas' => 'nullable|integer|min:1',
        ]);

        $t = $exam->questions->where('type', '!=', 'masala')->count();
        $m = $exam->questions->where('type', 'masala')->count();

        $pt = $request->pick_tests;
        $pm = $request->pick_masalas;

        $exam->update([
            'pick_tests' => ($pt === null || $pt >= $t) ? null : $pt,
            'pick_masalas' => ($pm === null || $pm >= $m) ? null : $pm,
        ]);

        return response()->json(['ok' => true]);
    }

    public function clearWarning($student_id)
    {
        $s = StudentResult::with('exam')->findOrFail($student_id);
        if ($s->exam->user_id && $s->exam->user_id != auth()->id())
            abort(403);
        $s->update(['warnings' => 0]);
        return response()->json(['status' => 'success']);
    }

    public function destroyExam($code)
    {
        $exam = $this->ownExam($code);

        DB::transaction(function () use ($exam) {
            $resultIds = StudentResult::where('exam_id', $exam->id)->pluck('id');
            StudentAnswer::whereIn('student_result_id', $resultIds)->delete();
            StudentResult::where('exam_id', $exam->id)->delete();

            $questionIds = Question::where('exam_id', $exam->id)->pluck('id');
            Option::whereIn('question_id', $questionIds)->delete();
            Question::where('exam_id', $exam->id)->delete();

            $exam->delete();
        });

        return redirect()->route('teacher.dashboard')->with('success', 'Test o\'chirildi!');
    }

    public function destroyQuestion($code, $id)
    {
        $exam = $this->ownExam($code);
        $q = Question::where('exam_id', $exam->id)->where('id', $id)->firstOrFail();

        DB::transaction(function () use ($q) {
            StudentAnswer::where('question_id', $q->id)->delete();
            Option::where('question_id', $q->id)->delete();
            $q->delete();
        });

        return back()->with('success', 'Savol o\'chirildi!');
    }

    public function kickStudent($student_id)
    {
        $s = StudentResult::with('exam')->findOrFail($student_id);
        if ($s->exam->user_id && $s->exam->user_id != auth()->id())
            abort(403);

        DB::transaction(function () use ($s) {
            StudentAnswer::where('student_result_id', $s->id)->delete();
            $s->delete();
        });

        return response()->json(['ok' => true]);
    }

    public function changeType(Request $request, $code)
    {
        $exam = $this->ownExam($code);
        $request->validate(['type' => 'required|in:test,masala,mixed']);

        if ($exam->is_started) {
            return back()->with('success', 'Test boshlangan, turini o\'zgartirib bo\'lmaydi.');
        }

        $type = $request->type;

        DB::transaction(function () use ($exam, $type) {
            $ids = collect();
            if ($type === 'test') {
                $ids = $exam->questions()->where('type', 'masala')->pluck('id');
            } elseif ($type === 'masala') {
                $ids = $exam->questions()->where('type', '!=', 'masala')->pluck('id');
            }
            if ($ids->isNotEmpty()) {
                StudentAnswer::whereIn('question_id', $ids)->delete();
                Option::whereIn('question_id', $ids)->delete();
                Question::whereIn('id', $ids)->delete();
            }
            $exam->update(['type' => $type, 'pick_tests' => null, 'pick_masalas' => null]);
        });

        return back()->with('success', 'Test turi o\'zgartirildi!');
    }

    public function importQuestions(Request $request, $code)
    {
        $exam = $this->ownExam($code);

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:test,masala',
            'items.*.text' => 'required|string',
            'items.*.options' => 'nullable|array',
            'items.*.options.*' => 'nullable|string',
            'items.*.correct' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($data, $exam) {
            foreach ($data['items'] as $it) {
                $q = Question::create([
                    'exam_id' => $exam->id,
                    'question_text' => $it['text'],
                    'type' => $it['type'],
                ]);

                if ($it['type'] === 'test') {
                    foreach (($it['options'] ?? []) as $k => $opt) {
                        if ($opt === null || $opt === '')
                            continue;
                        Option::create([
                            'question_id' => $q->id,
                            'option_text' => $opt,
                            'is_correct' => ((int) ($it['correct'] ?? -1) === $k),
                        ]);
                    }
                }
            }

            $types = $exam->questions->pluck('type')->merge(collect($data['items'])->pluck('type'));
            $hasT = $types->contains('test');
            $hasM = $types->contains('masala');
            $exam->update(['type' => ($hasT && $hasM) ? 'mixed' : ($hasM ? 'masala' : 'test')]);
        });

        return response()->json(['ok' => true]);
    }
}