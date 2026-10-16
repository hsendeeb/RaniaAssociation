<?php

namespace App\Http\Resources;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Enrollment
 */
class EnrollmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->getLabel(),
            ],
            'progress' => (int) $this->progress,
            'enrolled_at' => $this->enrolled_at?->toDateString(),
            'completed_at' => $this->completed_at?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'course' => new CourseResource($this->whenLoaded('course')),
        ];
    }
}
