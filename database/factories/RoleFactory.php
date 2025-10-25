<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = \App\Models\Company::factory()->create();

        return [
            'name' => $this->faker->unique()->word(),
            'display_name' => $this->faker->unique()->word(),
            'description' => $this->faker->unique()->word(),
            'company_id' => $company->id,
        ];
    }
}
