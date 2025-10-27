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
        Schema::table('invoices', function (Blueprint $table) {
            // Link to parent invoice (master record)
            $table->foreignId('parent_invoice_id')->nullable()->constrained('invoices')->onDelete('set null');

            // Flag to indicate this is a duplicate/linked record
            $table->boolean('is_duplicate')->default(false);

            // Memorial reference (first 12 chars of UUID) - THE UNIQUE KEY FOR MATCHING
            $table->string('memorial_reference', 12)->nullable()->index();

            // Matching confidence score (0-100) - for automatic matching
            $table->integer('match_confidence')->nullable();

            // Notes about the matching/linking
            $table->text('match_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['parent_invoice_id']);
            $table->dropColumn([
                'parent_invoice_id',
                'is_duplicate',
                'memorial_reference',
                'match_confidence',
                'match_notes',
            ]);
        });
    }
};
