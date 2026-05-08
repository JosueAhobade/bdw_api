<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();

            // Informations principales
            $table->string('titre');
            $table->text('description')->nullable();
            $table->text('objectif')->nullable();
            $table->longText('programme')->nullable();

            // Informations administratives
            $table->integer('duree')->nullable(); // en heures
            $table->string('niveau')->nullable(); // debutant/intermediaire/avance
            $table->string('type')->default('presentiel'); // presentiel, en_ligne, hybride
            $table->decimal('prix', 10, 2)->nullable();

            // Organisation
            $table->unsignedBigInteger('formateur_id')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->integer('nombre_places')->nullable();

            // Statut
            $table->string('status')->default('brouillon');

            $table->timestamps();

            // Relation avec formateurs
            $table->foreign('formateur_id')
                  ->references('id')->on('formateurs')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('formations');
    }
};
