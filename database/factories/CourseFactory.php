<?php

namespace Database\Factories;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'instructor_id' => Instructor::factory(),
            'title' => $this->faker->sentence(3),
            'slug' => 'course-'.$this->faker->unique()->lexify('??????'),
            'summary' => $this->faker->optional()->sentence(),
            'description' => $this->faker->optional()->paragraph(),
            'level' => $this->faker->randomElement(CourseLevel::cases()),
            'price' => $this->faker->randomElement([0, 50, 100, 150, 200]),
            'status' => CourseStatus::Draft,
            'duration_hours' => $this->faker->numberBetween(5, 60),
            'capacity' => $this->faker->numberBetween(10, 50),
            'is_featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => CourseStatus::Published]);
    }
}
