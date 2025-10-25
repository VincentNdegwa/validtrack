<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DocumentType;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory()->create();
        $subject = Subject::factory()->create(['company_id' => $company->id]);
        $documentType = DocumentType::factory()->create(['company_id' => $company->id]);

        return [
            'subject_id' => $subject->id,
            'document_type_id' => $documentType->id,
            'file_url' => 'documents/'.$this->faker->uuid().'.pdf',
            'issue_date' => $this->faker->dateTimeBetween('-2 years', '-1 month'),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
            'status' => 1, // Active
            'uploaded_by' => null,
            'notes' => $this->faker->paragraph(1),
            'company_id' => $company->id,
        ];
    }

    /**
     * Configure the factory to use existing related models.
     */
    public function forCompany(Company $company): static
    {
        return $this->state(function (array $attributes) use ($company) {
            return [
                'company_id' => $company->id,
                'subject_id' => Subject::factory()->create(['company_id' => $company->id])->id,
                'document_type_id' => DocumentType::factory()->create(['company_id' => $company->id])->id,
            ];
        });
    }

    /**
     * Configure the factory to use an existing subject.
     */
    public function forSubject(Subject $subject): static
    {
        return $this->state(function (array $attributes) use ($subject) {
            return [
                'subject_id' => $subject->id,
                'company_id' => $subject->company_id,
                'document_type_id' => DocumentType::factory()->create(['company_id' => $subject->company_id])->id,
            ];
        });
    }

    /**
     * Configure the factory to use an existing document type.
     */
    public function forDocumentType(DocumentType $documentType): static
    {
        return $this->state(function (array $attributes) use ($documentType) {
            return [
                'document_type_id' => $documentType->id,
                'company_id' => $documentType->company_id,
                'subject_id' => Subject::factory()->create(['company_id' => $documentType->company_id])->id,
            ];
        });
    }

    /**
     * Configure the factory for a specific uploaded by user.
     */
    public function uploadedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Configure the factory for an expired document.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    /**
     * Configure the factory for a document expiring soon (within 30 days).
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }

    /**
     * Configure the factory for an inactive document.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
