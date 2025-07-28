<?php

namespace App\Http\Controllers;

use App\Models\RappelImp;
use App\Models\Etudiant;
use App\Models\Resultat;
use App\Models\Programmation;
class RappelImpController extends Controller
{
    // Générer des rappels
    public function generateRappels()
    {
        try {
            $shortPrenom = fn($prenom) => explode(' ', trim($prenom))[0];
            $rappels = [];

            // 🔹 Paiement en attente
            $etudiantsNonSoldes = Etudiant::whereColumn('montant_paye', '<', 'scolarite')->get();
            foreach ($etudiantsNonSoldes as $etudiant) {
                $titre = "{$etudiant->nom} {$shortPrenom($etudiant->prenom)} – Paiement en attente";
                $description = "Reste à payer : " . ($etudiant->scolarite - $etudiant->montant_paye) . " FCFA";

                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $etudiant->id,
                    'model_type' => Etudiant::class,
                    'type' => 'paiement',
                    'statut' => 0,
                    'titre' => $titre,
                ], [
                    'description' => $description,
                    'date_rappel' => null,
                    'priorite' => 'élevée',
                ]);
            }

            // 🔹 Examens à venir
            $examens = Programmation::where('date_prog', '>', now())->get();
            foreach ($examens as $examen) {
                $titre = "Examen prévu le " . date('d/m/Y', strtotime($examen->date_prog));
                $description = "Un examen est programmé à cette date. Type : {$examen->type}";

                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $examen->id,
                    'model_type' => Programmation::class,
                    'type' => 'examen',
                    'statut' => 0,
                    'date_rappel' => $examen->date_prog,
                    'titre' => $titre,
                ], [
                    'description' => $description,
                    'priorite' => 'moyenne',
                ]);
            }

            // 🔹 Inactivité
            $etudiantsInactifs = Etudiant::where('updated_at', '<', now()->subDays(30))->get();
            foreach ($etudiantsInactifs as $etudiant) {
                $titre = "{$etudiant->nom} {$shortPrenom($etudiant->prenom)} – Inactivité";
                $description = "Aucune mise à jour des données depuis plus de 30 jours.";

                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $etudiant->id,
                    'model_type' => Etudiant::class,
                    'type' => 'inactivité',
                    'statut' => 0,
                    'titre' => $titre,
                ], [
                    'description' => $description,
                    'date_rappel' => null,
                    'priorite' => 'moyenne',
                ]);
            }

            // 🔹 Formation prolongée
            $etudiantsInscritsLongtemps = Etudiant::whereDate('created_at', '<', now()->subMonths(6))->get();
            foreach ($etudiantsInscritsLongtemps as $etudiant) {
                $titre = "{$etudiant->nom} {$shortPrenom($etudiant->prenom)} – Formation prolongée";
                $description = "Inscrit(e) depuis plus de 6 mois sans finaliser la formation.";

                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $etudiant->id,
                    'model_type' => Etudiant::class,
                    'type' => 'formation',
                    'statut' => 0,
                    'titre' => $titre,
                ], [
                    'description' => $description,
                    'date_rappel' => null,
                    'priorite' => 'élevée',
                ]);
            }

            // 🔹 Résultats non retirés
            $resultatsNonRetires = Resultat::where('statut', 0)->with('etudiant')->get();
            foreach ($resultatsNonRetires as $resultat) {
                $etudiant = $resultat->etudiant;
                $titre = "{$etudiant->nom} {$shortPrenom($etudiant->prenom)} – Résultat non retiré";
                $description = "L'étudiant(e) doit récupérer son résultat.";

                $rappels[] = RappelImp::updateOrCreate([
                    'model_id' => $resultat->id,
                    'model_type' => Resultat::class,
                    'type' => 'résultat',
                    'statut' => 0,
                    'titre' => $titre,
                ], [
                    'description' => $description,
                    'date_rappel' => null,
                    'priorite' => 'moyenne',
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Rappels générés avec succès.',
                'rappels' => $rappels,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la génération des rappels.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function closeOldRappels()
    {
        $nb = RappelImp::whereDate('date_rappel', '<=', now()->toDateString())
            ->where('statut', 0)
            ->update(['statut' => 1]);

        \Log::info("[$nb] rappels clôturés automatiquement.");

        return $nb;
    }

    public function listeRappelsImp()
    {
        // Mettre à jour le statut des rappels si la date est aujourd'hui
        RappelImp::whereDate('date_rappel', '=', now()->toDateString())
            ->where('statut', 0)
            ->update(['statut' => 1]);

        $rappels = RappelImp::with('user')
            ->where('statut', 1)
            ->get(); // Inclure les données utilisateur
        return response()->json([
            'status' => 'success',
            'rappels' => $rappels,
        ]);
    }

}
