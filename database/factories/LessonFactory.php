<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->optional()->paragraph(),
            'video_url' => null,
            'duration_minutes' => $this->faker->numberBetween(20, 90),
            'position' => $this->faker->numberBetween(1, 10),
            'is_published' => true,
        ];
    }
}
