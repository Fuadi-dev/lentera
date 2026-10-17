<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Classes;
use App\Models\Level;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourseLevelUnlockTest extends TestCase
{
    use RefreshDatabase;

    private User $childUser;

    private Student $student;

    private Subject $subject;

    private Classes $class;

    private Level $level1;

    private Level $level2;

    private Level $level3;

    private Unit $unit1;

    private Unit $unit2;

    private Unit $unit3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->childUser = User::factory()->create([
            'role' => 'child',
        ]);

        $this->student = Student::create([
            'user_id' => $this->childUser->id,
            'name' => $this->childUser->name,
            'pairing_code' => 'LNT-TEST01',
        ]);

        $this->subject = Subject::create([
            'title' => 'Matematika Dasar',
            'description' => 'Belajar matematika dasar',
        ]);

        $this->class = Classes::create([
            'subject_id' => $this->subject->id,
            'number' => 1,
            'title' => 'Kelas 1 SD',
            'description' => 'Materi kelas 1',
        ]);

        $this->level1 = Level::create([
            'id' => 'lvl-1',
            'class_id' => $this->class->id,
            'number' => 1,
            'title' => 'Level 1: Bilangan',
        ]);

        $chapter1 = Chapter::create([
            'id' => 'ch-1',
            'level_id' => $this->level1->id,
            'number' => 1,
            'title' => 'Mengenal Bilangan',
        ]);

        $this->unit1 = Unit::create([
            'id' => 1001,
            'chapter_id' => $chapter1->id,
            'order' => 1,
            'type' => 'text',
            'title' => 'Unit 1.1',
            'content' => 'Konten materi 1.1',
        ]);

        $this->unit2 = Unit::create([
            'id' => 1002,
            'chapter_id' => $chapter1->id,
            'order' => 2,
            'type' => 'text',
            'title' => 'Unit 1.2',
            'content' => 'Konten materi 1.2',
        ]);

        $this->level2 = Level::create([
            'id' => 'lvl-2',
            'class_id' => $this->class->id,
            'number' => 2,
            'title' => 'Level 2: Penjumlahan',
        ]);

        $chapter2 = Chapter::create([
            'id' => 'ch-2',
            'level_id' => $this->level2->id,
            'number' => 1,
            'title' => 'Penjumlahan Sederhana',
        ]);

        $this->unit3 = Unit::create([
            'id' => 1003,
            'chapter_id' => $chapter2->id,
            'order' => 1,
            'type' => 'text',
            'title' => 'Unit 2.1',
            'content' => 'Konten materi 2.1',
        ]);

        $this->level3 = Level::create([
            'id' => 'lvl-3',
            'class_id' => $this->class->id,
            'number' => 3,
            'title' => 'Level 3: Pengurangan',
        ]);

        $chapter3 = Chapter::create([
            'id' => 'ch-3',
            'level_id' => $this->level3->id,
            'number' => 1,
            'title' => 'Pengurangan Sederhana',
        ]);

        Unit::create([
            'id' => 1004,
            'chapter_id' => $chapter3->id,
            'order' => 1,
            'type' => 'text',
            'title' => 'Unit 3.1',
            'content' => 'Konten materi 3.1',
        ]);
    }

    public function test_level_two_is_locked_when_level_one_units_are_not_all_completed(): void
    {
        $response = $this->actingAs($this->childUser)->get(
            route('courses.learning-path', ['course' => $this->subject->id, 'class' => 1])
        );

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Course/LearningPath')
            ->where('course.classes.0.levels.0.isLocked', false)
            ->where('course.classes.0.levels.1.isLocked', true)
            ->where('course.classes.0.levels.2.isLocked', true)
        );
    }

    public function test_level_two_is_unlocked_when_all_level_one_units_are_marked_completed(): void
    {
        // Tandai unit pertama level 1 selesai
        $this->actingAs($this->childUser)->post(
            route('progress.units.complete', ['unit' => $this->unit1->id])
        );

        // Baru 1 dari 2 unit yang selesai, level 2 seharusnya masih terkunci
        $response = $this->actingAs($this->childUser)->get(
            route('courses.learning-path', ['course' => $this->subject->id, 'class' => 1])
        );
        $response->assertInertia(fn (Assert $page) => $page
            ->where('course.classes.0.levels.1.isLocked', true)
        );

        // Tandai unit kedua level 1 selesai
        $this->actingAs($this->childUser)->post(
            route('progress.units.complete', ['unit' => $this->unit2->id])
        );

        // Sekarang seluruh unit di level 1 sudah selesai, level 2 harus terbuka (unlocked)
        $responseAfter = $this->actingAs($this->childUser)->get(
            route('courses.learning-path', ['course' => $this->subject->id, 'class' => 1])
        );

        $responseAfter->assertOk();
        $responseAfter->assertInertia(fn (Assert $page) => $page
            ->component('Course/LearningPath')
            ->where('course.classes.0.levels.0.isLocked', false)
            ->where('course.classes.0.levels.1.isLocked', false)
            ->where('course.classes.0.levels.2.isLocked', true)
        );
    }

    public function test_accessing_locked_material_redirects_with_warning_flash(): void
    {
        // Siswa mencoba langsung mengakses materi di level 2 yang masih terkunci
        $response = $this->actingAs($this->childUser)->get(
            route('courses.material', [
                'course' => $this->subject->id,
                'class' => 1,
                'material' => $this->unit3->id,
            ])
        );

        $response->assertRedirect(
            route('courses.learning-path', ['course' => $this->subject->id, 'class' => 1])
        );
        $response->assertSessionHas('warning');
    }

    public function test_course_index_renders_with_unlocked_level_status(): void
    {
        $response = $this->actingAs($this->childUser)->get(
            route('courses.show', ['course' => $this->subject->id])
        );

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Course/Index')
            ->has('course.classes.0.levels', 3)
            ->where('course.classes.0.levels.0.isLocked', false)
            ->where('course.classes.0.levels.1.isLocked', true)
        );
    }
}
