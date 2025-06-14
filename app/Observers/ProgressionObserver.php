<?php

namespace App\Observers;

use App\Models\Progression;
use App\Models\RappelImp;

class ProgressionObserver
{
    /**
     * Surveiller l'évolution de la progression
     *
     * @param \App\Models\Progression $progression
     * @return void
     */
    public function updated(Progression $progression)
    {
        // Si l'étudiant atteint l'examen de conduite, clôturer le rappel de formation prolongée
        if ($progression->etape === 'programmé_pour_la_conduite') {
            RappelImp::where('model_id', $progression->id)
                ->where('model_type', Progression::class)

                ->where('type', 'formation')
                ->where('statut', 0)
                ->update(['statut' => 1]);
        }
    }
}
