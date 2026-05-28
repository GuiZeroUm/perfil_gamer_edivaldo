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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 36)->unique();
            $table->string('nickname')->unique();            // Nickname único[cite: 1]
            $table->string('avatar')->nullable();            // URL da imagem[cite: 1]
            $table->text('bio')->nullable();                 // Bio[cite: 1]
            $table->string('country')->nullable();           // País[cite: 1]
            $table->json('platforms')->nullable();          // Plataformas favoritas[cite: 1]
            $table->json('games')->nullable();              // Jogos favoritos[cite: 1]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
