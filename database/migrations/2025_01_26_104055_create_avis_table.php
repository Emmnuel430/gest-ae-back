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
        Schema::create('resultats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idEtudiant');
            $table->unsignedBigInteger('idUser');
            $table->string('libelle', 100);
            $table->tinyInteger('statut')->default(0); // 0 : Non reçu, 1 : Reçu

            $table->timestamps();

            // Clés étrangères
            $table->foreign('idEtudiant')->references('id')->on('etudiant')->onDelete('cascade');
            $table->foreign('idUser')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resultats');
    }
};