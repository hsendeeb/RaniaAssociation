<?php

namespace App\Http\Controllers\Site;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\EnrollmentResource;
use App\Models\Category;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'level' => ['nullable', 'string'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $courses = Course::query()
            ->published()
            ->with(['category', 'instructor'])
            ->withCount('lessons')
            ->when($filters['category'] ?? null, fn ($query, $slug) => $query->whereHas(
                'category',
                fn ($category) => $category->where('slug', $slug),
            ))
            ->when($filters['level'] ?? null, fn ($query, $level) => $query->where('level', $level))
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(
                fn ($where) => $where
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%"),
            ))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('site/Courses/Index', [
            'courses' => CourseResource::collection($courses),
            'categories' => CategoryResource::collection(
                Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            ),
            'levels' => collect(CourseLevel::cases())
                ->map(fn (CourseLevel $level) => ['value' => $level->value, 'label' => $level->getLabel()])
                ->values(),
            'filters' => [
                'category' => $filters['category'] ?? null,
                'level' => $filters['level'] ?? null,
                'q' => $filters['q'] ?? null,
            ],
        ]);
    }

    public function show(Request $request, Course $course): Response
    {
        abort_unless($course->status === CourseStatus::Published, 404);

        $course->load([
            'category',
            'instructor',
            'lessons' => fn ($query) => $query->where('is_published', true),
        ])->loadCount(['enrollments', 'students']);

        $relatedCourses = Course::query()
            ->published()
            ->whereKeyNot($course->getKey())
            ->when($course->category_id, fn ($query) => $query->where('category_id', $course->category_id))
            ->with(['category', 'instructor'])
            ->withCount('lessons')
            ->latest()
            ->take(3)
            ->get();

        $enrollment = $request->user()
            ? Student::query()->whereBelongsTo($request->user())->first()?->enrollmentFor($course)
            : null;

        return Inertia::render('site/Courses/Show', [
            'course' => new CourseResource($course),
            'relatedCourses' => CourseResource::collection($relatedCourses),
            'availableSeats' => $course->hasAvailableSeats(),
            'enrollment' => $enrollment ? new EnrollmentResource($enrollment) : null,
        ]);
    }
}
