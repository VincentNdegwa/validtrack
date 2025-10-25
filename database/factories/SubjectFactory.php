<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\SubjectType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory()->create([
            'owner_id' => null,
        ]);

        $user = User::factory()
            ->forCompany($company)
            ->create();

        $company->owner_id = $user->id;
        $company->save();

        return [
            'name' => $this->faker->name(),
            'company_id' => $company->id,
            'subject_type_id' => SubjectType::factory()->create(['company_id' => $company->id]),
            'user_id' => $user->id,
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'category' => $this->faker->randomElement(['individual', 'organization', 'asset']),
            'notes' => $this->faker->paragraph(1),
            'status' => 1, // Active
        ];
    }

    /**
     * Configure the factory to use an existing company.
     */
    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
            'subject_type_id' => SubjectType::factory()->create(['company_id' => $company->id]),
        ]);
    }

    /**
     * Configure the factory to use an existing subject type.
     */
    public function forSubjectType(SubjectType $subjectType): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $subjectType->company_id,
            'subject_type_id' => $subjectType->id,
        ]);
    }

    /**
     * Configure the factory to link to a user.
     */
    public function withUser(?User $user = null): static
    {
        $user = $user ?? User::factory()->create();

        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Configure the factory to create an inactive subject.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
