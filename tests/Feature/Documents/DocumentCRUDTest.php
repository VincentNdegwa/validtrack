<?php

namespace Tests\Feature\Documents;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $admin;
    protected $subject;
    protected $documentType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create([
            'owner_id' => null,
        ]);

        $this->admin = User::factory()
            ->forCompany($this->company)
            ->create([
            'role' => 'admin',
        ]);

        $this->company->owner_id = $this->admin->id;
        $this->company->save();

        Log::info('Test company created:', [
            'company' => $this->company->toArray(),
            'admin' => $this->admin->toArray()
        ]);

        $this->subject = Subject::factory()->create(['company_id' => $this->company->id]);
        $this->documentType = DocumentType::factory()->create(['company_id' => $this->company->id]);
        Storage::fake('public');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_documents()
    {
        Document::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentType->id
        ]);
        $this->actingAs($this->admin);
        $response = $this->get('/documents');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('documents/Index')
            ->has('documents.data', 5)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_new_document()
    {
        $this->actingAs($this->admin);
        
        $documentData = [
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentType->id,
            'issue_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
            'notes' => 'Test document notes',
            'status' => 1,
        ];
        
        $response = $this->post('/documents', array_merge(
            $documentData,
            ['file' => $this->createTestFile()]
        ));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('documents', [
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentType->id,
            'issue_date' => '2025-01-01 00:00:00',
            'company_id' => $this->company->id,
            'expiry_date' => '2026-01-01 00:00:00',
            'notes' => 'Test document notes',
            'status' => 1,
        ]);
        
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_existing_document()
    {
        $this->actingAs($this->admin);
        
        // Create a document
        $document = Document::factory()->create([
            'company_id' => $this->company->id,
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentType->id,
            'issue_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
        ]);
        
        // Update the document
        $response = $this->put("/documents/{$document->id}", [
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentType->id,
            'issue_date' => '2025-02-01',
            'expiry_date' => '2026-02-01',
            'notes' => 'Updated notes',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'issue_date' => '2025-02-01 00:00:00',
            'expiry_date' => '2026-02-01 00:00:00',
            'notes' => 'Updated notes',
            'status' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'issue_date' => '2025-02-01 00:00:00',
            'expiry_date' => '2026-02-01 00:00:00',
            'notes' => 'Updated notes',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_a_document()
    {
        $this->actingAs($this->admin);
        
        // Create a document
        $document = Document::factory()->create([
            'company_id' => $this->company->id,
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentType->id,
        ]);
        
        // Delete the document
        $response = $this->delete("/documents/{$document->id}");
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check if the document status is updated or soft-deleted depending on your implementation
        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
            'status' => 1,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_document_creation()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/documents', [
            'issue_date' => '2025-01-01',
        ]);
        
        $response->assertSessionHasErrors(['subject_id','file', 'status']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_enforces_expiry_date_after_issue_date()
    {
        $this->actingAs($this->admin);
        $response = $this->post('/documents', [
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentType->id,
            'issue_date' => '2025-01-01',
            'expiry_date' => '2024-01-01', 
            'file' => $this->createTestFile(),
        ]);
        
        $response->assertSessionHasErrors('expiry_date');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_can_only_access_their_company_documents()
    {
        $anotherCompany = Company::factory()->create();
        $anotherDocument = Document::factory()->create([
            'company_id' => $anotherCompany->id,
        ]);
        
        // Acting as admin of first company
        $this->actingAs($this->admin);
        
        // Try to access document from another company
        $response = $this->get("/documents/{$anotherDocument->id}");
        
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }
    
    /**
     * Helper to create a test file for uploads
     */
    protected function createTestFile($filename = 'test.pdf')
    {
        $path = storage_path('app/public/test');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        
        $filePath = $path . '/' . $filename;
        file_put_contents($filePath, 'Test file content');
        
        return new \Illuminate\Http\UploadedFile(
            $filePath,
            $filename,
            'application/pdf',
            null,
            true
        );
    }
}
