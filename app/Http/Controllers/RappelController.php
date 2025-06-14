<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rappel;
use App\Models\RappelImp;
use App\Models\Log;
use App\Models\Etudiant;
use App\Models\Resultat;
use App\Models\Programmation;
use App\Models\User;

class RappelController extends Controller
{
    // Créer un rappel
    public function addRappel(Request $req)
    {
        try {
            // Validation des données
            $req->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date_rappel' => 'required|date',
                'type' => 'required|string|max:100',
                'priorite' => 'required|string|in:basse,moyenne,élevée',
                'statut' => 'required|boolean', // Accepte uniquement true/false
                'idUser' => 'required|exists:users,id',
            ]);

            // Création du rappel
            $rappel = Rappel::create($req->all());

            $user = User::find($req->input('idUser'));
            // Enregistrement du log
            Log::create([
                'idUser' => $req->input('idUser'),
                'user_nom' => $user->nom,
                'user_prenom' => $user->prenom,
                'user_pseudo' => $user->pseudo,
                'user_doc' => $user->created_at,
                'action' => 'add',
                'table_concernee' => 'rappels',
                'details' => "Rappel créé : {$rappel->titre} (ID: {$rappel->id})",
                'created_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Rappel créé avec succès',
                'rappel' => $rappel,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création du rappel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Modifier un rappel
    public function updateRappel(Request $req, $id)
    {
        try {
            // Validation des données
            $req->validate([
                'titre' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'date_rappel' => 'nullable|date',
                'type' => 'nullable|string|max:100',
                'priorite' => 'nullable|string|in:basse,moyenne,élevée',
                'statut' => 'nullable|boolean', // Accepte true/false
                'idUser' => 'required|exists:users,id',
            ]);

            // Recherche du rappel
            $rappel = Rappel::findOrFail($id);
            $originalData = $rappel->only(['titre', 'description', 'date_rappel', 'type', 'priorite', 'statut']);

            // Mise à jour du rappel
            $rappel->update($req->all());

            // Vérifier les modifications pour le log
            $details = [];
            foreach ($originalData as $field => $oldValue) {
                $newValue = $rappel->{$field};
                if ($oldValue !== $newValue) {
                    $details[] = "{$field}: '{$oldValue}' -> '{$newValue}'";
                }
            }

            $user = User::find($req->input('idUser'));
            if (!empty($details)) {
                Log::create([
                    'idUser' => $req->input('idUser'),
                    'user_nom' => $user->nom,
                    'user_prenom' => $user->prenom,
                    'user_pseudo' => $user->pseudo,
                    'user_doc' => $user->created_at,
                    'action' => 'update',
                    'table_concernee' => 'rappels',
                    'details' => "Rappel modifié (ID: {$rappel->id}): " . implode(', ', $details),
                    'created_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Rappel mis à jour avec succès',
                'rappel' => $rappel,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rappel introuvable.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du rappel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Supprimer un rappel
    public function deleteRappel(Request $req, $id)
    {
        try {
            $rappel = Rappel::findOrFail($id);
            $userId = $req->input('idUser');

            if (!$userId) {
                return response()->json(['status' => 'Erreur : ID utilisateur invalide.'], 400);
            }

            $statut = "";

            $statut = $rappel->statut === 1 ? "Terminé" : "En cours";
            $rappelInfo = "{$rappel->titre} (ID: {$rappel->id}) Statut: {$statut}";
            $rappel->delete();

            $user = User::find($userId);
            // Log de suppression
            Log::create([
                'idUser' => $userId,
                'user_nom' => $user->nom,
                'user_prenom' => $user->prenom,
                'user_pseudo' => $user->pseudo,
                'user_doc' => $user->created_at,
                'action' => 'delete',
                'table_concernee' => 'rappels',
                'details' => "Rappel supprimé : {$rappelInfo}",
                'created_at' => now(),
            ]);

            return response()->json(['status' => 'Rappel supprimé avec succès'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rappel introuvable.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Liste des rappels
    public function listeRappels()
    {
        // Mettre à jour le statut des rappels si la date est aujourd'hui
        Rappel::whereDate('date_rappel', '=', now()->toDateString())
            ->where('statut', 0)
            ->update(['statut' => 1]);

        $rappels = Rappel::with('user')->get(); // Inclure les données utilisateur
        return response()->json([
            'status' => 'success',
            'rappels' => $rappels,
        ]);
    }

    // Générer des rappels
    public function generateRappels()
    {
        // Mettre à jour le statut des rappels si la date est aujourd'hui
        RappelImp::whereDate('date_rappel', '=', now()->toDateString())
            ->where('statut', 0)
            ->update(['statut' => 1]);

        $rappelActifs = RappelImp::where('statut', 0)
            ->orderBy('created_at', 'desc')
            ->get(); // Inclure les données utilisateur
        try {
            $rappels = [];

            // Rappels pour les paiements
            $etudiantsNonSoldes = Etudiant::whereColumn('montant_paye', '<', 'scolarite')->get();
            foreach ($etudiantsNonSoldes as $etudiant) {
                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $etudiant->id,
                    'model_type' => Etudiant::class,
                    'type' => 'paiement',
                    'statut' => 0,
                    'titre' => "Paiement en attente pour {$etudiant->nom} {$etudiant->prenom} (ID: {$etudiant->id})",
                ], [
                    'description' => "Reste à payer: " . ($etudiant->scolarite - $etudiant->montant_paye) . " FCFA",
                    'date_rappel' => null,
                    'priorite' => 'élevée',
                ]);
            }

            // Rappels pour les examens
            $examens = Programmation::where('date_prog', '>', now())->get();
            foreach ($examens as $examen) {
                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $examen->id,
                    'model_type' => Programmation::class,
                    'type' => 'examen',
                    'statut' => 0,
                    'date_rappel' => $examen->date_prog, // Important ici
                    'titre' => "Examen prévu le {$examen->date_prog}",
                ], [
                    'description' => "Un examen est programmé à cette date. Type: {$examen->type}",
                    'priorite' => 'moyenne',
                ]);
            }

            // Rappels pour les inactifs
            $etudiantsInactifs = Etudiant::where('updated_at', '<', now()->subDays(30))->get();
            foreach ($etudiantsInactifs as $etudiant) {
                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $etudiant->id,
                    'model_type' => Etudiant::class,
                    'type' => 'inactivité',
                    'statut' => 0,
                    'titre' => "Inactivité détectée pour {$etudiant->nom} {$etudiant->prenom} (ID: {$etudiant->id})",
                ], [
                    'description' => "Aucune mise à jour des données depuis plus de 30 jours.",
                    'date_rappel' => null,
                    'priorite' => 'moyenne',
                ]);
            }

            // Rappels pour formation prolongée
            $etudiantsInscritsLongtemps = Etudiant::whereDate('created_at', '<', now()->subMonths(6))->get();
            foreach ($etudiantsInscritsLongtemps as $etudiant) {
                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $etudiant->id,
                    'model_type' => Etudiant::class,
                    'type' => 'formation',
                    'statut' => 0,
                    'titre' => "Formation prolongée pour {$etudiant->nom} {$etudiant->prenom} (ID: {$etudiant->id})",
                ], [
                    'description' => "L'étudiant(e) est inscrit(e) depuis plus de 6 mois sans finaliser sa formation.",
                    'date_rappel' => null,
                    'priorite' => 'élevée',
                ]);
            }

            // Rappels pour résultats non retirés
            $resultatsNonRetires = Resultat::where('statut', 0)->get();
            foreach ($resultatsNonRetires as $resultat) {
                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $resultat->id,
                    'model_type' => Resultat::class,
                    'type' => 'résultat',
                    'statut' => 0,
                    'titre' => "Résultat non retiré pour {$resultat->etudiant->nom} {$resultat->etudiant->prenom}",
                ], [
                    'description' => "L'étudiant(e) doit récupérer son résultat.",
                    'date_rappel' => null,
                    'priorite' => 'moyenne',
                ]);
            }


            return response()->json([
                'status' => 'success',
                'message' => 'Rappels générés avec succès.',
                'rappels' => $rappels,
                'rappelActifs' => $rappelActifs,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la génération des rappels.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Récupérer les 5 derniers rappels importants et les 5 derniers rappels
    public function getRecentRappels()
    {
        try {
            $recentImportantRappels = RappelImp::where('statut', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            $recentRappels = Rappel::where('statut', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'recentImportantRappels' => $recentImportantRappels->count(),
                'recentRappels' => $recentRappels->count(),
                'lastRefresh' => now(), // ajoute l'heure de dernière récupération
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des rappels récents.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
