<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Classe représentant le modèle Moniteur
class Moniteur extends Model
{
    use HasFactory; // Utilisation du trait HasFactory pour la génération des données factices

    protected $table = 'moniteurs'; // Nom de la table associée au modèle

    // Champs pouvant être remplis en masse
    protected $fillable = [
        'nom',
        'prenom',
        'specialite',
        'num_telephone',
        'num_telephone_2',
        'email',
        'commune',
    ];


    // Relation entre Moniteur et Etudiant (un moniteur peut avoir plusieurs étudiants)
    public function etudiant()
    {
        return $this->hasMany(Etudiant::class, 'idMoniteur'); // Clé étrangère 'idMoniteur'
    }
}
