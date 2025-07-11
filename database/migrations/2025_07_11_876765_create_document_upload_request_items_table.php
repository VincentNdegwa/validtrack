<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_upload_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_upload_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained()->onDelete('cascade');
            $table->boolean('is_required')->default(false);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
            
            $table->unique(['document_upload_request_id', 'document_type_id'], 'unique_document_type_per_request');
        });
        
        // Add a column to documents to track which request item they fulfill
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('upload_request_item_id')->nullable()->constrained('document_upload_request_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['upload_request_item_id']);
            $table->dropColumn('upload_request_item_id');
        });
        
        Schema::dropIfExists('document_upload_request_items');
    }
};
