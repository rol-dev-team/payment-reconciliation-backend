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
        // Table name updated to match the image: COMPARISONS_HISTORY
        Schema::create('comparisons_history', function (Blueprint $table) {
            $table->id(); // id PK (int)
            
            // Foreign Keys
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('billing_system_id')->constrained('billing_systems');
            $table->foreignId('channel_id')->constrained('payment_channels');
            
            // Standard Fields
            $table->integer('process_no');
            $table->string('trx_id');
            $table->string('sender_no');
            $table->dateTime('trx_date');
            $table->string('entity')->nullable();
            $table->string('customer_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            
            // wallet_id is shown as string in your image
            $table->string('wallet_id')->nullable(); 
            
            $table->string('status')->nullable();
            
            // Boolean Flags
            $table->boolean('is_vendor')->default(false);
            $table->boolean('is_billing_system')->default(false);
            
            // Timestamps (created_at is explicitly in your image)
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparisons_history');
    }
};