<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
                ->where('defaults.phone_country', 'LB')
                ->has('countries')
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
                'phone_country' => 'LB',
                'phone' => '0522222222',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'name' => 'منى العبدالله',
            // يُخزَّن الرقم بصيغة E.164 الموحّدة.
            'phone' => '+961522222222',
            'phone_country' => 'LB',
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
            ->assertSessionHasErrors(['name', 'email', 'phone_country', 'phone']);
    }

    public function test_student_phone_number_must_be_unique(): void
    {
        Student::factory()->create(['phone' => '71994952']);

        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        // صيغة مختلفة لنفس الرقم يجب أن تُرفض أيضًا.
        $this->actingAs($user)
            ->from(route('enrollments.create', $course->slug))
            ->post(route('enrollments.store', $course->slug), [
                'name' => 'هدى',
                'email' => 'huda@example.com',
                'phone_country' => 'LB',
                'phone' => '+961 71 994 952',
            ])
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('students', 1);
    }

    public function test_student_phone_number_is_stored_in_e164(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $this->actingAs($user)
            ->post(route('enrollments.store', $course->slug), [
                'name' => 'هدى',
                'email' => 'huda@example.com',
                'phone_country' => 'LB',
                'phone' => '71 994 952',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'phone' => '+96171994952',
            'phone_country' => 'LB',
        ]);
    }

    public function test_foreign_phone_numbers_are_stored_with_their_country_code(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $this->actingAs($user)
            ->post(route('enrollments.store', $course->slug), [
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'phone_country' => 'GB',
                'phone' => '07911 123456',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'phone' => '+447911123456',
            'phone_country' => 'GB',
        ]);
    }

    public function test_duplicate_enrollments_are_blocked(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $payload = ['name' => 'أحمد', 'email' => 'ahmed@example.com', 'phone_country' => 'LB', 'phone' => '0500000000'];

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
                'phone_country' => 'LB',
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
                'phone_country' => 'LB',
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

    public function test_course_content_stays_locked_while_the_request_is_pending(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $course = Course::factory()->published()->has(Lesson::factory()->count(2))->create();

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        $this->actingAs($user)
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('canViewContent', false)
                ->missing('course.lessons')
            );
    }

    public function test_course_content_stays_locked_after_cancellation(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $course = Course::factory()->published()->has(Lesson::factory()->count(2))->create();

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Cancelled,
        ]);

        $this->actingAs($user)
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('canViewContent', false)
                ->missing('course.lessons')
            );
    }

    public function test_course_content_unlocks_once_the_enrollment_is_approved(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $course = Course::factory()->published()->create();

        Lesson::factory()->count(2)->create(['course_id' => $course->id]);
        Lesson::factory()->create(['course_id' => $course->id, 'is_published' => false]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('canViewContent', true)
                // الدروس المنشورة فقط، والدرس المسودة يُستثنى.
                ->has('course.lessons', 2)
            );
    }

    public function test_admins_can_preview_course_content(): void
    {
        $course = Course::factory()->published()->has(Lesson::factory()->count(2))->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('canViewContent', true)
                ->has('course.lessons', 2)
            );
    }

    public function test_lessons_without_video_still_expose_text_and_attachments(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $course = Course::factory()->published()->create();

        // درس بلا فيديو: نص + مرفق فقط (الحالة التي كانت تظهر مقفلة خطأً).
        Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Variables&Datatypes',
            'video_url' => null,
            'content' => '<p>المتغيرات وأنواع البيانات في لغة C</p>',
            'attachment' => 'lessons/handout.docx',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('canViewContent', true)
                ->has('course.lessons', 1)
                ->where('course.lessons.0.video_url', null)
                ->where('course.lessons.0.content', '<p>المتغيرات وأنواع البيانات في لغة C</p>')
                ->where('course.lessons.0.attachment', 'lessons/handout.docx')
                ->where('course.lessons.0.attachment_url', Storage::disk('public')->url('lessons/handout.docx'))
            );
    }
}
