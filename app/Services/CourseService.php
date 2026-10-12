<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\Unit;

class CourseService
{
    /**
     * Get course with its full learning path hierarchy.
     */
    public function getCourse(string $courseId)
    {
        return Subject::with(['classes.levels.chapters.units', 'classes.levels.chapters.finalTest'])
            ->findOrFail($courseId);
    }

    /**
     * Get learning path for a specific class in a course.
     */
    public function getLearningPath(string $courseId, int $classNumber)
    {
        $course = $this->getCourse($courseId);

        $classExists = $course->classes->contains(function ($item) use ($classNumber) {
            return (int) $item->number === $classNumber;
        });

        if (! $classExists) {
            abort(404, 'Class not found in this course.');
        }

        return $course;
    }

    /**
     * Get a specific material (unit) with its questions.
     */
    public function getMaterial(string $courseId, int $classNumber, string $materialId)
    {
        $course = $this->getLearningPath($courseId, $classNumber);

        // Find the specific unit in the DB
        $material = Unit::with('questions.options')
            ->where('id', $materialId)
            ->whereHas('chapter.level.classes', function ($q) use ($courseId, $classNumber) {
                $q->where('subject_id', $courseId)
                    ->where('number', $classNumber);
            })->firstOrFail();

        $question = $material->questions->first();
        $material->setAttribute('passingScore', $material->passing_score);
        if ($material->type === 'question' && $question) {
            $material->setAttribute('question', $question->question);
            $material->setAttribute('answer', $question->answer);
            $material->setAttribute('explanation', $question->explanation);
            $material->setAttribute(
                'items',
                $question->options->map(fn ($option) => [
                    'id' => str($option->id)->after($question->id.'-')->toString(),
                    'label' => $option->text,
                ])->values()->all()
            );

            $answer = is_array($question->answer) ? $question->answer : [];
            $material->setAttribute(
                'dropZones',
                array_map(
                    fn ($zoneId) => [
                        'id' => $zoneId,
                        'label' => match ($zoneId) {
                            'genap' => 'Bilangan genap',
                            'ganjil' => 'Bilangan ganjil',
                            default => ucfirst($zoneId),
                        },
                    ],
                    array_keys($answer)
                )
            );
        }

        return [
            'course' => $course,
            'material' => $material,
        ];
    }
}
