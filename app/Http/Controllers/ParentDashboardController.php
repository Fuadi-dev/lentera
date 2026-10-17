<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParentDashboardController extends Controller
{
    /**
     * Display the parent dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user->role === 'parent', 403);

        $parent = Parents::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
            ]
        );

        $students = $parent->students()
            ->with([
                'unitProgress' => fn ($query) => $query->where('is_completed', true)->with('unit.chapter.level.classes.subject'),
                'streak',
            ])
            ->get();

        $totalUnitsCount = Unit::count();
        $subjects = Subject::with('classes.levels.chapters.units')->get();

        $formattedChildren = $students->map(function (Student $child) use ($totalUnitsCount, $subjects) {
            $completedUnits = $child->unitProgress;
            $completedCount = $completedUnits->count();

            $words = array_values(array_filter(explode(' ', trim($child->name))));
            $avatar = strtoupper(substr($words[0] ?? 'A', 0, 1).(isset($words[1]) ? substr($words[1], 0, 1) : ''));

            $lastCompleted = $completedUnits->sortByDesc('completed_at')->first();
            $lastActivityDetails = null;

            if ($lastCompleted && $lastCompleted->unit) {
                $unit = $lastCompleted->unit;
                $chapter = $unit->chapter;
                $level = $chapter?->level;
                $classes = $level?->classes;
                $subject = $classes?->subject;

                $lastActivityDetails = [
                    'subject' => $subject?->title,
                    'chapterNumber' => $chapter?->number,
                    'chapterTitle' => $chapter?->title,
                    'levelNumber' => $level?->number,
                    'levelTitle' => $level?->title,
                    'unitOrder' => $unit->order,
                    'unitTitle' => $unit->title,
                    'timeAgo' => $lastCompleted->completed_at
                        ? $lastCompleted->completed_at->locale('id')->diffForHumans()
                        : 'Baru saja',
                    'completedAt' => $lastCompleted->completed_at
                        ? $lastCompleted->completed_at->translatedFormat('d M Y, H:i')
                        : null,
                ];
            }

            $lastActivity = $lastActivityDetails
                ? ($lastActivityDetails['subject'] ? $lastActivityDetails['subject'].' • ' : '')
                    .($lastActivityDetails['chapterNumber'] ? 'Chapter '.$lastActivityDetails['chapterNumber'].' ' : '')
                    .'(Unit '.$lastActivityDetails['unitOrder'].'): '.$lastActivityDetails['unitTitle']
                : 'Belum ada aktivitas belajar';

            $classTitle = $lastCompleted?->unit?->chapter?->level?->classes?->title ?? 'Siswa Aktif';

            $subjectsProgress = $subjects->map(function ($subject) use ($completedUnits) {
                $totalUnitsInSubject = $subject->classes->flatMap->levels->flatMap->chapters->flatMap->units->count();
                $completedInSubject = $completedUnits->filter(function ($progress) use ($subject) {
                    return $progress->unit?->chapter?->level?->classes?->subject_id === $subject->id;
                })->count();

                $progressPercentage = $totalUnitsInSubject > 0
                    ? (int) round(($completedInSubject / $totalUnitsInSubject) * 100)
                    : 0;

                return [
                    'name' => $subject->title,
                    'progress' => $progressPercentage,
                    'color' => 'bg-primary',
                ];
            })->values()->all();

            $overallProgress = $totalUnitsCount > 0
                ? (int) round(($completedCount / $totalUnitsCount) * 100)
                : 0;

            return [
                'id' => $child->id,
                'name' => $child->name,
                'class' => $classTitle,
                'avatar' => $avatar,
                'pairingCode' => $child->pairing_code,
                'overallProgress' => $overallProgress,
                'completedLessons' => $completedCount,
                'totalLessons' => $totalUnitsCount,
                'streak' => $child->streak?->active_streak ?? 0,
                'streakActiveToday' => $child->streak?->is_active_today ?? false,
                'lastActivity' => $lastActivity,
                'lastActivityDetails' => $lastActivityDetails,
                'subjects' => $subjectsProgress,
            ];
        })->values()->all();

        return Inertia::render('ParentDashboard', [
            'children' => $formattedChildren,
        ]);
    }

    /**
     * Pair a child using unique pairing code.
     */
    public function pairChild(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'parent', 403);

        $request->validate([
            'pairing_code' => ['required', 'string', 'max:20'],
        ], [
            'pairing_code.required' => 'Masukkan kode unik anak terlebih dahulu.',
        ]);

        $code = strtoupper(trim($request->input('pairing_code')));

        $student = Student::where('pairing_code', $code)->first();

        if (! $student) {
            return back()->withErrors([
                'pairing_code' => 'Kode unik tidak ditemukan. Pastikan kode yang dimasukkan sudah benar.',
            ]);
        }

        $parent = Parents::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
            ]
        );

        if ($student->parent_id === $parent->id) {
            return back()->withErrors([
                'pairing_code' => "{$student->name} sudah terhubung ke akun Anda.",
            ]);
        }

        $student->update(['parent_id' => $parent->id]);

        return back()->with('success', "Berhasil menghubungkan {$student->name} ke akun Anda.");
    }

    /**
     * Unpair a child from parent account.
     */
    public function unpairChild(Request $request, Student $student): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'parent', 403);

        $parent = Parents::where('user_id', $user->id)->first();

        if ($parent && $student->parent_id === $parent->id) {
            $student->update(['parent_id' => null]);

            return back()->with('success', "Berhasil memutuskan hubungan akun dengan {$student->name}.");
        }

        return back();
    }
}
