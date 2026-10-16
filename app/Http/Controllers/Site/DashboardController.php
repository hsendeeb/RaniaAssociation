<?php

namespace App\Http\Controllers\Site;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * لوحة الطالب: الدورات المسجّل بها وحالة كل تسجيل.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $student = Student::query()->whereBelongsTo($user)->first();

        $enrollments = $student
            ? $student->enrollments()
                ->with(['course' => fn ($query) => $query
                    ->with(['category', 'instructor'])
                    ->withCount('lessons')])
                ->latest()
                ->get()
            : Collection::make();

        return Inertia::render('Dashboard', [
            'enrollments' => EnrollmentResource::collection($enrollments),
            'stats' => [
                'total' => $enrollments->count(),
                'pending' => $enrollments->where('status', EnrollmentStatus::Pending)->count(),
                'active' => $enrollments->where('status', EnrollmentStatus::Active)->count(),
                'completed' => $enrollments->where('status', EnrollmentStatus::Completed)->count(),
            ],
            'isAdmin' => (bool) $user->is_admin,
        ]);
    }
}
