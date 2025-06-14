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
        Schema::create('rappels_imp', function (Blueprint $table) {
            $table->id(); // Identifiant unique
            $table->string('titre'); // Titre du rappel
            $table->text('description')->nullable(); // Description détaillée
            $table->date('date_rappel')->nullable(); // Date et heure du rappel
            $table->string('type')->nullable(); // Type de rappel (ex: leçon, paiement, examen)
            $table->enum('priorite', ['basse', 'moyenne', 'élevée'])->default('basse'); // Niveau de priorité
            $table->tinyInteger('statut')->default(0); // Statut du rappel (0 = en attente, 1 = terminé)
            $table->unsignedBigInteger('model_id'); // ID de l'objet concerné
            $table->string('model_type');           // Type du modèle (ex: App\Models\Etudiant)
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Annule les migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rappels_imp');
    }
};
