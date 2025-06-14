<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    // Nom de la table associée au modèle
    protected $table = 'etudiant';

    // Champs pouvant être remplis en masse
    protected $fillable = [
        'idMoniteur', // Référence au moniteur associé
        'idUser', // Référence à l'utilisateur associé
        'nom', // Nom de l'étudiant
        'prenom', // Prénom de l'étudiant
        'dateNaissance', // Date de naissance de l'étudiant
        'lieuNaissance', // Lieu de naissance de l'étudiant
        'commune', // Commune de résidence
        'num_telephone', // Numéro de téléphone principal
        'num_telephone_2', // Numéro de téléphone secondaire
        'nom_autoEc', // Nom de l'auto-école
        'reduction', // Indique si une réduction est appliquée
        'type_piece', // Type de pièce d'identité
        'num_piece', // Numéro de la pièce d'identité
        'scolarite', // Informations sur la scolarité
        'montant_paye', // Montant payé par l'étudiant
        'motif_inscription', // Motif de l'inscription
        'categorie', // Catégorie de l'étudiant
    ];

    // Définition des types pour certains champs
    protected $casts = [
        'reduction' => 'boolean', // Cast explicite pour le champ réduction
    ];

    // Relation : un étudiant a une progression
    public function progression()
    {
        return $this->hasOne(Progression::class, 'idEtudiant');
    }

    // Relation : un étudiant appartient à un moniteur
    public function moniteur()
    {
        return $this->belongsTo(Moniteur::class, 'idMoniteur');
    }

    // Relation : un étudiant appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }

    /**
     * Récupérer les étudiants inactifs depuis une période donnée.
     */
    public static function inactifsDepuis($jours)
    {
        return self::whereDate('updated_at', '<', now()->subDays($jours))->get();
    }
}
