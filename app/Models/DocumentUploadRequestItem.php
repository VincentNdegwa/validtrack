<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentUploadRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_upload_request_id',
        'document_type_id',
        'is_required',
        'status', // pending, completed
    ];

    /**
     * Get the document upload request that owns the item.
     */
    public function uploadRequest()
    {
        return $this->belongsTo(DocumentUploadRequest::class, 'document_upload_request_id');
    }

    /**
     * Get the document type for this item.
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the document that fulfills this request item (if any).
     */
    public function document()
    {
        return $this->hasOne(Document::class, 'upload_request_item_id');
    }

    /**
     * Check if this item has been completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark this item as completed
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }
}
