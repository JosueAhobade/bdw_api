<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->unsignedBigInteger('formation_id');
            $table->unsignedBigInteger('formateur_id')->nullable();

            // Organisation
            $table->date('date_debut');
            $table->date('date_fin')->nullable();

            $table->integer('nombre_places')->nullable();
            $table->integer('places_restantes')->nullable();

            // Type de session
            $table->string('type')->default('presentiel'); // presentiel/en_ligne
            $table->string('lien_visio')->nullable();      // si en ligne

            // Statut de la session
            $table->string('status')->default('programmee');

            $table->timestamps();

            // Foreign keys
            $table->foreign('formation_id')
                  ->references('id')->on('formations')
                  ->onDelete('cascade');

            $table->foreign('formateur_id')
                  ->references('id')->on('formateurs')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
