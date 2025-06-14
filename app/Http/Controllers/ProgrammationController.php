<?php

namespace App\Http\Controllers;

use App\Models\Programmation;
use \App\Models\Progression;
use \App\Models\Etudiant;
use App\Models\Log;
use App\Models\RappelImp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


class ProgrammationController extends Controller
{
    // Méthode pour lister les programmations
    public function listeProg()
    {
        // Récupère toutes les programmations avec les informations de l'utilisateur associé
        $programmations = Programmation::with('user')->get();

        // Retourne une réponse JSON avec le statut et les programmations
        return response()->json([
            'status' => 'success',
            'programmations' => $programmations
        ]);
    }

    public function addProg(Request $request)
    {
        $validated = $request->validate([
            'date_prog' => 'required|date',
            'type' => 'required|in:code,conduite',
            'idUser' => 'required|exists:users,id',
            'etudiants' => 'required|array',
            'etudiants.*' => 'exists:etudiant,id',
        ]);

        DB::beginTransaction();

        try {
            $etudiants = Etudiant::whereIn('id', $validated['etudiants'])->get();

            // Crée une programmation en base
            $programmation = Programmation::create([
                'date_prog' => $validated['date_prog'],
                'type' => $validated['type'],
                'idUser' => $validated['idUser'],
            ]);

            // Nom du fichier
            $filename = "Prog-{$validated['type']}-{$validated['date_prog']}.pdf";
            $filename = str_replace(' ', '_', $filename);

            // Génère le PDF avec les données
            $pdf = Pdf::loadView('pdfs.programmation', [
                'programmation' => $programmation,
                'etudiants' => $etudiants
            ])
                ->setPaper('a4', 'landscape');
            ;

            $pdfPath = "programmations/{$filename}";

            // Enregistre le PDF dans storage/app/public/programmations
            Storage::disk('public')->put($pdfPath, $pdf->output());

            // Met à jour la programmation avec le chemin du PDF
            $programmation->update([
                'fichier_pdf' => $pdfPath,
            ]);

            // Mise à jour des étapes des étudiants
            $nouvelleEtape = $validated['type'] === 'code'
                ? 'programmé_pour_le_code'
                : 'programmé_pour_la_conduite';

            foreach ($etudiants as $etudiant) {
                $progression = Progression::where('idEtudiant', $etudiant->id)->first();
                if ($progression) {
                    $progression->update(['etape' => $nouvelleEtape]);
                }
            }

            // Log
            $user = User::find($validated['idUser']);
            Log::create([
                'idUser' => $validated['idUser'],
                'user_nom' => $user->nom,
                'user_prenom' => $user->prenom,
                'user_pseudo' => $user->pseudo,
                'user_doc' => $user->created_at,
                'action' => 'add',
                'table_concernee' => 'programmations',
                'details' => "Programmation ajoutée : {$validated['date_prog']} ({$validated['type']}).",
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Programmation ajoutée avec succès.',
                'programmation' => $programmation,
                'file_url' => asset("storage/{$pdfPath}"),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de l’ajout de la programmation', ['exception' => $e]);
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la génération ou de l’enregistrement : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteProg(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $programmation = Programmation::findOrFail($id);

            // Supprimer le fichier PDF s'il existe
            if ($programmation->fichier_pdf && Storage::disk('public')->exists($programmation->fichier_pdf)) {
                Storage::disk('public')->delete($programmation->fichier_pdf);
            }

            // Supprimer tous les rappels liés à cette programmation
            RappelImp::where('model_id', $programmation->id)
                ->where('model_type', Programmation::class)
                ->delete();

            // Supprimer la programmation
            $programmation->delete();

            $userId = $request->query('idUser');
            $user = User::find($userId);
            // Log (optionnel)
            Log::create([
                'idUser' => $userId,
                'user_nom' => $user->nom,
                'user_prenom' => $user->prenom,
                'user_pseudo' => $user->pseudo,
                'user_doc' => $user->created_at,
                'action' => 'delete',
                'table_concernee' => 'programmations',
                'details' => "Programmation supprimée : ID {$id}",
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Programmation et fichier supprimés avec succès.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ], 500);
        }
    }

}
