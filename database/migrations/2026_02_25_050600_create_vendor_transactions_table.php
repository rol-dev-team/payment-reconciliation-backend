<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('row_index')->nullable()->index();
            
            // Foreign key to batches table
            $table->foreignId('batch_id')
                  ->nullable()
                  ->index()
                  ->constrained('batches')
                  ->onDelete('set null');
            
            // Foreign key to wallets table
            $table->foreignId('wallet_id')
                  ->index()
                  ->constrained('wallets')
                  ->onDelete('cascade');
            
            $table->string('trx_id')->index(); // Index for faster searching by transaction ID
            $table->string('sender_no');
            $table->dateTime('trx_date')->index(); // Index for filtering reports by date
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_transactions');
    }
};