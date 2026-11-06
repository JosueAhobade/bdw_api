<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('associe_mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('associe_id')->constrained('associes')->restrictOnDelete();
            // apport, interets (crédit) / remboursement, retrait (débit)
            $table->string('type', 20)->index();
            $table->decimal('montant', 14, 2);
            $table->date('date_mouvement')->index();
            $table->string('mode_paiement')->nullable();
            $table->string('reference_paiement')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('associe_mouvements');
    }
};
