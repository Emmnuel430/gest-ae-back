<?php
namespace App\Observers;

use App\Models\RappelImp;
use App\Models\Programmation;

class ProgrammationObserver
{

    // 🟢 Surveiller l’ajout d’un examen
    public function created(Programmation $programmation)
    {
        // 4️⃣ Quand la date d’un examen est passée, on clôture le rappel
        RappelImp::where('model_id', $programmation->id)
            ->where('model_type', Programmation::class)

            ->where('type', 'examen')
            ->whereDate('date_rappel', '>=', value: now()->toDateString())
            ->where('statut', 0)
            ->update(['statut' => 1]);
    }

    // 🟢 Surveiller la suppression d’un examen
    public function deleted(Programmation $programmation)
    {
        // 4️⃣ Quand la date d’un examen est passée, on clôture le rappel
        RappelImp::where('model_id', $programmation->id)
            ->where('model_type', Programmation::class)

            ->where('type', 'examen')
            ->whereDate('date_rappel', operator: '<=', value: now()->toDateString())
            ->where('statut', 0)
            ->update(['statut' => 1]);
    }

}
