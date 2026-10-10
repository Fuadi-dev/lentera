<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Services\CourseService;
use Inertia\Inertia;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function show(CourseRequest $request, string $course)
    {
        $courseData = $this->courseService->getCourse($course);
        
        return Inertia::render('Course/Index', [
            'course' => $courseData,
        ]);
    }

    public function learningPath(CourseRequest $request, string $course, int $class)
    {
        $courseData = $this->courseService->getLearningPath($course, $class);

        return Inertia::render('Course/LearningPath', [
            'course' => $courseData,
            'classNumber' => $class,
        ]);
    }

    public function material(CourseRequest $request, string $course, int $class, int $material)
    {
        $data = $this->courseService->getMaterial($course, $class, $material);

        return Inertia::render('Course/Material', [
            'course' => $data['course'],
            'classNumber' => $class,
            'material' => $data['material'],
        ]);
    }
}
