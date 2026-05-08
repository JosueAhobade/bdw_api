<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('objectifs', function (Blueprint $table) {
            $table->id();

            // Informations principales
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('type')->default('global');
            $table->string('periode')->nullable();

            // Valeurs cibles
            $table->decimal('objectif_valeur', 15, 2)->nullable();
            $table->string('unite')->nullable();

            // Dates
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            // Relations
            $table->unsignedBigInteger('employe_id')->nullable();
            $table->string('departement')->nullable();

            // Suivi
            $table->integer('progression')->default(0); // 0-100 %
            $table->string('status')->default('en_cours'); // atteint / non_atteint / en_cours

            $table->timestamps();

            // FK
            $table->foreign('employe_id')
                  ->references('id')->on('employes')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('objectifs');
    }
};
