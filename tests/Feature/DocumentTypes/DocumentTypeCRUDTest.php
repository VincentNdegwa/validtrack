<?php

namespace Tests\Feature\DocumentTypes;

use App\Models\Company;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTypeCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $company;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'admin@testcompany.com',
            'owner_id' => null,
        ]);

        $this->admin = User::factory()
            ->forCompany($this->company)
            ->create([
                'role' => 'admin',
            ]);

        $this->company->owner_id = $this->admin->id;
        $this->company->save();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_document_types()
    {
        DocumentType::factory()->count(5)->create([
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->admin);
        $response = $this->get('/document-types');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('documents/types/Index')
            ->has('documentTypes')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_new_document_type()
    {
        $this->actingAs($this->admin);

        $documentTypeData = [
            'name' => 'Passport',
            'description' => 'International passport document',
            'is_active' => true,
        ];

        $response = $this->post('/document-types', $documentTypeData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('document_types', [
            'name' => 'Passport',
            'description' => 'International passport document',
            'company_id' => $this->company->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_existing_document_type()
    {
        $this->actingAs($this->admin);

        // Create a document type
        $documentType = DocumentType::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Original Name',
            'description' => 'Original description',
        ]);

        // Update the document type
        $response = $this->put("/document-types/{$documentType->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('document_types', [
            'id' => $documentType->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_a_document_type()
    {
        $this->actingAs($this->admin);

        // Create a document type
        $documentType = DocumentType::factory()->create([
            'company_id' => $this->company->id,
        ]);

        // Delete the document type
        $response = $this->delete("/document-types/{$documentType->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check if the document type is deleted or marked as inactive
        $this->assertDatabaseMissing('document_types', [
            'id' => $documentType->id,
            'is_active' => true,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_document_type_creation()
    {
        $this->actingAs($this->admin);

        // Try to create a document type with missing required fields
        $response = $this->post('/document-types', [
            // Missing name
            'description' => 'Test description',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_document_type_name_uniqueness_within_company()
    {
        $this->actingAs($this->admin);

        // Create a document type
        $documentType = DocumentType::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Existing Name',
        ]);

        // Try to create another document type with the same name
        $response = $this->post('/document-types', [
            'name' => 'Existing Name',
            'description' => 'Another description',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_can_only_access_their_company_document_types()
    {
        // Create another company and document type
        $anotherCompany = Company::factory()->create();
        $anotherDocumentType = DocumentType::factory()->create([
            'company_id' => $anotherCompany->id,
        ]);
        $this->actingAs($this->admin);

        $response = $this->get("/document-types/{$anotherDocumentType->id}");

        $response->assertStatus(302);

        $response->assertSessionHas('error', 'Unauthorized action.');
    }
}
