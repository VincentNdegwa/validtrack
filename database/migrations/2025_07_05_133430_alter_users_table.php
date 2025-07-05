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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete()
                ->after('id');
            $table->string('role')->default('admin')->after('company_id');
            $table->string('phone')->nullable()->after('role');
            $table->string('address')->nullable()->after('phone');
            $table->string('website')->nullable()->after('address');
            $table->string('location')->nullable()->after('website');
            $table->string('logo')->nullable()->after('location');
            $table->boolean('is_active')->default(true)->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'company_id',
                'role',
                'phone',
                'address',
                'location',
                'logo',
                'is_active'
            ]);
        });
    }
};
