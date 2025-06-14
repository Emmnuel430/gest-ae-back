<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultat extends Model
{
    use HasFactory;

    // Nom de la table associée au modèle
    protected $table = 'resultats';

    // Colonnes pouvant être assignées en masse
    protected $fillable = [
        'idEtudiant', // Identifiant de l'étudiant
        'idUser',     // Identifiant de l'utilisateur
        'libelle',    // Libellé du résultat
        'statut',     // Statut du résultat
    ];

    // Indique que le modèle utilise les colonnes created_at et updated_at
    public $timestamps = true;

    /**
     * Relation avec le modèle Etudiant.
     * Un résultat appartient à un étudiant.
     */
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'idEtudiant');
    }

    /**
     * Relation avec le modèle User (utilisateur).
     * Un résultat est associé à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }
}
