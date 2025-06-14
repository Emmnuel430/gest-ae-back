<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programmation extends Model
{
    use HasFactory; // Utilisation du trait HasFactory pour générer des instances de modèle pour les tests ou les seeders.

    protected $table = 'programmations'; // Nom de la table associée à ce modèle dans la base de données.

    protected $fillable = [
        'idUser',       // Identifiant de l'utilisateur associé à la programmation.
        'type',         // Type de programmation.
        'fichier_pdf',  // Chemin ou nom du fichier PDF associé.
        'date_prog',    // Date de la programmation.
    ];

    // Définition de la relation entre Programmation et User.
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser'); // Une programmation appartient à un utilisateur.
    }
}
