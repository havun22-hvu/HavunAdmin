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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null')->comment('For income');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null')->comment('For expense');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null')->comment('For expense');

            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('description')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0)->comment('Excl. BTW');
            $table->decimal('vat_amount', 10, 2)->default(0)->comment('BTW bedrag');
            $table->decimal('vat_percentage', 5, 2)->default(0)->comment('BTW percentage (21.00)');
            $table->decimal('total', 10, 2);

            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->string('payment_method', 50)->nullable();
            $table->string('reference')->nullable()->comment('External reference');
            $table->string('file_path')->nullable()->comment('Path to PDF');

            $table->enum('source', ['manual', 'mollie', 'bunq', 'gmail'])->default('manual');
            $table->string('mollie_payment_id')->nullable();
            $table->string('bunq_transaction_id')->nullable();
            $table->string('gmail_message_id')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('status');
            $table->index('invoice_date');
            $table->index('user_id');
            $table->index('project_id');
            $table->index('customer_id');
            $table->index('supplier_id');
            $table->index('category_id');
            $table->index('mollie_payment_id');
            $table->index('bunq_transaction_id');
            $table->index('gmail_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
