<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Lien optionnel vers une formation
            $table->foreignId('formation_id')
                ->nullable()
                ->constrained('formations')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();

            $table->string('location')->nullable();

            $table->boolean('is_online')->default(false);
            $table->string('online_link')->nullable();

            $table->integer('capacity')->nullable();

            $table->enum('status', [
                'supprimé',
                'publié',
                'annulé',
                'terminé',
            ])->default('publié');

            $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
};
