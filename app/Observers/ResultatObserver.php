<?php
namespace App\Observers;

use App\Models\Resultat;
use App\Models\RappelImp;

class ResultatObserver
{
    public function updated(Resultat $resultat)
    {
        // 🔹 Fermer le rappel quand un étudiant récupère son résultat
        if ($resultat->statut == 1) {
            RappelImp::where('model_id', $resultat->id)
                ->where('model_type', Resultat::class)

                ->where('type', 'résultat')
                ->where('statut', 0)
                ->update(['statut' => 1]);
        }
    }

    public function deleted(Resultat $resultat)
    {
        // 🔹 Fermer le rappel quand un étudiant supprime son résultat
        RappelImp::where('model_id', $resultat->id)
            ->where('model_type', Resultat::class)

            ->where('type', 'résultat')
            ->where('statut', 0)
            ->update(['statut' => 1]);
    }
}
