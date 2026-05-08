<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW vue_chiffre_affaires AS
            SELECT
                DATE_FORMAT(date_recette, '%Y-%m') AS periode,
                SUM(montant) AS chiffre_affaires,
                COUNT(*) AS nombre_paiements
            FROM recettes
            WHERE status = 'valide'
            GROUP BY DATE_FORMAT(date_recette, '%Y-%m')
            ORDER BY periode DESC;
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS vue_chiffre_affaires');
    }
};
