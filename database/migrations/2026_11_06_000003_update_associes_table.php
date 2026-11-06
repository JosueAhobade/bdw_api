<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('associes', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->decimal('part', 5, 2)->nullable()->comment('Part du capital en %')->change();
            $table->string('devise', 10)->default('XOF')->after('part');
            // Le solde est calculé à partir des mouvements
            $table->dropColumn('solde');
        });
    }

    public function down()
    {
        Schema::table('associes', function (Blueprint $table) {
            $table->decimal('solde', 10, 2)->nullable();
            $table->dropColumn('devise');
            $table->decimal('part', 10, 2)->nullable()->comment('')->change();
            $table->string('email')->nullable(false)->change();
        });
    }
};
