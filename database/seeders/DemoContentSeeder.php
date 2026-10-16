<?php

namespace Database\Seeders;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Database\Seeder;

/**
 * بيانات تجريبية للعرض فقط (اختياري).
 *
 * التشغيل:  php artisan db:seed --class=DemoContentSeeder
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'الرياضيات', 'slug' => 'mathematics', 'sort_order' => 1],
            ['name' => 'العلوم', 'slug' => 'science', 'sort_order' => 2],
            ['name' => 'اللغات', 'slug' => 'languages', 'sort_order' => 3],
        ])->mapWithKeys(function (array $data): array {
            $category = Category::updateOrCreate(
                ['slug' => $data['slug']],
                $data + ['is_active' => true],
            );

            return [$data['slug'] => $category];
        });

        $instructors = collect([
            ['name' => 'أ. محمد العبد الله', 'email' => 'mohammed@rania-association.test', 'specialization' => 'الرياضيات'],
            ['name' => 'أ. سارة الحسن', 'email' => 'sara@rania-association.test', 'specialization' => 'اللغة الإنجليزية'],
        ])->map(fn (array $data): Instructor => Instructor::updateOrCreate(
            ['email' => $data['email']],
            $data + ['is_active' => true],
        ));

        $mathCourse = Course::updateOrCreate(
            ['slug' => 'introduction-to-mathematics'],
            [
                'category_id' => $categories['mathematics']->id,
                'instructor_id' => $instructors->first()->id,
                'title' => 'مقدمة في الرياضيات',
                'summary' => 'دورة تأسيسية تغطي أساسيات الرياضيات للأطفال والناشئة.',
                'level' => CourseLevel::Beginner,
                'price' => 0,
                'status' => CourseStatus::Published,
                'starts_at' => now()->addWeek()->toDateString(),
                'duration_hours' => 20,
                'capacity' => 30,
                'is_featured' => true,
            ],
        );

        Course::updateOrCreate(
            ['slug' => 'spoken-english-basics'],
            [
                'category_id' => $categories['languages']->id,
                'instructor_id' => $instructors->last()->id,
                'title' => 'أساسيات المحادثة بالإنجليزية',
                'summary' => 'تطوير مهارات المحادثة اليومية.',
                'level' => CourseLevel::Intermediate,
                'price' => 150,
                'status' => CourseStatus::Draft,
                'duration_hours' => 24,
                'capacity' => 20,
            ],
        );

        Lesson::updateOrCreate(
            ['course_id' => $mathCourse->id, 'position' => 1],
            ['title' => 'الأعداد والعمليات الحسابية', 'duration_minutes' => 45, 'is_published' => true],
        );

        Lesson::updateOrCreate(
            ['course_id' => $mathCourse->id, 'position' => 2],
            ['title' => 'الكسور العشرية', 'duration_minutes' => 40, 'is_published' => true],
        );

        $students = collect([
            ['name' => 'أحمد خالد', 'email' => 'ahmed@example.test', 'phone' => '0500000001'],
            ['name' => 'ليان سالم', 'email' => 'layan@example.test', 'phone' => '0500000002'],
        ])->map(fn (array $data): Student => Student::updateOrCreate(
            ['email' => $data['email']],
            $data + ['status' => StudentStatus::Active],
        ));

        Enrollment::updateOrCreate(
            ['student_id' => $students->first()->id, 'course_id' => $mathCourse->id],
            ['status' => EnrollmentStatus::Active, 'progress' => 25, 'enrolled_at' => now()->toDateString()],
        );

        Enrollment::updateOrCreate(
            ['student_id' => $students->last()->id, 'course_id' => $mathCourse->id],
            ['status' => EnrollmentStatus::Pending, 'progress' => 0, 'enrolled_at' => now()->toDateString()],
        );
    }
}
