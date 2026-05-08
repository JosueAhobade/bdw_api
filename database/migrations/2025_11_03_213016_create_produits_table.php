<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();

            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('categorie')->nullable();
            $table->decimal('prix', 12, 2)->default(0);
            $table->string('devise')->default('XOF');

            // Média
            $table->string('image')->nullable();

            // Statut
            $table->string('status')->default('actif');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('produits');
    }
};
