<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW vue_comptabilite_globale AS
            SELECT
                (SELECT COALESCE(SUM(montant), 0) FROM recettes WHERE status = 'valide')
                AS total_recettes,

                (SELECT COALESCE(SUM(montant), 0) FROM depenses WHERE status = 'validee')
                AS total_depenses,

                (
                    (SELECT COALESCE(SUM(montant), 0) FROM recettes WHERE status = 'valide')
                    -
                    (SELECT COALESCE(SUM(montant), 0) FROM depenses WHERE status = 'validee')
                ) AS solde;
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS vue_comptabilite_globale");
    }
};
