<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Lesson
 */
class LessonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'video_url' => $this->video_url,
            'attachment' => $this->attachment,
            'attachment_url' => $this->attachmentUrl(),
            'duration_minutes' => $this->duration_minutes,
            'position' => $this->position,
        ];
    }

    protected function attachmentUrl(): ?string
    {
        if (blank($this->attachment)) {
            return null;
        }

        if (str_starts_with($this->attachment, 'http')) {
            return $this->attachment;
        }

        return Storage::disk('public')->url($this->attachment);
    }
}
