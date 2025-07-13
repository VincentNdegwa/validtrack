<?php

namespace Tests\Feature\SubjectTypes;

use App\Models\Company;
use App\Models\SubjectType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectTypeCRUDTest extends TestCase
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
    public function it_can_list_subject_types()
    {
        SubjectType::factory()->count(5)->create([
            'company_id' => $this->company->id
        ]);
        $this->actingAs($this->admin);
        $response = $this->get('/subject-types');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('subjects/types/Index')
            ->has('subjectTypes')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_new_subject_type()
    {
        $this->actingAs($this->admin);
        
        $subjectTypeData = [
            'name' => 'Employee',
        ];
        
        $response = $this->post('/subject-types', $subjectTypeData);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('subject_types', [
            'name' => 'Employee',
            'company_id' => $this->company->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_existing_subject_type()
    {
        $this->actingAs($this->admin);
        $subjectType = SubjectType::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Original Name',
        ]);
        $response = $this->put("/subject-types/{$subjectType->id}", [
            'name' => 'Updated Name',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('subject_types', [
            'id' => $subjectType->id,
            'name' => 'Updated Name',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_a_subject_type()
    {
        $this->actingAs($this->admin);
        
        $subjectType = SubjectType::factory()->create([
            'company_id' => $this->company->id,
        ]);
        $response = $this->delete("/subject-types/{$subjectType->id}");
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check if the subject type is deleted
        $this->assertDatabaseMissing('subject_types', [
            'id' => $subjectType->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_subject_type_creation()
    {
        $this->actingAs($this->admin);
        $response = $this->post('/subject-types', [
            // Missing name
        ]);
        
        $response->assertSessionHasErrors(['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_subject_type_name_uniqueness_within_company()
    {
        $this->actingAs($this->admin);
        $subjectType = SubjectType::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Existing Name',
        ]);
        $response = $this->post('/subject-types', [
            'name' => 'Existing Name',
        ]);
        
        $response->assertSessionHasErrors(['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_can_only_access_their_company_subject_types()
    {
        $anotherCompany = Company::factory()->create();
        $anotherSubjectType = SubjectType::factory()->create([
            'company_id' => $anotherCompany->id,
        ]);
        $this->actingAs($this->admin);
        $response = $this->get("/subject-types/{$anotherSubjectType->id}");
        
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_associate_document_types_with_subject_types()
    {
        $this->actingAs($this->admin);
        
        $subjectType = SubjectType::factory()->create([
            'company_id' => $this->company->id,
        ]);
        $documentTypes = [];
        for ($i = 0; $i < 3; $i++) {
            $documentTypes[] = \App\Models\DocumentType::factory()->create([
                'company_id' => $this->company->id,
            ])->id;
        }
        $response = $this->post("/required-documents",
            [
                'subject_type_id' => $subjectType->id,
                'document_type_id' => $documentTypes[0],
                'is_required' => 1,
            ]
        );  
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $response2 = $this->post(
            "/required-documents",
            [
                'subject_type_id' => $subjectType->id,
                'document_type_id' => $documentTypes[1],
                'is_required' => 1,
            ]
        );

        $response2->assertRedirect();
        $response2->assertSessionHas('success');

        $this->assertDatabaseHas('required_document_types', [
            'subject_type_id' => $subjectType->id,
            'document_type_id' => $documentTypes[0],
            'is_required' => 1,
        ]);
        
        $this->assertDatabaseHas('required_document_types', [
            'subject_type_id' => $subjectType->id,
            'document_type_id' => $documentTypes[1],
            'is_required' => 1,
        ]);
    }
}
