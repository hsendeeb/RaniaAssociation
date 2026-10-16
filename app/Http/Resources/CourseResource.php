<?php

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Course
 */
class CourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
            'cover_image' => $this->coverImageUrl(),
            'level' => [
                'value' => $this->level?->value,
                'label' => $this->level?->getLabel(),
            ],
            'price' => (float) $this->price,
            'is_free' => (float) $this->price <= 0,
            'duration_hours' => $this->duration_hours,
            'capacity' => $this->capacity,
            'is_featured' => (bool) $this->is_featured,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'lessons_count' => $this->whenCounted('lessons'),
            'students_count' => $this->whenCounted('students'),
            'enrollments_count' => $this->whenCounted('enrollments'),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'instructor' => new InstructorResource($this->whenLoaded('instructor')),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'url' => route('courses.show', $this->slug),
        ];
    }

    protected function coverImageUrl(): ?string
    {
        if (blank($this->cover_image)) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }

        return Storage::disk('public')->url($this->cover_image);
    }
}
