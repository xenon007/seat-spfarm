<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('spfarm_characters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('character_id');
            $table->boolean('is_farm')->default(false);
            $table->boolean('pi_enabled')->default(false);
            $table->text('plan_text')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'character_id']);
        });

        Schema::create('spfarm_user_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->boolean('show_idle_table')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('spfarm_characters');
        Schema::dropIfExists('spfarm_user_settings');
    }
};