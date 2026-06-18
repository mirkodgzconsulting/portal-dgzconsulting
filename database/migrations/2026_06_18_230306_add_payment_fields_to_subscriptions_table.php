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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('status');
            $table->string('payment_link')->nullable()->after('payment_method');
            $table->string('currency', 3)->default('EUR')->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_link', 'currency']);
        });
    }
};
