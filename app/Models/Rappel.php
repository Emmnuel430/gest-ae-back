<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rappel extends Model
{
    use HasFactory;

    protected $table = 'rappels';
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
        'idUser', // ID de l'utilisateur associé au rappel
    ];

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
            $this->date_rappel == now()->toDateString() &&
            $this->statut == 0
        ) {
            $this->update(['statut' => 1]);
        }
    }
}