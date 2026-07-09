<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   

    /**
     * Reverse the migrations.
     */
public function up(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('payment_method')->nullable();
        $table->string('chargily_checkout_id')->nullable();
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['payment_method', 'chargily_checkout_id']);
    });
}
};