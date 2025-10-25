<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentUploadRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'subject_id',
        'token',
        'verification_code',
        'email',
        'expires_at',
        'status', // pending, used, cancelled, expired
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    protected $hidden = [
        'verification_code',
    ];

    public static function generateToken(): string
    {
        return Str::uuid()->toString();
    }

    public static function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function items()
    {
        return $this->hasMany(DocumentUploadRequestItem::class, 'document_upload_request_id');
    }

    public function documentTypes()
    {
        return $this->hasManyThrough(
            DocumentType::class,
            DocumentUploadRequestItem::class,
            'document_upload_request_id',
            'id',
            'id',
            'document_type_id'
        );
    }

    public function isExpired(): bool
    {
        return now()->gt($this->expires_at);
    }

    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsUsed(): void
    {
        $this->status = 'used';
        $this->used_at = now();
        $this->save();
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }
}
