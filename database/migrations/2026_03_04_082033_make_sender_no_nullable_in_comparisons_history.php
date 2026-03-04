<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comparisons_history', function (Blueprint $table) {
            $table->string('sender_no')->nullable()->change();
        });

        Schema::table('comparisons', function (Blueprint $table) {
            $table->string('sender_no')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('comparisons_history', function (Blueprint $table) {
            $table->string('sender_no')->nullable(false)->change();
        });

        Schema::table('comparisons', function (Blueprint $table) {
            $table->string('sender_no')->nullable(false)->change();
        });
    }
};