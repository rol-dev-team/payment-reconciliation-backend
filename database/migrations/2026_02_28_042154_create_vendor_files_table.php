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
            Schema::create('vendor_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
                $table->foreignId('channel_id')->constrained('payment_channels')->onDelete('cascade');
                $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
                $table->string('original_filename');
                $table->string('stored_path');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('vendor_files');
        }
};
