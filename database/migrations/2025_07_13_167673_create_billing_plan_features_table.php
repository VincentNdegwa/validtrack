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
        Schema::create('billing_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_feature_id')->constrained()->cascadeOnDelete();
            $table->string('value')->default('true');
            $table->timestamps();

            $table->unique(['billing_plan_id', 'billing_feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_plan_features');
    }
};
