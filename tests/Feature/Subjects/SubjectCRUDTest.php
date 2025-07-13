<?php

namespace Tests\Feature\Subjects;

use App\Models\Company;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $admin;
    protected $subjectType;

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
        
        // Create a subject type
        $this->subjectType = SubjectType::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Employee'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_subjects()
    {
        // Create multiple subjects
        Subject::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'subject_type_id' => $this->subjectType->id
        ]);
        
        // Acting as admin
        $this->actingAs($this->admin);
        
        // Test listing subjects
        $response = $this->get('/subjects');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('subjects/Index')
            ->has('subjects.data', 5)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_new_subject()
    {
        $this->actingAs($this->admin);
        
        $subjectData = [
            'name' => 'John Doe',
            'subject_type_id' => $this->subjectType->id,
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'category' => 'individual',
            'notes' => 'Test notes',
            'status' => 1,
        ];
        
        $response = $this->post('/subjects', $subjectData);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('subjects', [
            'name' => 'John Doe',
            'subject_type_id' => $this->subjectType->id,
            'email' => 'john@example.com',
            'company_id' => $this->company->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_existing_subject()
    {
        $this->actingAs($this->admin);
        
        // Create a subject
        $subject = Subject::factory()->create([
            'company_id' => $this->company->id,
            'subject_type_id' => $this->subjectType->id,
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        
        // Update the subject
        $response = $this->put("/subjects/{$subject->id}", [
            'name' => 'Updated Name',
            'subject_type_id' => $this->subjectType->id,
            'email' => 'updated@example.com',
            'phone' => '9876543210',
            'status' => 1,
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '9876543210',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_view_subject_details()
    {
        $this->actingAs($this->admin);
        
        // Create a subject
        $subject = Subject::factory()->create([
            'company_id' => $this->company->id,
            'subject_type_id' => $this->subjectType->id,
        ]);
        
        // View the subject
        $response = $this->get("/subjects/{$subject->id}");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('subjects/Show')
            ->has('subject')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_disable_a_subject()
    {
        $this->actingAs($this->admin);
        
        // Create a subject
        $subject = Subject::factory()->create([
            'company_id' => $this->company->id,
            'status' => 1, // Active
        ]);
        
        // Disable the subject
        $response = $this->put("/subjects/{$subject->id}", [
            'name' => $subject->name,
            'subject_type_id' => $subject->subject_type_id,
            'status' => 0, // Inactive
        ]);
        
        $response->assertRedirect();
        
        // Check if the subject is now inactive
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'status' => 0,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_subject_creation()
    {
        $this->actingAs($this->admin);
        $response = $this->post('/subjects', [
            'email' => 'test@example.com',
        ]);
        
        $response->assertSessionHasErrors(['name', 'status']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_email_format()
    {
        $this->actingAs($this->admin);
        
        // Try to create a subject with invalid email
        $response = $this->post('/subjects', [
            'name' => 'Test Subject',
            'subject_type_id' => $this->subjectType->id,
            'email' => 'not-an-email',
        ]);
        
        $response->assertSessionHasErrors(['email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_can_only_access_their_company_subjects()
    {
        $anotherCompany = Company::factory()->create();
        $anotherSubjectType = SubjectType::factory()->create([
            'company_id' => $anotherCompany->id,
        ]);
        $anotherSubject = Subject::factory()->create([
            'company_id' => $anotherCompany->id,
            'subject_type_id' => $anotherSubjectType->id,
        ]);
        
        // Acting as admin of first company
        $this->actingAs($this->admin);
        
        // Try to access subject from another company
        $response = $this->get("/subjects/{$anotherSubject->id}");
        
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }
}
