<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_channels', function (Blueprint $table) {
        $table->id();
        // Index added to foreignId for faster JOINs
        $table->foreignId('payment_method_id')->index()->constrained()->cascadeOnDelete();
        $table->string('channel_name');
        $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_channels');
    }
};