<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EnrollmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_the_enrollment_form(): void
    {
        $course = Course::factory()->published()->create();

        $this->get(route('enrollments.create', $course->slug))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_open_the_enrollment_form(): void
    {
        $course = Course::factory()->published()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('enrollments.create', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/Courses/Enroll')
                ->where('defaults.email', $user->email)
            );
    }

    public function test_enrolling_creates_a_student_and_a_pending_enrollment(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $this->actingAs($user)
            ->post(route('enrollments.store', $course->slug), [
                'name' => 'منى العبدالله',
                'email' => 'mona@example.com',
                'phone' => '0522222222',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'name' => 'منى العبدالله',
            'phone' => '0522222222',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Pending->value,
        ]);
    }

    public function test_enrollment_requires_valid_details(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $this->actingAs($user)
            ->post(route('enrollments.store', $course->slug), [])
            ->assertSessionHasErrors(['name', 'email', 'phone']);
    }

    public function test_student_phone_number_must_be_unique(): void
    {
        Student::factory()->create(['phone' => '0599999999']);

        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $this->actingAs($user)
            ->from(route('enrollments.create', $course->slug))
            ->post(route('enrollments.store', $course->slug), [
                'name' => 'هدى',
                'email' => 'huda@example.com',
                'phone' => '0599999999',
            ])
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('students', 1);
    }

    public function test_duplicate_enrollments_are_blocked(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $payload = ['name' => 'أحمد', 'email' => 'ahmed@example.com', 'phone' => '0500000000'];

        $this->actingAs($user)->post(route('enrollments.store', $course->slug), $payload);

        $this->actingAs($user)
            ->from(route('enrollments.create', $course->slug))
            ->post(route('enrollments.store', $course->slug), $payload)
            ->assertSessionHas('error');

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_full_courses_cannot_be_enrolled(): void
    {
        $course = Course::factory()->published()->create(['capacity' => 1]);

        Enrollment::factory()->create([
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('enrollments.create', $course->slug))
            ->post(route('enrollments.store', $course->slug), [
                'name' => 'سارة',
                'email' => 'sara@example.com',
                'phone' => '0511111111',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_students_can_cancel_their_pending_enrollment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        $this->actingAs($user)
            ->delete(route('enrollments.destroy', $enrollment))
            ->assertSessionHas('success');

        $this->assertSame(EnrollmentStatus::Cancelled, $enrollment->fresh()->status);
    }

    public function test_students_cannot_cancel_another_students_enrollment(): void
    {
        $owner = User::factory()->create();
        $ownerStudent = Student::factory()->create(['user_id' => $owner->id]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $ownerStudent->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        $other = User::factory()->create();
        Student::factory()->create(['user_id' => $other->id]);

        $this->actingAs($other)
            ->delete(route('enrollments.destroy', $enrollment))
            ->assertForbidden();
    }

    public function test_completed_enrollments_cannot_be_cancelled(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::Completed,
        ]);

        $this->actingAs($user)
            ->delete(route('enrollments.destroy', $enrollment))
            ->assertForbidden();
    }

    public function test_cancelled_enrollments_can_be_revived(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $course = Course::factory()->published()->create();

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Cancelled,
        ]);

        $this->actingAs($user)
            ->post(route('enrollments.store', $course->slug), [
                'name' => 'علي',
                'email' => 'ali@example.com',
                'phone' => '0533333333',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('enrollments', 1);
        $this->assertSame(EnrollmentStatus::Pending, Enrollment::first()->status);
    }

    public function test_dashboard_lists_the_students_enrollments(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        Enrollment::factory()->count(2)->create(['student_id' => $student->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('enrollments', 2)
                ->where('stats.total', 2)
            );
    }

    public function test_course_page_shows_the_students_enrollment_status(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $course = Course::factory()->published()->create();

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        $this->actingAs($user)
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('enrollment.status.value', EnrollmentStatus::Pending->value)
                ->where('availableSeats', true)
            );
    }

    public function test_course_page_shows_no_enrollment_for_guests(): void
    {
        $course = Course::factory()->published()->create();

        $this->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('enrollment', null));
    }
}
