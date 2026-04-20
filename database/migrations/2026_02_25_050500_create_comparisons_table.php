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
            // 🔥 id auto increment (manual define)
            $table->bigIncrements('id');

            // MUST NOT NULL for partition
            $table->timestamp('created_at')->useCurrent();

            $table->timestamp('updated_at')->nullable();
            $table->integer('batch_id');
            $table->integer('process_no');
            $table->string('trx_id');
            $table->unsignedBigInteger('billing_system_id')->nullable();
            $table->string('sender_no')->nullable();
            $table->dateTime('trx_date');
            $table->integer('entity_id')->nullable();
            $table->string('entity')->nullable();
            $table->string('customer_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_vendor')->default(false);
            $table->boolean('is_billing_system')->default(false);

            // 🔥 composite primary key (IMPORTANT)
            $table->primary(['id', 'created_at']);

            // indexes
            $table->index('created_at');
            $table->index('trx_date');
        });

        DB::statement("
            ALTER TABLE comparisons
            PARTITION BY RANGE COLUMNS(created_at) (
                PARTITION pmax VALUES LESS THAN (MAXVALUE)
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparisons');
    }
};
