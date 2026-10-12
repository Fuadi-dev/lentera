<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Classes;
use App\Models\FinalTest;
use App\Models\Level;
use App\Models\Parents;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Orang Tua & Siswa Default
        $parent = Parents::firstOrCreate(
            ['email' => 'budi@example.com'],
            ['name' => 'Budi Santoso']
        );

        Student::firstOrCreate(
            ['name' => 'Andi'],
            ['parent_id' => $parent->id]
        );

        // 2. Baca file JSON
        $jsonPath = base_path('data/courses.json');
        if (! file_exists($jsonPath)) {
            $this->command->warn('File courses.json tidak ditemukan!');

            return;
        }

        $json = file_get_contents($jsonPath);
        $subjects = json_decode($json, true);

        if (! $subjects) {
            $this->command->warn('Gagal membaca atau mem-parsing courses.json!');

            return;
        }

        foreach ($subjects as $subjectData) {
            $subject = Subject::updateOrCreate(
                ['id' => $subjectData['id']],
                [
                    'title' => $subjectData['title'],
                    'description' => $subjectData['description'] ?? null,
                    'thumbnail' => $subjectData['thumbnail'] ?? null,
                ]
            );

            if (isset($subjectData['classes'])) {
                foreach ($subjectData['classes'] as $classData) {
                    $class = Classes::updateOrCreate(
                        ['id' => $classData['id']],
                        [
                            'subject_id' => $subject->id,
                            'number' => $classData['number'],
                            'title' => $classData['title'],
                            'description' => $classData['description'] ?? null,
                        ]
                    );

                    if (isset($classData['levels'])) {
                        foreach ($classData['levels'] as $levelData) {
                            $level = Level::updateOrCreate(
                                ['id' => $levelData['id']],
                                [
                                    'class_id' => $class->id,
                                    'number' => $levelData['number'],
                                    'title' => $levelData['title'],
                                    'description' => $levelData['description'] ?? null,
                                ]
                            );

                            if (isset($levelData['chapters'])) {
                                foreach ($levelData['chapters'] as $chapterData) {
                                    $chapter = Chapter::updateOrCreate(
                                        ['id' => $chapterData['id']],
                                        [
                                            'level_id' => $level->id,
                                            'number' => $chapterData['number'],
                                            'title' => $chapterData['title'],
                                            'description' => $chapterData['description'] ?? null,
                                        ]
                                    );

                                    if (isset($chapterData['units'])) {
                                        foreach ($chapterData['units'] as $unitData) {
                                            $unit = Unit::updateOrCreate(
                                                ['id' => $unitData['id']],
                                                [
                                                    'chapter_id' => $chapter->id,
                                                    'order' => $unitData['order'],
                                                    'type' => $unitData['type'],
                                                    'title' => $unitData['title'],
                                                    'content' => $unitData['content'] ?? null,
                                                    'media_url' => $unitData['url'] ?? null,
                                                    'duration' => $unitData['duration'] ?? null,
                                                    'passing_score' => $unitData['passingScore'] ?? null,
                                                ]
                                            );

                                            $unitQuestions = $unitData['questions'] ?? [];
                                            if ($unitData['type'] === 'question' && ! $unitQuestions) {
                                                $unitQuestions = [$unitData];
                                            }

                                            foreach ($unitQuestions as $qData) {
                                                if ($unitData['type'] === 'question' && ! isset($qData['id'])) {
                                                    $qData['id'] = 'q-'.$unitData['id'].'-001';
                                                }

                                                if ($unitData['type'] === 'question') {
                                                    $qData['id'] = 'q-'.$unitData['id'].'-001';
                                                    $qData['type'] = 'dropzone';
                                                }

                                                // Map answer ids if options exist to make them globally unique
                                                $rawAnswer = $qData['answer'] ?? [];
                                                $mappedAnswer = $rawAnswer;

                                                if (isset($qData['options'])) {
                                                    $rawAnswer = is_array($rawAnswer) ? $rawAnswer : [$rawAnswer];
                                                    $mappedAnswer = [];
                                                    foreach ($rawAnswer as $ans) {
                                                        $mappedAnswer[] = $qData['id'].'-'.$ans;
                                                    }
                                                }

                                                $question = Question::updateOrCreate(
                                                    ['id' => $qData['id']],
                                                    [
                                                        'unit_id' => $unit->id,
                                                        'type' => $qData['type'],
                                                        'question' => $qData['question'],
                                                        'answer' => $mappedAnswer,
                                                        'explanation' => $qData['explanation'] ?? null,
                                                        'points' => $qData['points'] ?? 10,
                                                    ]
                                                );

                                                if (isset($qData['options']) || isset($qData['items'])) {
                                                    QuestionOption::where('question_id', $question->id)->delete();
                                                }

                                                if (isset($qData['options'])) {
                                                    foreach ($qData['options'] as $optData) {
                                                        QuestionOption::updateOrCreate(
                                                            ['id' => $qData['id'].'-'.$optData['id']],
                                                            [
                                                                'question_id' => $question->id,
                                                                'text' => $optData['text'],
                                                            ]
                                                        );
                                                    }
                                                }

                                                if (isset($qData['items'])) {
                                                    foreach ($qData['items'] as $itemData) {
                                                        QuestionOption::updateOrCreate(
                                                            ['id' => $qData['id'].'-'.$itemData['id']],
                                                            [
                                                                'question_id' => $question->id,
                                                                'text' => $itemData['label'] ?? $itemData['text'] ?? '',
                                                            ]
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if (isset($chapterData['finalTest'])) {
                                        FinalTest::updateOrCreate(
                                            ['chapter_id' => $chapter->id],
                                            [
                                                'title' => $chapterData['finalTest']['title'],
                                                'minimum_score' => $chapterData['finalTest']['minimumScore'],
                                                'question_count' => $chapterData['finalTest']['questionCount'],
                                                'unlocks_next_level' => $chapterData['finalTest']['unlocksNextLevel'] ?? false,
                                            ]
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->command->info('Data berhasil di-seed dari courses.json!');
    }
}
