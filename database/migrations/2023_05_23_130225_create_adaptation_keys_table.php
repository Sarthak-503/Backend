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
        Schema::create('adaptation_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adaptation_id')->constrained()->onDelete('cascade');
            $table->string('key')->nullable()->unique();
            $table->string('code')->nullable()->unique();
            $table->string('parent_id')->nullable();
            $table->timestamps();

            $table->index('adaptation_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adaptation_keys');
    }
};
