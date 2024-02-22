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
        Schema::create('adaptation_keys_i18n', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adaptation_key_id')->constrained()->onDelete('cascade');
            $table->string('locale');
            $table->string('title');
            $table->longText('desc')->nullable();
            $table->longText('purpose')->nullable();
            $table->unique(['adaptation_key_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adaptation_keys_i18n');
    }
};
