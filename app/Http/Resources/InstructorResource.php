<?php

namespace App\Http\Resources;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Instructor
 */
class InstructorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'specialization' => $this->specialization,
            'bio' => $this->bio,
            'avatar' => $this->avatarUrl(),
            'initials' => $this->initials(),
            'courses_count' => $this->whenCounted('courses'),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
        ];
    }

    protected function avatarUrl(): ?string
    {
        if (blank($this->avatar)) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    protected function initials(): string
    {
        $parts = preg_split('/\s+/u', trim((string) $this->name)) ?: [];

        return collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_substr($part, 0, 1))
            ->implode('');
    }
}
