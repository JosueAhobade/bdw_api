<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->unsignedBigInteger('apprenant_id');
            $table->unsignedBigInteger('session_id');

            // Informations administratives
            $table->date('date_inscription')->nullable();
            $table->string('status')->default('inscrit'); // inscrit/en_attente/confirme/annule

            // Suivi pédagogique
            $table->boolean('presence')->nullable(); // true/false
            $table->decimal('note_finale', 5, 2)->nullable();
            $table->boolean('certificat_delivre')->default(false);
            $table->string('certificat_numero')->nullable();

            // Notes internes
            $table->text('commentaires')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('apprenant_id')
                  ->references('id')->on('apprenants')
                  ->onDelete('cascade');

            $table->foreign('session_id')
                  ->references('id')->on('sessions')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inscriptions');
    }
};
