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
        Schema::create('vendor_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id'); // could be foreign if needed
            $table->string('trx_id');
            $table->string('sender_no');
            $table->dateTime('trx_date');
            $table->decimal('amount', 15, 2);
            $table->foreignId('wallet_id')->constrained('wallets');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_transactions');
    }
};
