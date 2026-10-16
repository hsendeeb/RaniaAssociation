<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\StudentStatus;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('05########'),
            'birth_date' => $this->faker->dateTimeBetween('-25 years', '-6 years'),
            'gender' => $this->faker->randomElement(Gender::cases()),
            'guardian_name' => $this->faker->optional()->name(),
            'guardian_phone' => $this->faker->optional()->numerify('05########'),
            'status' => StudentStatus::Active,
            'notes' => null,
        ];
    }
}
