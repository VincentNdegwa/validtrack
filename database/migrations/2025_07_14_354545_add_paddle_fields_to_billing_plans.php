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
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->string('paddle_product_id')->nullable()->after('sort_order');
            $table->string('paddle_monthly_price_id')->nullable()->after('paddle_product_id');
            $table->string('paddle_yearly_price_id')->nullable()->after('paddle_monthly_price_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->dropColumn([
                'paddle_product_id',
                'paddle_monthly_price_id',
                'paddle_yearly_price_id',
            ]);
        });
    }
};
