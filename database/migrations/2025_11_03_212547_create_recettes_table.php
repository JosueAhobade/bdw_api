<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recettes', function (Blueprint $table) {
            $table->id();

            // Informations financières
            $table->decimal('montant', 12, 2);
            $table->string('devise')->default('XOF');
            $table->string('mode_paiement')->nullable(); // cash, momo, virement...
            $table->string('reference_paiement')->nullable(); // reçu, transaction...

            // Informations comptables
            $table->string('type_recette'); // formation, service, vente...
            $table->text('description')->nullable();
            $table->date('date_recette')->nullable();

            // Relations (optionnelles)
            $table->unsignedBigInteger('inscription_id')->nullable();
            $table->unsignedBigInteger('employe_id')->nullable();

            // Statut
            $table->string('status')->default('valide'); // valide, en_attente, annule

            $table->timestamps();

            // Foreign Keys
            $table->foreign('inscription_id')
                  ->references('id')->on('inscriptions')
                  ->onDelete('set null');

            $table->foreign('employe_id')
                  ->references('id')->on('employes')
                  ->onDelete('set null');

        });
    }

    public function down()
    {
        Schema::dropIfExists('recettes');
    }
};
