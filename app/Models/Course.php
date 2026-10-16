<?php

namespace App\Models;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'instructor_id',
        'title',
        'slug',
        'summary',
        'description',
        'cover_image',
        'level',
        'price',
        'status',
        'starts_at',
        'ends_at',
        'duration_hours',
        'capacity',
        'is_featured',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => CourseLevel::class,
            'status' => CourseStatus::class,
            'price' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'duration_hours' => 'integer',
            'capacity' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Instructor, $this>
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * الطلاب المسجّلون في هذه الدورة.
     *
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments')
            ->withPivot(['status', 'progress', 'enrolled_at'])
            ->withTimestamps();
    }

    /**
     * @param  Builder<Course>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', CourseStatus::Published);
    }

    /**
     * @param  Builder<Course>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * هل ما زال هناك مقاعد متاحة في الدورة؟
     *
     * تُحتسب الطلبات قيد الانتظار والتسجيلات النشطة ضمن السعة، أما الملغاة
     * فلا تشغل مقعدًا. الدورة بلا سعة محددة تُعدّ مفتوحة دائمًا.
     */
    public function hasAvailableSeats(): bool
    {
        if (blank($this->capacity)) {
            return true;
        }

        $reserved = $this->enrollments()
            ->whereIn('status', [
                EnrollmentStatus::Pending->value,
                EnrollmentStatus::Active->value,
            ])
            ->count();

        return $reserved < $this->capacity;
    }
}
