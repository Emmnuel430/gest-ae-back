<?php

namespace App\Observers;

use App\Models\Etudiant;
use App\Models\RappelImp;

class EtudiantObserver
{
    // 🟢 Surveiller la mise à jour d'un étudiant
    public function updated(Etudiant $etudiant)
    {
        // 🔹 Vérifier si l'étudiant atteint l'examen de conduite
        if ($etudiant->progression && $etudiant->progression->etape === 'examen_de_conduite') {
            RappelImp::where('model_id', $etudiant->id)
                ->where('model_type', Etudiant::class)

                ->where('type', 'formation')
                ->where('statut', 0)
                ->update(['statut' => 1]);
        }

        // 🔹 Si l'étudiant a un moniteur, clôturer le rappel d'affectation
        if ($etudiant->idMoniteur) {
            RappelImp::where('model_id', $etudiant->id)
                ->where('model_type', Etudiant::class)

                ->where('type', 'affectation')
                ->where('statut', 0)
                ->update(['statut' => 1]);
        }

        // 🔹 Si l’étudiant paie tout, clôturer le rappel de paiement
        if ($etudiant->montant_paye == $etudiant->scolarite) {
            RappelImp::where('model_id', $etudiant->id)
                ->where('model_type', Etudiant::class)

                ->where('type', 'paiement')
                ->where('statut', 0)
                ->update(['statut' => 1]);
        }

        // 🔹 Si l'étudiant met à jour son profil, il n'est plus inactif
        if ($etudiant->updated_at >= now()->subDays(30)) {
            RappelImp::where('model_id', $etudiant->id)
                ->where('model_type', Etudiant::class)

                ->where('type', 'inactivité')
                ->where('statut', 0)
                ->update(['statut' => 1]);
        }
    }

    public function deleted(Etudiant $etudiant)
    {
        // 🔹 Fermer tous les rappels associés à l'étudiant supprimé
        RappelImp::where('model_id', $etudiant->id)
            ->where('model_type', Etudiant::class)

            ->where('statut', 0)
            ->update(['statut' => 1]);
    }
}
