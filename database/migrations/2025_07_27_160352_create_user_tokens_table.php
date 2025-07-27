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
        Schema::create('user_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('facebook_id')->nullable()->unique();
            $table->text('facebook_token')->nullable();
            $table->string('facebook_refresh_token')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->text('google_token')->nullable();
            $table->string('google_refresh_token')->nullable();
            $table->string('twitter_id')->nullable();
            $table->text('twitter_token')->nullable();
            $table->string('twitter_refresh_token')->nullable();
            $table->string('linkedin_id')->nullable();
            $table->text('linkedin_token')->nullable();
            $table->string('linkedin_refresh_token')->nullable();
            $table->string('github_id')->nullable();
            $table->text('github_token')->nullable();
            $table->string('github_refresh_token')->nullable();
            $table->string('instagram_id')->nullable();
            $table->text('instagram_token')->nullable();
            $table->string('instagram_refresh_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tokens');
    }
};
