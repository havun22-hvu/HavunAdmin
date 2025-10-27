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
        Schema::create('api_syncs', function (Blueprint $table) {
            $table->id();
            $table->enum('service', ['mollie', 'bunq', 'gmail']);
            $table->string('type', 50)->comment('Type of sync: payments, transactions, emails');
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->integer('items_found')->default(0);
            $table->integer('items_processed')->default(0);
            $table->integer('items_created')->default(0);
            $table->integer('items_updated')->default(0);
            $table->integer('items_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable()->comment('Extra metadata');
            $table->timestamp('created_at')->nullable();

            $table->index('service');
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_syncs');
    }
};
