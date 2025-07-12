<?php

namespace Tests\Feature\DocumentUploadRequests;

use App\Mail\DocumentUploadRequestMail;
use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentUploadRequest;
use App\Models\DocumentUploadRequestItem;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $admin;
    protected $subject;
    protected $documentTypes = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Mock mail and storage
        Mail::fake();
        Storage::fake('public');

        // Create company
        $this->company = Company::factory()->create();
        
        // Create admin
        $this->admin = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'admin'
        ]);
        
        // Create subject
        $this->subject = Subject::factory()->create([
            'company_id' => $this->company->id
        ]);
        
        // Create document types
        $this->documentTypes[] = DocumentType::factory()->create([
            'name' => 'Passport',
            'company_id' => $this->company->id
        ]);
        
        $this->documentTypes[] = DocumentType::factory()->create([
            'name' => 'Driver License',
            'company_id' => $this->company->id
        ]);
    }

    /** @test */
    public function admin_can_create_document_upload_request()
    {
        $this->actingAs($this->admin);
        
        $requestData = [
            'subject_id' => $this->subject->id,
            'document_types' => [
                [
                    'id' => $this->documentTypes[0]->id,
                    'required' => true
                ],
                [
                    'id' => $this->documentTypes[1]->id,
                    'required' => false
                ]
            ],
            'email' => 'recipient@example.com',
            'expiry_hours' => 48
        ];
        
        $response = $this->post('/document-upload-requests', $requestData);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Assert that upload request was created
        $this->assertDatabaseHas('document_upload_requests', [
            'subject_id' => $this->subject->id,
            'email' => 'recipient@example.com',
        ]);
        
        // Assert that request items were created
        $this->assertDatabaseHas('document_upload_request_items', [
            'document_type_id' => $this->documentTypes[0]->id,
            'is_required' => true,
        ]);
        
        $this->assertDatabaseHas('document_upload_request_items', [
            'document_type_id' => $this->documentTypes[1]->id,
            'is_required' => false,
        ]);
        
        Mail::assertQueued(DocumentUploadRequestMail::class);
    }
    
    /** @test */
    public function user_can_view_public_upload_form_with_valid_token()
    {
        // Create an upload request
        $uploadRequest = DocumentUploadRequest::factory()->create([
            'subject_id' => $this->subject->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(1)
        ]);
        
        // Create request items
        foreach ($this->documentTypes as $index => $documentType) {
            DocumentUploadRequestItem::factory()->create([
                'document_upload_request_id' => $uploadRequest->id,
                'document_type_id' => $documentType->id,
                'is_required' => $index === 0 // First is required
            ]);
        }
        
        // Access public upload form
        $response = $this->get("/document-upload/{$uploadRequest->token}");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('documents/PublicUpload')
            ->has('uploadRequest')
            ->has('uploadRequest.documentTypes', 2)
        );
    }
    
    /** @test */
    public function user_cannot_view_expired_or_used_upload_form()
    {
        // Create an expired upload request
        $expiredRequest = DocumentUploadRequest::factory()->create([
            'subject_id' => $this->subject->id,
            'status' => 'pending',
            'expires_at' => now()->subDays(1)
        ]);
        
        // Create a used upload request
        $usedRequest = DocumentUploadRequest::factory()->create([
            'subject_id' => $this->subject->id,
            'status' => 'used',
            'expires_at' => now()->addDays(1),
            'used_at' => now()
        ]);
        
        // Try to access expired form
        $response1 = $this->get("/document-upload/{$expiredRequest->token}");
        $response1->assertStatus(404);
        
        // Try to access used form
        $response2 = $this->get("/document-upload/{$usedRequest->token}");
        $response2->assertStatus(404);
    }
    
    /** @test */
    public function user_can_upload_document_with_valid_verification_code()
    {
        // Create an upload request
        $uploadRequest = DocumentUploadRequest::factory()->create([
            'subject_id' => $this->subject->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(1),
            'verification_code' => 'ABC123'
        ]);
        
        // Create request item
        $requestItem = DocumentUploadRequestItem::factory()->create([
            'document_upload_request_id' => $uploadRequest->id,
            'document_type_id' => $this->documentTypes[0]->id,
            'is_required' => true
        ]);
        
        // Create test file
        $file = UploadedFile::fake()->create('passport.pdf', 500);
        
        // Submit document upload
        $response = $this->post("/document-upload/{$uploadRequest->token}", [
            'verification_code' => 'ABC123',
            'upload_request_item_id' => $requestItem->id,
            'file' => $file,
            'issue_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
            'notes' => 'Test upload notes'
        ]);
        
        $response->assertInertia(fn ($page) => $page
            ->component('documents/PublicUploadSuccess')
            ->has('allCompleted')
        );

        $this->assertDatabaseHas('documents', [
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentTypes[0]->id,
            'company_id' => $this->company->id,
            'issue_date' => '2025-01-01 00:00:00',
            'expiry_date' => '2026-01-01 00:00:00',
            'notes' => 'Test upload notes'
        ]);
        
        // Assert request item was marked as completed
        $this->assertDatabaseHas('document_upload_request_items', [
            'id' => $requestItem->id,
            'status' => 'completed'
        ]);
        
        // If all required items completed, request should be marked as used
        $this->assertDatabaseHas('document_upload_requests', [
            'id' => $uploadRequest->id,
            'status' => 'used'
        ]);
    }
    
    /** @test */
    public function user_cannot_upload_with_incorrect_verification_code()
    {
        // Create an upload request
        $uploadRequest = DocumentUploadRequest::factory()->create([
            'subject_id' => $this->subject->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(1),
            'verification_code' => 'ABC123'
        ]);
        
        // Create request item
        $requestItem = DocumentUploadRequestItem::factory()->create([
            'document_upload_request_id' => $uploadRequest->id,
            'document_type_id' => $this->documentTypes[0]->id,
            'is_required' => true
        ]);
        
        // Create test file
        $file = UploadedFile::fake()->create('passport.pdf', 500);
        
        // Submit with incorrect verification code
        $response = $this->post("/document-upload/{$uploadRequest->token}", [
            'verification_code' => 'WRONG1',
            'upload_request_item_id' => $requestItem->id,
            'file' => $file,
            'issue_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
        ]);
        
        $response->assertSessionHas('error');
        
        // Assert no document was created
        $this->assertDatabaseMissing('documents', [
            'subject_id' => $this->subject->id,
            'document_type_id' => $this->documentTypes[0]->id,
        ]);
    }
    
    
    /** @test */
    public function multiple_documents_flow_completes_correctly()
    {
        // Create an upload request with multiple document types
        $uploadRequest = DocumentUploadRequest::factory()->create([
            'subject_id' => $this->subject->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(1),
            'verification_code' => 'ABC123'
        ]);
        
        // Create request items (2 required, 1 optional)
        $requestItems = [];
        foreach ($this->documentTypes as $index => $documentType) {
            $requestItems[] = DocumentUploadRequestItem::factory()->create([
                'document_upload_request_id' => $uploadRequest->id,
                'document_type_id' => $documentType->id,
                'is_required' => true
            ]);
        }
        
        // Add one optional item
        $optionalDocumentType = DocumentType::factory()->create([
            'name' => 'Optional Document',
            'company_id' => $this->company->id
        ]);
        
        $optionalItem = DocumentUploadRequestItem::factory()->create([
            'document_upload_request_id' => $uploadRequest->id,
            'document_type_id' => $optionalDocumentType->id,
            'is_required' => false
        ]);
        
        $requestItems[] = $optionalItem;
        
        // Upload first required document
        $this->post("/document-upload/{$uploadRequest->token}", [
            'verification_code' => 'ABC123',
            'upload_request_item_id' => $requestItems[0]->id,
            'file' => UploadedFile::fake()->create('doc1.pdf', 500),
            'issue_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
        ]);
        
        $this->assertDatabaseHas('document_upload_requests', [
            'id' => $uploadRequest->id,
            'status' => 'pending'
        ]);
        
        // Upload second required document
        $response = $this->post("/document-upload/{$uploadRequest->token}", [
            'verification_code' => 'ABC123',
            'upload_request_item_id' => $requestItems[1]->id,
            'file' => UploadedFile::fake()->create('doc2.pdf', 500),
            'issue_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
        ]);
        
        // Now all required documents are uploaded, so request should be marked as used
        $this->assertDatabaseHas('document_upload_requests', [
            'id' => $uploadRequest->id,
            'status' => 'used'
        ]);
        
        // Optional document wasn't uploaded, so it should still be pending
        $this->assertDatabaseHas('document_upload_request_items', [
            'id' => $optionalItem->id,
            'status' => 'pending'
        ]);
        
        // Trying to upload another document after request is used should fail
        $response = $this->post("/document-upload/{$uploadRequest->token}", [
            'verification_code' => 'ABC123',
            'upload_request_item_id' => $optionalItem->id,
            'file' => UploadedFile::fake()->create('doc3.pdf', 500),
            'issue_date' => '2025-01-01',
            'expiry_date' => '2026-01-01',
        ]);
        
        $response->assertSessionHas('error');
    }
}
