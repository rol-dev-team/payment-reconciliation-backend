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
        Schema::create('billing_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_system_id')->constrained('billing_systems');
            $table->string('trx_id');
            $table->string('entity')->nullable();
            $table->string('customer_id');
            $table->string('sender_no');
            $table->decimal('amount', 15, 2);
            $table->dateTime('trx_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_transactions');
    }
};
