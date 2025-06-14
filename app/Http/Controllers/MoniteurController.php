<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Moniteur;
use App\Models\Log;
use App\Models\User;

class MoniteurController extends Controller
{
    // Créer un moniteur
    function addMoniteur(Request $req)
    {
        try {
            // Validation des données
            $req->validate([
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'specialite' => 'required|in:code,conduite', // Vérifie que la spécialité est valide
                'user_id' => 'required|exists:users,id', // Vérifie que l'utilisateur existe
            ]);

            // Création du moniteur
            $moniteur = Moniteur::create([
                'nom' => $req->input('nom'),
                'prenom' => $req->input('prenom'),
                'specialite' => $req->input('specialite'),
            ]);

            $user = User::find($req->input('user_id'));
            // Enregistre un log avec l'action "add"
            Log::create([
                'idUser' => $req->input('user_id'),
                'user_nom' => $user->nom,
                'user_prenom' => $user->prenom,
                'user_pseudo' => $user->pseudo,
                'user_doc' => $user->created_at,
                'action' => 'add',
                'table_concernee' => 'moniteurs',
                'details' => "Moniteur(trice) ajouté(e) : {$moniteur->nom} {$moniteur->prenom} (ID: {$moniteur->id})",
                'created_at' => now(),
            ]);

            // Retourne la réponse JSON
            return response()->json([
                'status' => 'success',
                'message' => 'Moniteur ajouté avec succès',
                'moniteur' => $moniteur,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l’ajout du moniteur.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Récuperer tous les Moniteurs
    function listeMoniteur()
    {
        // Retourne tous les produits sous forme de collection
        $moniteurs = Moniteur::all();

        // Retourne la collection de moniteurs
        return response()->json([
            'status' => 'success',
            'moniteurs' => $moniteurs,
        ], 200);
    }

    // Fonction pour supprimer un moniteur par son ID
    function deleteMoniteur(Request $request, $id)
    {
        try {
            // Recherche du moniteur par ID
            $moniteur = Moniteur::find($id);

            if (!$moniteur) {
                return response()->json(['status' => 'Moniteur introuvable'], 404);
            }

            // Validation de l'ID utilisateur
            $userId = $request->input('user_id');
            if (!$userId || !User::find($userId)) {
                return response()->json(['status' => 'Erreur : ID utilisateur invalide.'], 400);
            }

            $moniteurInfo = $moniteur->nom . ' ' . $moniteur->prenom . '(ID: ' . $moniteur->id . ')';

            // Supprime le moniteur
            $moniteur->delete();

            $user = User::find($userId);
            // Enregistre un log avec l'action "delete"
            Log::create([
                'idUser' => $userId,
                'user_nom' => $user->nom,
                'user_prenom' => $user->prenom,
                'user_pseudo' => $user->pseudo,
                'user_doc' => $user->created_at,
                'action' => 'delete',
                'table_concernee' => 'moniteurs',
                'details' => "Moniteur(trice) supprimé(e) : {$moniteurInfo} (ID: {$id})",
                'created_at' => now(),
            ]);

            return response()->json(['status' => 'deleted'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la suppression.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Fonction pour récupérer un moniteur spécifique par son ID
    function getMoniteur($id)
    {
        try {
            // Recherche du moniteur par ID avec les étudiants associés
            $moniteur = Moniteur::with('etudiant')->find($id);

            if (!$moniteur) {
                return response()->json(['status' => 'Moniteur introuvable'], 404);
            }

            // Préparer la réponse avec les informations du moniteur et des étudiants
            return response()->json([
                'status' => 'success',
                'moniteur' => $moniteur,
                'etudiants' => $moniteur->etudiant->map(function ($etudiant) {
                    return [
                        'nom' => $etudiant->nom,
                        'prenom' => $etudiant->prenom,
                        'num_telephone' => $etudiant->num_telephone,
                        'motif_inscription' => $etudiant->motif_inscription,
                    ];
                }),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de la récupération des informations du moniteur.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // Modifier un moniteur par son ID
    function updateMoniteur(Request $req, $id)
    {
        try {
            // Validation des données
            $req->validate([
                'nom' => 'nullable|string|max:100',
                'prenom' => 'nullable|string|max:100',
                'specialite' => 'nullable|in:code,conduite', // Vérifie que la spécialité est valide
                'user_id' => 'required|exists:users,id', // Vérifie que l'utilisateur existe
            ]);

            // Recherche du moniteur à mettre à jour
            $moniteur = Moniteur::findOrFail($id);

            // Stocker les données initiales pour comparaison
            $originalData = $moniteur->only(['nom', 'prenom', 'specialite']);

            // Mise à jour des données du moniteur
            $moniteur->update([
                'nom' => $req->input('nom', $moniteur->nom),
                'prenom' => $req->input('prenom', $moniteur->prenom),
                'specialite' => $req->input('specialite', $moniteur->specialite),
            ]);

            // Initialiser les détails des modifications
            $details = [];

            // Comparer chaque champ pour détecter les modifications
            foreach ($originalData as $field => $oldValue) {
                $newValue = $moniteur->{$field};
                if ($oldValue !== $newValue) {
                    $details[] = "{$field}: '{$oldValue}' -> '{$newValue}'"; // Ajouter au log
                }
            }

            if (count($details) > 0) {
                // Transformer les détails en une chaîne lisible
                $detailsString = implode(', ', $details);

                $user = User::find($req->input('user_id'));
                // Enregistrer un log détaillé
                Log::create([
                    'idUser' => $req->input('user_id'),
                    'user_nom' => $user->nom,
                    'user_prenom' => $user->prenom,
                    'user_pseudo' => $user->pseudo,
                    'user_doc' => $user->created_at,
                    'action' => 'update',
                    'table_concernee' => 'moniteurs',
                    'details' => "Moniteur(trice) modifié(e) (ID: moni-{$moniteur->id}): {$detailsString}",
                    'created_at' => now(),
                ]);
            }

            // Retourner la réponse JSON
            return response()->json([
                'status' => 'success',
                'message' => 'Moniteur mis à jour avec succès',
                'moniteur' => $moniteur,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Moniteur introuvable.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour du moniteur.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
