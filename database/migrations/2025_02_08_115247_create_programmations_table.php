<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programmations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['code', 'conduite']);
            $table->string('fichier_pdf')->nullable(); // Stockera le chemin du fichier
            $table->date('date_prog')->nullable(); // Ajout de la colonne date_prog

            $table->timestamps();

            // Clés étrangères
            $table->foreignId('idUser')->constrained('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('programmations');
    }
};
