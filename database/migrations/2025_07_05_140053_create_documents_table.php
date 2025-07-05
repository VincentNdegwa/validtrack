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

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->onDelete('cascade');
            $table->foreignId('document_type_id')
                ->nullable()
                ->constrained('document_types')
                ->onDelete('set null');
            $table->string('file_url');
            $table->date('issue_date');
            $table->date('expiry_date')->nullable(); 
            $table->bigInteger('status')
                ->default(1);
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null'); 
            $table->string('notes')->nullable();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
