<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('associes', function (Blueprint $table) {
            $table->id();

            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();

            // Informations professionnelles
            $table->string('fonction')->nullable();

            // Catégorie d'associé (ex : partenaire, actionnaire…)
            $table->decimal('part', 10, 2)->nullable();

            $table->decimal('solde', 10, 2)->nullable();

            // Dates
            $table->date('date_adhesion')->nullable();

            // Zone de notes libre
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('associes');
    }
};
