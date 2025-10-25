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
        Schema::create('slack_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('app_id');
            $table->string('authed_user_id');
            $table->string('scope');
            $table->string('token_type');
            $table->text('access_token');
            $table->string('bot_user_id');
            $table->string('team_id');
            $table->string('team_name');
            $table->boolean('is_enterprise_install')->default(false);
            $table->string('webhook_channel')->nullable();
            $table->string('webhook_channel_id')->nullable();
            $table->string('webhook_configuration_url')->nullable();
            $table->text('webhook_url')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slack_integrations');
    }
};
