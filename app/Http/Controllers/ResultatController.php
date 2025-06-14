<?php

namespace App\Http\Controllers;

use App\Models\Resultat;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;

class ResultatController extends Controller
{
    /**
     * Récupérer tous les résultats.
     * Cette méthode retourne une liste de tous les résultats avec leurs relations avec la table Etudiant.
     */
    public function listeResultat()
    {
        $resultats = Resultat::with('etudiant')->get();

        return response()->json([
            'status' => 'success',
            'resultats' => $resultats,
        ], 200);
    }

    /**
     * Ajouter un nouveau résultat.
     */
    public function addResultat(Request $request)
    {
        $validated = $request->validate([
            'idEtudiant' => 'required|exists:etudiant,id',
            'idUser' => 'required|integer',
            'libelle' => 'required|string|max:100',
            'statut' => 'nullable|boolean',
        ]);

        // Vérification que l'utilisateur existe
        if (!User::find($validated['idUser'])) {
            return response()->json(['error' => 'ID utilisateur invalide.'], 400);
        }

        // Vérification de l'existence d'un résultat avec le même libellé
        $existingResultat = Resultat::where('idEtudiant', $validated['idEtudiant'])
            ->where('libelle', $validated['libelle'])
            ->first();

        if ($existingResultat) {
            return response()->json(['error' => 'Ce type de résultat existe déjà pour cet étudiant.'], 409);
        }

        // Création du résultat
        $resultat = Resultat::create($validated);

        $user = User::find($validated['idUser']);
        // Enregistrement du log
        Log::create([
            'idUser' => $validated['idUser'],
            'user_nom' => $user->nom,
            'user_prenom' => $user->prenom,
            'user_pseudo' => $user->pseudo,
            'user_doc' => $user->created_at,
            'action' => 'add',
            'table_concernee' => 'resultats',
            'details' => "Résultat ajouté pour l'étudiant ID: {$validated['idEtudiant']}, type: {$validated['libelle']}.",
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Résultat ajouté avec succès.',
            'resultat' => $resultat,
        ], 201);
    }


    /**
     * Récupérer un résultat spécifique.
     * Cette méthode retourne un résultat spécifique avec ses relations (étudiant et utilisateurs).
     */
    /* public function getResultat($id)
    {
        $resultat = Resultat::with('etudiant')->find($id);

        if (!$resultat) {
            return response()->json(['status' => 'error', 'message' => 'Résultat introuvable.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'resultat' => $resultat,
        ], 200);
    } */

    /**
     * Mettre à jour un résultat.
     * Cette méthode met à jour les champs spécifiés d'un résultat existant et enregistre un log.
     */
    public function updateResultat(Request $request, $id)
    {
        $validated = $request->validate([
            'idUser' => 'required|integer',
            'libelle' => 'sometimes|string|max:100',
            'statut' => 'sometimes|boolean',
        ]);

        $resultat = Resultat::find($id);

        if (!$resultat) {
            return response()->json(['status' => 'error', 'message' => 'Résultat introuvable.'], 404);
        }

        // Vérification que l'utilisateur existe
        if (!User::find($validated['idUser'])) {
            return response()->json(['status' => 'error', 'message' => 'ID utilisateur invalide.'], 400);
        }

        // Vérification des champs à mettre à jour
        $updateData = [];

        if (isset($validated['libelle'])) {
            $updateData['libelle'] = $validated['libelle'];
        }

        if (isset($validated['statut']) && $validated['statut'] !== $resultat->statut) {
            $updateData['statut'] = $validated['statut'];
            $updateData['updated_at'] = now(); // Mettre à jour la date de modification
        }

        if (!empty($updateData)) {
            $resultat->update($updateData);
        }

        $user = User::find($validated['idUser']);
        // Enregistrement du log
        Log::create([
            'idUser' => $validated['idUser'],
            'user_nom' => $user->nom,
            'user_prenom' => $user->prenom,
            'user_pseudo' => $user->pseudo,
            'user_doc' => $user->created_at,
            'action' => 'update',
            'table_concernee' => 'resultats',
            'details' => "Résultat mis à jour pour l'étudiant ID: {$resultat->idEtudiant}.",
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Résultat mis à jour avec succès.',
            'resultat' => $resultat,
        ], 200);
    }


    /**
     * Supprimer un résultat.
     * Cette méthode supprime un résultat spécifique après avoir vérifié l'ID utilisateur et enregistre un log.
     */
    public function deleteResultat(Request $request, $id)
    {
        $resultat = Resultat::find($id);

        if (!$resultat) {
            return response()->json(['status' => 'error', 'message' => 'Résultat introuvable.'], 404);
        }

        // Vérification de l'ID utilisateur
        $userId = $request->input('idUser');
        if (!$userId || !User::find($userId)) {
            return response()->json(['status' => 'error', 'message' => 'ID utilisateur invalide.'], 400);
        }

        $user = User::find($userId);
        // Enregistrement du log avant suppression
        Log::create([
            'idUser' => $userId,
            'user_nom' => $user->nom,
            'user_prenom' => $user->prenom,
            'user_pseudo' => $user->pseudo,
            'user_doc' => $user->created_at,
            'action' => 'delete',
            'table_concernee' => 'resultats',
            'details' => "Résultat supprimé pour l'étudiant ID: {$resultat->idEtudiant}.",
            'created_at' => now(),
        ]);

        $resultat->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Résultat supprimé avec succès.',
        ], 200);
    }

    /**
     * Récupérer les 5 derniers résultats non retirés.
     */
    public function getLastFiveResultats()
    {
        $resultats = Resultat::where('statut', false) // Filtrer les résultats non retirés
            ->orderBy('created_at', 'desc') // Trier par date de création décroissante
            ->take(5) // Limiter à 5 résultats
            ->with('etudiant') // Charger la relation avec l'étudiant
            ->get();

        return response()->json([
            'status' => 'success',
            'resultats' => $resultats,
        ], 200);
    }
}
