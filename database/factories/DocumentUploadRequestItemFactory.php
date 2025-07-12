<?php

namespace Database\Factories;

use App\Models\DocumentType;
use App\Models\DocumentUploadRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentUploadRequestItem>
 */
class DocumentUploadRequestItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uploadRequest = DocumentUploadRequest::factory()->create();
        $subject = $uploadRequest->subject;
        
        return [
            'document_upload_request_id' => $uploadRequest->id,
            'document_type_id' => DocumentType::factory()->create(['company_id' => $subject->company_id])->id,
            'is_required' => $this->faker->boolean(70), // 70% chance of being required
            'status' => 'pending', // pending, completed
        ];
    }
    
    /**
     * Configure the factory to use an existing document upload request.
     */
    public function forUploadRequest(DocumentUploadRequest $uploadRequest): static
    {
        return $this->state(function (array $attributes) use ($uploadRequest) {
            $subject = $uploadRequest->subject;
            
            return [
                'document_upload_request_id' => $uploadRequest->id,
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
            $uploadRequest = DocumentUploadRequest::factory()->create();
            
            return [
                'document_upload_request_id' => $uploadRequest->id,
                'document_type_id' => $documentType->id,
            ];
        });
    }
    
    /**
     * Configure the factory for a required document.
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }
    
    /**
     * Configure the factory for an optional document.
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }
    
    /**
     * Configure the factory for a completed item.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
