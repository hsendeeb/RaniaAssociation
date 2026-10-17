<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\Gender;
use App\Enums\StudentStatus;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'birth_date',
        'gender',
        'guardian_name',
        'guardian_phone',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'gender' => Gender::class,
            'status' => StudentStatus::class,
        ];
    }

    /**
     * Store a blank phone as NULL so the unique index only applies to real
     * numbers (a unique column ignores NULL values).
     *
     * @return Attribute<string|null, string|null>
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => filled($value) ? $value : null,
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * الدورات المسجّل بها الطالب.
     *
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot(['status', 'progress', 'enrolled_at'])
            ->withTimestamps();
    }

    /**
     * تسجيل الطالب في دورة محددة — يشمل الطلبات قيد الانتظار والنشطة والمكتملة،
     * ويستثني التسجيلات الملغاة حتى يتمكّن الطالب من إعادة التسجيل.
     */
    public function enrollmentFor(Course $course): ?Enrollment
    {
        return $this->enrollments()
            ->where('course_id', $course->getKey())
            ->whereIn('status', [
                EnrollmentStatus::Pending->value,
                EnrollmentStatus::Active->value,
                EnrollmentStatus::Completed->value,
            ])
            ->first();
    }
}
