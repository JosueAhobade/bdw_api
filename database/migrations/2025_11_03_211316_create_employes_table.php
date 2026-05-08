<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->id();

            $table->string('nom');
            $table->string('prenom');
            $table->string('matricule')->unique();
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->string('dernier_diplome')->nullable();

            // Champs utiles pour un employé :
            $table->date('date_naissance')->nullable();
            $table->date('date_embauche')->nullable();
            $table->string('poste')->nullable();
            $table->string('departement')->nullable();
            $table->decimal('salaire', 10, 2)->nullable();

            // Statut (exemples : actif, en_conge, suspendu, depart)
            $table->string('status')->default('actif');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employes');
    }
};
