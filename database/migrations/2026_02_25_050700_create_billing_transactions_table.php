<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('row_index')->index()->nullable();
            $table->foreignId('billing_system_id')->index()->constrained('billing_systems')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->index()->constrained('batches')->onDelete('set null');

            $table->string('trx_id')->index();
            $table->string('entity')->nullable();
            $table->string('customer_id')->index();
            $table->string('sender_no')->nullable();
            $table->decimal('amount', 15, 2);
            $table->dateTime('trx_date')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_transactions');
    }
};