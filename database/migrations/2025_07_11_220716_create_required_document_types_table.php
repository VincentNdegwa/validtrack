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
        Schema::create('required_document_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(
                ['subject_type_id', 'document_type_id', 'company_id'],
                'req_doc_types_subj_doc_comp_unique' 
            );       
 });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_document_types');
    }
};
