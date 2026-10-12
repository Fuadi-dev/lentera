<?php

namespace App\Http\Controllers;

use App\Models\FinalTest;
use App\Models\Student;
use App\Models\StudentChapterCompletion;
use App\Models\StudentUnitProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseProgressController extends Controller
{
    public function completeUnit(Request $request, string $unit): RedirectResponse
    {
        $student = $this->studentFor($request);

        StudentUnitProgress::updateOrCreate(
            ['student_id' => $student->id, 'unit_id' => $unit],
            ['is_completed' => true, 'completed_at' => now()]
        );

        return back();
    }

    public function completeChapter(Request $request, string $chapter): RedirectResponse
    {
        $student = $this->studentFor($request);
        $request->validate(['score' => ['required', 'integer', 'min:0', 'max:100']]);
        $score = $request->integer('score');
        $finalTest = FinalTest::where('chapter_id', $chapter)->firstOrFail();

        StudentChapterCompletion::updateOrCreate(
            ['student_id' => $student->id, 'chapter_id' => $chapter],
            [
                'score' => $score,
                'is_passed' => $score >= $finalTest->minimum_score,
                'completed_at' => now(),
            ]
        );

        return back();
    }

    private function studentFor(Request $request): Student
    {
        abort_unless($request->user()->role === 'child', 403);

        return Student::where('user_id', $request->user()->id)->firstOrFail();
    }
}
