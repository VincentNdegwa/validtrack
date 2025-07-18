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
        Schema::create('subject_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('company_id')
                ->constrained('companies')
                ->onDelete('cascade');
            $table->timestamps();
            $table->unique(['name', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_types');
    }
};
