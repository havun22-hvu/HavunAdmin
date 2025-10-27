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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('vat_percentage', 5, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
