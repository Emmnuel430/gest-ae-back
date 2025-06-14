<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RappelImp extends Model
{
    use HasFactory;

    protected $table = 'rappels_imp';
    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'titre',          // Titre du rappel
        'description',    // Description détaillée du rappel
        'date_rappel',    // Date et heure du rappel
        'type',           // Type de rappel (ex: leçon, paiement, examen, etc.)
        'priorite',       // Niveau de priorité (ex: basse, moyenne, élevée)
        'statut',         // Statut du rappel (0 = en attente, 1 = terminé)
        'model_id',        // ID de l’élément concerné (étudiant, etc.)
        'model_type',      // Classe du modèle concerné (App\Models\Etudiant, etc.)
    ];

    public function model()
    {
        // Relation polymorphe pour récupérer le modèle associé au rappel
        // Par exemple, si le rappel est associé à un étudiant, il renverra l'instance de l'étudiant
        // Si le rappel est associé à un autre modèle, il renverra cet autre modèle
        return $this->morphTo();
    }

    /**
     * Relation avec le modèle User (utilisateur).
     * Un rappel appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }

    public function checkAndCloseIfToday()
    {
        if (
            $this->date_rappel &&
            \Carbon\Carbon::parse($this->date_rappel)->isToday() &&
            $this->statut == 0
        ) {
            $this->update(['statut' => 1]);
        }
    }

}