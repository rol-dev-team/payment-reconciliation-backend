<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->date('upload_date')->index(); // Index to quickly search by upload date
            $table->integer('vendor_file_count')->default(0); // Number of vendor files in this batch
            $table->integer('billing_file_count')->default(0); // Number of billing files in this batch
            $table->string('status')->default('pending')->index(); // Index for filtering by batch status
            $table->timestamp('started_at')->nullable(); // When the batch processing started
            $table->timestamp('completed_at')->nullable(); // When the batch processing completed
            $table->timestamps(); // Laravel created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};