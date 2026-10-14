<?php

namespace App\Http\Controllers;

use App\Models\FinalTest;
use App\Models\Student;
use App\Models\StudentChapterCompletion;
use App\Models\StudentUnitProgress;
use App\Services\StreakService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseProgressController extends Controller
{
    public function __construct(
        protected StreakService $streakService
    ) {}

    public function completeUnit(Request $request, string $unit): RedirectResponse
    {
        $student = $this->studentFor($request);

        StudentUnitProgress::updateOrCreate(
            ['student_id' => $student->id, 'unit_id' => $unit],
            ['is_completed' => true, 'completed_at' => now()]
        );

        $streakResult = $this->streakService->recordActivity($student);
        if ($streakResult['streak_increased']) {
            session()->flash('streak_award', $streakResult);
        }

        return back();
    }

    public function completeChapter(Request $request, string $chapter): RedirectResponse
    {
        $student = $this->studentFor($request);
        $request->validate(['score' => ['required', 'integer', 'min:0', 'max:100']]);
        $score = $request->integer('score');
        $finalTest = FinalTest::where('chapter_id', $chapter)->firstOrFail();
        $isPassed = $score >= $finalTest->minimum_score;

        StudentChapterCompletion::updateOrCreate(
            ['student_id' => $student->id, 'chapter_id' => $chapter],
            [
                'score' => $score,
                'is_passed' => $isPassed,
                'completed_at' => now(),
            ]
        );

        if ($isPassed) {
            $streakResult = $this->streakService->recordActivity($student);
            if ($streakResult['streak_increased']) {
                session()->flash('streak_award', $streakResult);
            }
        }

        return back();
    }

    private function studentFor(Request $request): Student
    {
        abort_unless($request->user()->role === 'child', 403);

        return Student::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => $request->user()->name]
        );
    }
}
