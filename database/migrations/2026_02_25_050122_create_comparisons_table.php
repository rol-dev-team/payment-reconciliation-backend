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
        Schema::create('comparisons', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id');
            $table->integer('process_no');
            $table->string('trx_id');
            $table->foreignId('billing_system_id')->constrained('billing_systems');
            $table->string('sender_no');
            $table->string('field')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('trx_date');
            $table->string('entity')->nullable();
            $table->string('customer_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->foreignId('channel_id')->constrained('payment_channels');
            $table->foreignId('wallet_id')->constrained('wallets');
            $table->string('status')->nullable();
            $table->boolean('is_vendor')->default(false);
            $table->boolean('is_billing_system')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparisons');
    }
};
