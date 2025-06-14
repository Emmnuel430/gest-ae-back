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
        Schema::create('etudiant', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idMoniteur')->nullable(); // Clé étrangère optionnelle
            $table->unsignedBigInteger('idUser')->nullable(); // Clé étrangère optionnelle
            $table->string('nom');
            $table->string('prenom');
            $table->date('dateNaissance');
            $table->string('lieuNaissance')->nullable();
            $table->string('commune');
            $table->string('num_telephone');
            $table->string('num_telephone_2')->nullable(); // Numéro secondaire optionnel
            $table->string('nom_autoEc');
            $table->boolean('reduction')->default(false); // Réduction binaire
            $table->string('type_piece');
            $table->string('num_piece')->unique(); // Numéro de pièce unique
            $table->decimal('scolarite', 10, 2)->default(0); // Frais de scolarité
            $table->decimal('montant_paye', 10, 2)->default(0); // Ajoute la colonne après "scolarite"
            $table->text('motif_inscription')->nullable(); // Champ texte pour motif
            $table->text('categorie')->nullable(); // Champ texte pour catégorie
            $table->timestamps();

            // Ajout des contraintes de clé étrangère
            $table->foreign('idMoniteur')->references('id')->on('moniteurs')->onDelete('set null');
            $table->foreign('idUser')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('etudiant');
    }
};
