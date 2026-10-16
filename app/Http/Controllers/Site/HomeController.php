<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\InstructorResource;
use App\Models\Category;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Student;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featuredCourses = Course::query()
            ->published()
            ->featured()
            ->with(['category', 'instructor'])
            ->withCount('lessons')
            ->latest()
            ->take(3)
            ->get();

        // Fall back to the newest courses when nothing is flagged as featured yet.
        if ($featuredCourses->isEmpty()) {
            $featuredCourses = Course::query()
                ->published()
                ->with(['category', 'instructor'])
                ->withCount('lessons')
                ->latest()
                ->take(3)
                ->get();
        }

        $latestCourses = Course::query()
            ->published()
            ->with(['category', 'instructor'])
            ->withCount('lessons')
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['courses' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->get();

        $instructors = Instructor::query()
            ->where('is_active', true)
            ->withCount(['courses' => fn ($query) => $query->published()])
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('site/Home', [
            'featuredCourses' => CourseResource::collection($featuredCourses),
            'latestCourses' => CourseResource::collection($latestCourses),
            'categories' => CategoryResource::collection($categories),
            'instructors' => InstructorResource::collection($instructors),
            'stats' => [
                'courses' => Course::query()->published()->count(),
                'lessons' => Lesson::query()->where('is_published', true)->count(),
                'students' => Student::query()->count(),
                'instructors' => Instructor::query()->where('is_active', true)->count(),
            ],
        ]);
    }
}
