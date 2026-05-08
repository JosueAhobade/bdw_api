<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('apprenants', function (Blueprint $table) {
            $table->id();

            // Informations personnelles
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('genre')->nullable(); // homme / femme / autre

            // Situation professionnelle / éducative
            $table->string('niveau_etude')->nullable();
            $table->string('profession')->nullable();
            $table->string('employeur')->nullable();

            // Documents / Identifiants
            $table->string('num_identite')->nullable();
            $table->string('photo')->nullable();

            // Statut général
            $table->string('status')->default('actif');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('apprenants');
    }
};
