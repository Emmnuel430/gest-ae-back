<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progression extends Model
{
    use HasFactory;

    // Nom de la table explicitement défini
    protected $table = 'progression';

    // Champs pouvant être remplis en masse
    protected $fillable = [
        'idEtudiant', // Référence à l'identifiant de l'étudiant
        'etape',      // Étape actuelle de la progression⏬
        /* 
        const etapesPermis = [
            { value: "inscription", label: "Inscription" },
            { value: "visite_médicale", label: "Visite Médicale" },
            { value: "cours_de_code", label: "Cours de Code" },
            { value: "examen_de_code", label: "Examen de Code" },
            { value: "programmé_pour_le_code", label: "Programmé pour le code" },
            { value: "cours_de_conduite", label: "Cours de Conduite" },
            { value: "examen_de_conduite", label: "Examen de Conduite" },
            {
            value: "programmé_pour_la_conduite",
            label: "Programmé pour la conduite",
            },
        ];

        // Étapes spécifiques pour le recyclage
        const etapeRecyclage = [
            { value: "inscription", label: "Inscription" },
            { value: "cours_de_conduite", label: "Cours de Conduite" },
            { value: "examen_de_conduite", label: "Examen de Conduite" },
            {
            value: "programmé_pour_la_conduite",
            label: "Programmé pour la conduite",
            },
        ];
        */
    ];

    // Définition de la relation avec le modèle Etudiant
    public function etudiant()
    {
        // Une progression appartient à un étudiant
        return $this->belongsTo(Etudiant::class, 'idEtudiant');
    }
}
