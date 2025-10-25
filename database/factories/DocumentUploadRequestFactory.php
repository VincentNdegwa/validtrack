<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentUploadRequest>
 */
class DocumentUploadRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'token' => Str::random(64),
            'verification_code' => strtoupper(Str::random(6)),
            'email' => $this->faker->safeEmail(),
            'expires_at' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'status' => 'pending', // pending, used, cancelled, expired
            'used_at' => null,
        ];
    }

    /**
     * Configure the factory to use an existing subject.
     */
    public function forSubject(Subject $subject): static
    {
        return $this->state(function (array $attributes) use ($subject) {
            return [
                'subject_id' => $subject->id,
                'email' => $subject->email ?? $this->faker->safeEmail(),
            ];
        });
    }

    /**
     * Configure the factory to create a used request.
     */
    public function used(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'used',
                'used_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        });
    }

    /**
     * Configure the factory to create a cancelled request.
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'used_at' => null,
            ];
        });
    }

    /**
     * Configure the factory to create an expired request.
     */
    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'expires_at' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
                'used_at' => null,
            ];
        });
    }
}
