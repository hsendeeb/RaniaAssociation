<?php

namespace App\Http\Controllers\Site;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Rules\UniqueStudentPhone;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    /**
     * نموذج التسجيل في دورة.
     */
    public function create(Request $request, Course $course): Response|RedirectResponse
    {
        abort_unless($course->status === CourseStatus::Published, 404);

        $student = $this->studentFor($request->user());

        if ($student?->enrollmentFor($course)) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'أنت مسجّل في هذه الدورة بالفعل.');
        }

        if (! $course->hasAvailableSeats()) {
            return redirect()
                ->route('courses.show', $course->slug)
                ->with('error', 'عذرًا، اكتمل عدد المقاعد في هذه الدورة.');
        }

        return Inertia::render('site/Courses/Enroll', [
            'course' => new CourseResource(
                $course->load(['category', 'instructor'])->loadCount('lessons'),
            ),
            'countries' => PhoneNumber::options(),
            'defaults' => [
                'name' => $student?->name ?? $request->user()->name,
                'email' => $student?->email ?? $request->user()->email,
                'phone_country' => $student?->phone_country ?? PhoneNumber::defaultCountry(),
                'phone' => PhoneNumber::national($student?->phone) ?? '',
            ],
        ]);
    }

    /**
     * إنشاء طلب تسجيل بحالة «قيد الانتظار» ليُراجع من الإدارة.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->status === CourseStatus::Published, 404);

        // حوّل رقم الهاتف إلى صيغة E.164 الموحّدة اعتمادًا على الدولة المختارة
        // حتى يتطابق مع القيم المخزّنة عند التحقّق من التفرّد.
        $country = $request->input('phone_country');
        $phone = $request->input('phone');

        $request->merge([
            'phone' => is_string($phone) && is_string($country)
                ? PhoneNumber::toE164($phone, $country)
                : null,
        ]);

        $existingStudent = $this->existingStudent($request->user());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_country' => ['required', 'string', Rule::in(PhoneNumber::countryCodes())],
            'phone' => [
                'required', 'string', 'max:20',
                'regex:/^\+[1-9]\d{6,14}$/',
                new UniqueStudentPhone(is_string($country) ? $country : null, $existingStudent?->id),
            ],
        ], [
            'phone.regex' => 'رقم الهاتف غير صالح.',
        ]);

        if ($this->studentFor($request->user())?->enrollmentFor($course)) {
            return back()->with('error', 'أنت مسجّل في هذه الدورة بالفعل.');
        }

        if (! $course->hasAvailableSeats()) {
            return back()->with('error', 'عذرًا، اكتمل عدد المقاعد في هذه الدورة.');
        }

        $student = $this->resolveStudent($request->user(), $validated);

        // updateOrCreate يسمح بإحياء تسجيل ملغى سابق بدلًا من التعارض مع
        // القيد الفريد (student_id, course_id).
        Enrollment::updateOrCreate(
            [
                'student_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'status' => EnrollmentStatus::Pending,
                'enrolled_at' => null,
                'completed_at' => null,
            ],
        );

        return redirect()
            ->route('dashboard')
            ->with('success', 'تم إرسال طلب التسجيل بنجاح، وسيتم مراجعته من قِبل الإدارة.');
    }

    /**
     * إلغاء تسجيل الطالب في دورة.
     */
    public function destroy(Request $request, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->student?->user_id === $request->user()->id, 403);
        abort_unless($enrollment->status?->isCancellable() ?? false, 403);

        $enrollment->update([
            'status' => EnrollmentStatus::Cancelled,
            'completed_at' => null,
        ]);

        return back()->with('success', 'تم إلغاء التسجيل في الدورة.');
    }

    /**
     * ملف الطالب المرتبط بحساب المستخدم.
     *
     * نستعلم من قاعدة البيانات مباشرة بدلًا من علاقة `$user->student` حتى لا
     * نقع في نسخة علاقة مخزنة مؤقتًا وقديمة داخل الطلب الواحد.
     */
    protected function studentFor(User $user): ?Student
    {
        return Student::query()->whereBelongsTo($user)->first();
    }

    /**
     * ملف الطالب الذي سيُحدَّث عند التسجيل: الملف المرتبط بالحساب، أو ملف يتيم
     * بنفس البريد الإلكتروني قبل ربطه بالحساب.
     */
    protected function existingStudent(User $user): ?Student
    {
        return $this->studentFor($user)
            ?? Student::query()->whereNull('user_id')->where('email', $user->email)->first();
    }

    /**
     * إيجاد ملف الطالب أو إنشاؤه، مع ربط أي ملف يتيم بنفس البريد الإلكتروني.
     *
     * @param  array{name: string, email: string, phone: string, phone_country: string}  $data
     */
    protected function resolveStudent(User $user, array $data): Student
    {
        $student = $this->existingStudent($user) ?? new Student;

        $student->fill([
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_country' => $data['phone_country'],
            'phone' => $data['phone'],
            'status' => $student->status ?? StudentStatus::Active,
        ])->save();

        return $student;
    }
}
