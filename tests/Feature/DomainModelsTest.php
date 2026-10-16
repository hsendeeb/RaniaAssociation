<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_course_has_lessons_enrollments_and_an_instructor(): void
    {
        $course = Course::factory()
            ->published()
            ->has(Lesson::factory()->count(3))
            ->create();

        $this->assertInstanceOf(CourseStatus::class, $course->status);
        $this->assertSame(CourseStatus::Published, $course->status);
        $this->assertCount(3, $course->lessons);
        $this->assertNotNull($course->instructor);
        $this->assertNotNull($course->category);
    }

    public function test_students_can_be_enrolled_in_courses(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->assertCount(1, $student->courses);
        $this->assertCount(1, $course->enrollments);
        $this->assertCount(1, $student->enrollments);
    }

    public function test_the_published_scope_only_returns_published_courses(): void
    {
        Course::factory()->create();
        Course::factory()->published()->create();

        $this->assertSame(1, Course::published()->count());
        $this->assertSame(2, Course::count());
    }
}
