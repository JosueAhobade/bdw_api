<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();

            // Informations financières
            $table->decimal('montant', 12, 2);
            $table->string('devise')->default('XOF');
            $table->string('mode_paiement')->nullable();
            $table->string('reference_paiement')->nullable();

            // Informations comptables
            $table->string('type_depense');
            $table->text('description')->nullable();
            $table->date('date_depense')->nullable();

            // Relations
            $table->unsignedBigInteger('employe_id')->nullable();
            $table->string('preuve')->nullable(); // fichier PDF / image

            // Statut
            $table->string('status')->default('validee');

            $table->timestamps();

            // Foreign keys
            $table->foreign('employe_id')
                  ->references('id')->on('employes')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('depenses');
    }
};
