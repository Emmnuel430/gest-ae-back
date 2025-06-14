<?php

namespace App\Observers;

use App\Models\Etudiant;
use App\Models\Moniteur;
use App\Models\RappelImp;
use Illuminate\Support\Facades\Log;

class MoniteurObserver
{
    public function deleting(Moniteur $moniteur)
    {
        $etapesCritiques = ['cours_de_code', 'cours_de_conduite'];

        $etudiantsAffectes = Etudiant::where('idMoniteur', '=', $moniteur->id)->with('progression')->get();

        Log::info("Moniteur supprimé : {$moniteur->id}. Étudiants affectés : " . $etudiantsAffectes->count());

        foreach ($etudiantsAffectes as $etudiant) {
            $progression = $etudiant->progression;

            if ($progression) {
                Log::info("Étudiant : {$etudiant->id}, étape : {$progression->etape}");

                if (in_array($progression->etape, $etapesCritiques)) {
                    RappelImp::updateOrCreate([
                        'model_id' => $etudiant->id,
                        'model_type' => Etudiant::class,
                        'type' => 'affectation',
                        'statut' => 0,
                        'titre' => "Moniteur(trice) manquant(e) pour {$etudiant->nom} {$etudiant->prenom}",
                    ], [
                        'description' => "L'étudiant est à l'étape « {$progression->etape} » mais n’a plus de moniteur.",
                        'priorite' => 'élevée',
                    ]);
                }
            } else {
                Log::warning("Aucune progression trouvée pour l'étudiant {$etudiant->id}");
            }
        }
    }
}
