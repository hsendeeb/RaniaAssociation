<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'duration_minutes' => $this->duration_minutes,
            'position' => $this->position,
        ];
    }
}
