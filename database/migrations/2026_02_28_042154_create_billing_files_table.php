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
            Schema::create('billing_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
                $table->foreignId('billing_system_id')->constrained('billing_systems')->onDelete('cascade');
                $table->string('original_filename');
                $table->string('stored_path');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('billing_files');
        }
};
