<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstructorResource;
use App\Models\Instructor;
use Inertia\Inertia;
use Inertia\Response;

class InstructorController extends Controller
{
    public function index(): Response
    {
        $instructors = Instructor::query()
            ->where('is_active', true)
            ->withCount(['courses' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        return Inertia::render('site/Instructors/Index', [
            'instructors' => InstructorResource::collection($instructors),
        ]);
    }

    public function show(Instructor $instructor): Response
    {
        abort_unless($instructor->is_active, 404);

        $instructor->load([
            'courses' => fn ($query) => $query
                ->published()
                ->with('category')
                ->withCount('lessons')
                ->latest(),
        ])->loadCount(['courses' => fn ($query) => $query->published()]);

        return Inertia::render('site/Instructors/Show', [
            'instructor' => new InstructorResource($instructor),
        ]);
    }
}
