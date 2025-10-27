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
        Schema::create('oauth_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('service', 50)->comment('Service name: gmail, etc.');
            $table->text('access_token')->nullable()->comment('Access token (encrypted)');
            $table->text('refresh_token')->nullable()->comment('Refresh token (encrypted)');
            $table->dateTime('expires_at')->nullable()->comment('Expiry time of access token');
            $table->text('scope')->nullable()->comment('OAuth scopes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('service');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_tokens');
    }
};
