<?php

namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

use App\Models\Etudiant;
use App\Models\User;
use App\Models\Progression;
use App\Models\Log;
use Illuminate\Http\Request;

class EtudiantController extends Controller
{
    // Ajoute un Etudiant
    public function addEtudiant(Request $req)
    {
        // Validation des données
        $req->validate([
            'idMoniteur' => 'nullable|integer|exists:moniteurs,id',
            'idUser' => 'required|integer|exists:users,id',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'dateNaissance' => 'required|date',
            'lieuNaissance' => 'nullable|string|max:100',
            'commune' => 'required|string|max:100',
            'numTelephone' => 'required|string|max:20',
            'numTelephone2' => 'nullable|string|max:20',
            'nomAutoEc' => 'required|string|max:100',
            'reduction' => 'required|boolean',
            'typePiece' => 'required|string|max:50',
            'numPiece' => 'required|string|max:50',
            'scolarite' => 'required|numeric|min:0',
            'motifInscription' => 'required|string|max:255',
            'categorie' => 'nullable|array', // S'assurer que c'est un tableau
            'categorie.*' => 'in:A,B,C,D,E,AB,BCDE,ABCDE,CDE', // S'assurer que chaque élément est valide
            'montant_paye' => 'required|numeric|min:0' // Nouveau champ

        ]);

        try {
            // Création de l'étudiant
            $etudiant = Etudiant::create([
                'idMoniteur' => $req->input('idMoniteur'),
                'idUser' => $req->input('idUser'),
                'nom' => $req->input('nom'),
                'prenom' => $req->input('prenom'),
                'dateNaissance' => $req->input('dateNaissance'),
                'lieuNaissance' => $req->input('lieuNaissance'),
                'commune' => $req->input('commune'),
                'num_telephone' => $req->input('numTelephone'),
                'num_telephone_2' => $req->input('numTelephone2'),
                'nom_autoEc' => $req->input('nomAutoEc'),
                'reduction' => $req->input('reduction'),
                'type_piece' => $req->input('typePiece'),
                'num_piece' => $req->input('numPiece'),
                'scolarite' => $req->input('scolarite'),
                'montant_paye' => $req->input('montant_paye'), // Nouveau champ
                'motif_inscription' => $req->input('motifInscription'),
                'categorie' => $req->has('categorie') ? implode(',', $req->input('categorie')) : null,
            ]);

            // Initialisation de la progression
            Progression::create([
                'idEtudiant' => $etudiant->id,
                'etape' => 'inscription',
                'created_at' => now(),
            ]);


            $user = User::find($req->input('idUser'));
            // Enregistre un log avec l'action "add"
            Log::create([
                'idUser' => $req->input('idUser'),
                'user_nom' => $user->nom,
                'user_prenom' => $user->prenom,
                'user_pseudo' => $user->pseudo,
                'user_doc' => $user->created_at,
                'action' => 'add',
                'table_concernee' => 'etudiants',
                'details' => "Etudiant ajouté : {$etudiant->nom} {$etudiant->prenom} (ID: {$etudiant->id})",
                'created_at' => now(),
            ]);

            // Retourne une réponse en cas de succès
            return response()->json([
                'status' => 'success',
                'message' => 'Étudiant enregistré avec succès !',
                'etudiant' => $etudiant,
            ], 201);

        } catch (\Exception $e) {
            // Gestion des erreurs
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'enregistrement de l\'étudiant.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // Récuperer tous les etudiants
    function listEtudiant()
    {
        // Retourne tous les etudiants sous forme de collection
        $etudiants = Etudiant::with('progression')->get(); // Récupérer les étudiants avec leur progression
        return response()->json([
            'status' => 'success',
            'etudiants' => $etudiants,
        ], 200);

    }

    // Récuperer un etudiant par son ID
    public function getEtudiant($id)
    {
        try {
            // Recherche de l'étudiant par ID avec sa progression
            $etudiant = Etudiant::with(['progression', 'moniteur'])->find($id);

            if (!$etudiant) {
                return response()->json(['status' => 'Étudiant introuvable'], 404);
            }

            return response()->json([
                'status' => 'success',
                'etudiant' => $etudiant,
                'progression' => $etudiant->progression, // Inclure la progression
                'moniteur' => $etudiant->moniteur, // Inclure le moniteur
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de la récupération de l\'étudiant.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // Supprimer un etudiant par son ID
    public function deleteEtudiant(Request $request, $id)
    {
        try {
            // Recherche de l'étudiant par ID
            $etudiant = Etudiant::find($id);

            if (!$etudiant) {
                return response()->json(['status' => 'Étudiant introuvable'], 404);
            }

            // Validation de l'ID utilisateur
            $userId = $request->input('user_id');
            if (!$userId || !User::find($userId)) {
                return response()->json(['status' => 'Erreur : ID utilisateur invalide.'], 400);
            }

            // Suppression de la progression associée à l'étudiant
            $progression = Progression::where('idEtudiant', $id)->first();
            if ($progression) {
                $progression->delete();
            }

            $etudiantInfo = $etudiant->nom . ' ' . $etudiant->prenom . '(ID: ' . $etudiant->id . ')';

            // Supprime l'étudiant
            $etudiant->delete();

            $user = User::find($userId);
            // Enregistre un log avec l'action "delete"
            Log::create([
                'idUser' => $userId,
                'user_nom' => $user->nom,
                'user_prenom' => $user->prenom,
                'user_pseudo' => $user->pseudo,
                'user_doc' => $user->created_at,
                'action' => 'delete',
                'table_concernee' => 'etudiants',
                'details' => "Étudiant supprimé : {$etudiantInfo}",
                'created_at' => now(),
            ]);

            return response()->json([
                'status' => 'deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la suppression.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mettre à jour un etudiant par son ID
    public function updateEtudiant(Request $req, $id)
    {
        try {
            // Validation des données
            $req->validate([
                'nom' => 'nullable|string|max:100',
                'prenom' => 'nullable|string|max:100',
                'dateNaissance' => 'nullable|date',
                'num_telephone' => 'nullable|string|max:15',
                'num_telephone_2' => 'nullable|string|max:15',
                'progression' => 'nullable|exists:progression,id',
                'etape' => 'nullable|string|max:100',
                'montant_paye' => 'nullable|numeric|min:0',
                'user_id' => 'required|exists:users,id',
                'moniteur_id' => 'nullable|exists:moniteurs,id',
            ]);

            // Récupération de l'étudiant
            $etudiant = Etudiant::findOrFail($id);
            $originalData = $etudiant->only(['nom', 'prenom', 'dateNaissance', 'num_telephone', 'num_telephone_2', 'montant_paye', 'idMoniteur']);
            $details = [];

            $fieldDescriptions = [
                'nom' => "Nom modifié",
                'prenom' => "Prénom modifié",
                'dateNaissance' => "Date de naissance modifiée",
                'num_telephone' => "Numéro de téléphone principal modifié",
                'num_telephone_2' => "Numéro de téléphone secondaire modifié",
                'montant_paye' => "Montant payé complété",
            ];

            $updateData = $req->only(['nom', 'prenom', 'dateNaissance', 'num_telephone', 'num_telephone_2']);


            // Gestion du montant payé
            if ($req->has('montant_paye')) {
                $nouveauMontantPaye = $etudiant->montant_paye + $req->input('montant_paye');
                if ($nouveauMontantPaye > $etudiant->scolarite) {
                    return response()->json(['status' => 'error', 'message' => 'Le montant payé dépasse la scolarité totale.'], 400);
                }
                // $etudiant->montant_paye = $nouveauMontantPaye;
                $updateData['montant_paye'] = $nouveauMontantPaye;
            }

            // Gestion de la progression
            if ($req->has('progression') && $req->has('etape')) {
                $progression = Progression::find($req->input('progression'));
                if ($progression && $progression->idEtudiant == $etudiant->id) {
                    $progression->update(['etape' => $req->input('etape')]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Progression invalide ou non associée à cet étudiant.'], 400);
                }
            }

            // Gestion du moniteur
            if ($req->filled('moniteur_id') && $etudiant->idMoniteur != $req->input('moniteur_id')) {
                $details[] = "Moniteur modifié";
                $etudiant->idMoniteur = $req->input('moniteur_id');
            }

            // Mise à jour des autres champs
            // $etudiant->update($req->only(['nom', 'prenom', 'dateNaissance', 'num_telephone', 'num_telephone_2', 'montant_paye']));
            $etudiant->update($updateData);

            // Comparaison et log des modifications
            foreach ($originalData as $field => $oldValue) {
                $newValue = $etudiant->{$field};
                if ($oldValue !== $newValue && isset($fieldDescriptions[$field])) {
                    $details[] = "{$fieldDescriptions[$field]} : '{$oldValue}' -> '{$newValue}'";
                }
            }

            $user = User::find($req->input('user_id'));
            // Enregistrement du log si des modifications ont été effectuées
            if (!empty($details)) {
                Log::create([
                    'idUser' => $req->input('user_id'),
                    'user_nom' => $user->nom,
                    'user_prenom' => $user->prenom,
                    'user_pseudo' => $user->pseudo,
                    'user_doc' => $user->created_at,
                    'action' => 'update',
                    'table_concernee' => 'etudiants',
                    'details' => "Modification pour {$etudiant->nom} {$etudiant->prenom} (ID: {$etudiant->id}) " . implode(', ', $details),
                    'created_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Étudiant mis à jour avec succès.',
                'etudiant' => $etudiant
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Étudiant introuvable.'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Une erreur est survenue.', 'error' => $e->getMessage()], 500);
        }
    }


    // Récupérer les 10 derniers étudiants enregistrés
    public function getLastTenEtudiants()
    {
        try {
            $etudiants = Etudiant::orderBy('created_at', 'desc')->take(10)->get();
            return response()->json([
                'status' => 'success',
                'etudiants' => $etudiants,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des étudiants.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
