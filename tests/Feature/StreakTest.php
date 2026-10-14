<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Classes;
use App\Models\FinalTest;
use App\Models\Level;
use App\Models\Student;
use App\Models\StudentStreak;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreakTest extends TestCase
{
    use RefreshDatabase;

    private function createChildUser(): array
    {
        $user = User::factory()->create([
            'role' => 'child',
            'email_verified_at' => now(),
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'name' => $user->name,
        ]);

        return [$user, $student];
    }

    private function createUnit(): Unit
    {
        $subject = Subject::create(['title' => 'Matematika']);
        $class = Classes::create(['subject_id' => $subject->id, 'number' => 1, 'title' => 'Kelas 1']);
        $level = Level::create(['id' => 'lvl-1', 'class_id' => $class->id, 'number' => 1, 'title' => 'Level 1']);
        $chapter = Chapter::create(['id' => 'ch-1', 'level_id' => $level->id, 'number' => 1, 'title' => 'Bab 1']);

        return Unit::create([
            'id' => 1001,
            'chapter_id' => $chapter->id,
            'order' => 1,
            'type' => 'text',
            'title' => 'Mengenal Angka',
            'content' => 'Isi materi angka',
        ]);
    }

    public function test_marking_unit_as_completed_starts_streak_at_1(): void
    {
        [$user, $student] = $this->createChildUser();
        $unit = $this->createUnit();

        $response = $this->actingAs($user)
            ->post(route('progress.units.complete', ['unit' => $unit->id]));

        $response->assertRedirect();
        $response->assertSessionHas('streak_award');

        $streak = StudentStreak::where('student_id', $student->id)->first();
        $this->assertNotNull($streak);
        $this->assertEquals(1, $streak->current_streak);
        $this->assertEquals(1, $streak->longest_streak);
        $this->assertEquals(now()->toDateString(), $streak->last_activity_date->toDateString());
    }

    public function test_completing_another_unit_on_same_day_does_not_increment_streak_twice(): void
    {
        [$user, $student] = $this->createChildUser();
        $unit = $this->createUnit();

        // Complete unit 1
        $this->actingAs($user)->post(route('progress.units.complete', ['unit' => $unit->id]));

        // Create unit 2 in same chapter
        $unit2 = Unit::create([
            'id' => 1002,
            'chapter_id' => $unit->chapter_id,
            'order' => 2,
            'type' => 'text',
            'title' => 'Materi 2',
        ]);

        // Complete unit 2 on same day
        $response = $this->actingAs($user)->post(route('progress.units.complete', ['unit' => $unit2->id]));
        $response->assertSessionMissing('streak_award');

        $streak = StudentStreak::where('student_id', $student->id)->first();
        $this->assertEquals(1, $streak->current_streak);
    }

    public function test_completing_unit_on_consecutive_day_increments_streak(): void
    {
        [$user, $student] = $this->createChildUser();
        $unit = $this->createUnit();

        // Set streak as yesterday
        StudentStreak::create([
            'student_id' => $student->id,
            'current_streak' => 1,
            'longest_streak' => 1,
            'last_activity_date' => now()->subDay()->startOfDay(),
        ]);

        $response = $this->actingAs($user)->post(route('progress.units.complete', ['unit' => $unit->id]));
        $response->assertSessionHas('streak_award');

        $streak = StudentStreak::where('student_id', $student->id)->first();
        $this->assertEquals(2, $streak->current_streak);
        $this->assertEquals(2, $streak->longest_streak);
        $this->assertEquals(now()->toDateString(), $streak->last_activity_date->toDateString());
    }

    public function test_completing_unit_after_missing_day_resets_streak_to_1(): void
    {
        [$user, $student] = $this->createChildUser();
        $unit = $this->createUnit();

        // Last activity was 3 days ago
        StudentStreak::create([
            'student_id' => $student->id,
            'current_streak' => 5,
            'longest_streak' => 5,
            'last_activity_date' => now()->subDays(3)->startOfDay(),
        ]);

        $response = $this->actingAs($user)->post(route('progress.units.complete', ['unit' => $unit->id]));
        $response->assertSessionHas('streak_award');

        $streak = StudentStreak::where('student_id', $student->id)->first();
        $this->assertEquals(1, $streak->current_streak);
        $this->assertEquals(5, $streak->longest_streak);
        $this->assertEquals(now()->toDateString(), $streak->last_activity_date->toDateString());
    }

    public function test_active_streak_accessor_returns_zero_if_day_missed(): void
    {
        [$user, $student] = $this->createChildUser();

        // Active streak alive if yesterday
        $streak = StudentStreak::create([
            'student_id' => $student->id,
            'current_streak' => 3,
            'longest_streak' => 3,
            'last_activity_date' => now()->subDay()->startOfDay(),
        ]);

        $this->assertEquals(3, $streak->active_streak);
        $this->assertFalse($streak->is_active_today);

        // Active streak broken if 2 days ago
        $streak->update(['last_activity_date' => now()->subDays(2)->startOfDay()]);
        $streak->refresh();

        $this->assertEquals(0, $streak->active_streak);
        $this->assertFalse($streak->is_active_today);

        // Active today if activity was today
        $streak->update(['last_activity_date' => now()->startOfDay()]);
        $streak->refresh();

        $this->assertTrue($streak->is_active_today);
    }

    public function test_completing_final_test_chapter_also_updates_streak(): void
    {
        [$user, $student] = $this->createChildUser();
        $unit = $this->createUnit();

        $finalTest = FinalTest::create([
            'chapter_id' => $unit->chapter_id,
            'title' => 'Ujian Akhir',
            'minimum_score' => 70,
            'question_count' => 10,
        ]);

        $response = $this->actingAs($user)->post(
            route('progress.chapters.complete', ['chapter' => $unit->chapter_id]),
            ['score' => 80]
        );

        $response->assertRedirect();
        $response->assertSessionHas('streak_award');

        $streak = StudentStreak::where('student_id', $student->id)->first();
        $this->assertNotNull($streak);
        $this->assertEquals(1, $streak->current_streak);
    }

    public function test_recording_streak_activity_does_not_mark_unit_as_completed(): void
    {
        [$user, $student] = $this->createChildUser();
        $unit = $this->createUnit();

        $response = $this->actingAs($user)->post(route('progress.streak.record'));

        $response->assertRedirect();
        $response->assertSessionHas('streak_award');

        $this->assertEquals(1, StudentStreak::where('student_id', $student->id)->first()->current_streak);
        $this->assertDatabaseMissing('student_unit_progress', [
            'student_id' => $student->id,
            'unit_id' => $unit->id,
        ]);
    }

    public function test_recording_streak_activity_twice_on_same_day_only_counts_once(): void
    {
        [$user, $student] = $this->createChildUser();

        $this->actingAs($user)->post(route('progress.streak.record'));
        $response = $this->actingAs($user)->post(route('progress.streak.record'));

        $response->assertSessionMissing('streak_award');
        $this->assertEquals(1, StudentStreak::where('student_id', $student->id)->first()->current_streak);
    }

    public function test_recording_streak_activity_is_forbidden_for_parents(): void
    {
        $parent = User::factory()->create(['role' => 'parent', 'email_verified_at' => now()]);

        $this->actingAs($parent)->post(route('progress.streak.record'))->assertForbidden();
    }

    public function test_streak_award_is_passed_via_inertia_flash_props(): void
    {
        [$user, $student] = $this->createChildUser();
        $unit = $this->createUnit();

        session()->flash('streak_award', [
            'streak_increased' => true,
            'current_streak' => 1,
            'longest_streak' => 1,
            'is_new_streak' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('flash.streak_award')
            ->where('flash.streak_award.streak_increased', true)
            ->where('flash.streak_award.current_streak', 1)
        );
    }
}
