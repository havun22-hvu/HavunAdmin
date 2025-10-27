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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['income', 'expense']);
            $table->enum('source', ['mollie', 'bunq', 'manual'])->default('manual');
            $table->string('external_id')->nullable()->comment('External ID from Mollie/Bunq');
            $table->dateTime('transaction_date');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->string('counterparty_name')->nullable();
            $table->string('counterparty_iban', 34)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->json('raw_data')->nullable()->comment('Raw API response');
            $table->boolean('matched')->default(false)->comment('Matched with invoice?');
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('type');
            $table->index('source');
            $table->index('external_id');
            $table->index('transaction_date');
            $table->index('matched');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
