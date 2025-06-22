<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; // Importation du contrôleur UserController 
use App\Http\Controllers\MoniteurController; // Importation du contrôleur MoniteurController 
use App\Http\Controllers\LogController; // Importation du contrôleur LogController 
use App\Http\Controllers\EtudiantController; // Importation du contrôleur EtudiantController 
use App\Http\Controllers\ResultatController; // Importation du contrôleur ResultatController 
use App\Http\Controllers\ProgrammationController; // Importation du contrôleur ProgrammationController
use App\Http\Controllers\GlobalController; // Importation du contrôleur GlobalController
use App\Http\Controllers\RappelController; // Importation du contrôleur RappelController

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json(['user' => $request->user()]);
    ;
});

// Définit une route POST pour l'endpoint '/login'.
// Lorsque cette route est appelée, elle exécute la fonction 'login' du UserController.
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Routes protégées par l'authentification Sanctum



    // -----------------------------------------------
    // -------------   Users   ----------------------
    // -----------------------------------------------
    // Définit une route POST pour l'endpoint '/register'.
    // Lorsque cette route est appelée, elle exécute la fonction 'register' du UserController.
    Route::post('add_user', [UserController::class, 'addUser']);


    // Définit une route POST pour l'endpoint '/logout'.
    // Lorsque cette route est appelée, elle exécute la fonction suivante.
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès']);
    })->middleware('auth:sanctum');


    // Définit une route GET pour l'endpoint '/list'.
    // Lorsque cette route est appelée, elle exécute la fonction 'list' du UserController.
    Route::get('liste_user', [UserController::class, 'listeUser']);

    // Définit une route DELETE pour l'endpoint '/delete_user à qui est passé l'id de l'user'.
    // Lorsque cette route est appelée, elle exécute la fonction 'delete_user' du UserController.
    Route::delete('delete_user/{id}', [UserController::class, 'deleteUser']);

    // Définit une route GET pour l'endpoint '/users à qui est passé l'id de l'user '.
    // Lorsque cette route est appelée, elle exécute la fonction 'getuser' du UserController.
    Route::get('user/{id}', [UserController::class, 'getUser']);

    // Définit une route POST pour l'endpoint '/update_user à qui est passé l'id de l'user '.
    // Lorsque cette route est appelée, elle exécute la fonction 'updateUser' du UserController.
    Route::post('update_user/{id}', [UserController::class, 'updateUser']);


    // -----------------------------------------------
    // ---------------   Etudiants    -----------------
    // -----------------------------------------------
    // Définit une route POST pour l'endpoint '/addetudiant'.
    // Lorsque cette route est appelée, elle exécute la fonction 'addEtudiant' du EtudiantController.
    Route::post('add_etudiant', [EtudiantController::class, 'addEtudiant']);

    // Définit une route GET pour l'endpoint '/listetudiant'.
    // Lorsque cette route est appelée, elle exécute la fonction 'listEtudiant' du EtudiantController.
    Route::get('liste_etudiant', [EtudiantController::class, 'listEtudiant']);

    // Définit une route GET pour l'endpoint '/etudiant à qui est passé l'id de l'etudiant'.
    // Lorsque cette route est appelée, elle exécute la fonction 'getEtudiant' du EtudiantController.
    Route::get('etudiant/{id}', [EtudiantController::class, 'getEtudiant']);

    // Définit une route DELETE pour l'endpoint '/delete_etudiant à qui est passé l'id de l'etudiant'.
    // Lorsque cette route est appelée, elle exécute la fonction 'delete_etudiant' du EtudiantController.
    Route::delete('delete_etudiant/{id}', [EtudiantController::class, 'deleteEtudiant']);

    // Définit une route POST pour l'endpoint '/update_etudiant à qui est passé l'id de l'etudiant'.
    // Lorsque cette route est appelée, elle exécute la fonction 'updateEtudiant' du EtudiantController.
    Route::post('update_etudiant/{id}', [EtudiantController::class, 'updateEtudiant']);

    // Définit une route GET pour l'endpoint '/latest_etudiant'.
    // Lorsque cette route est appelée, elle exécute la fonction 'getLastTenEtudiants' du EtudiantController.
    Route::get('latest_etudiant', [EtudiantController::class, 'getLastTenEtudiants']);


    // -----------------------------------------------
    // ---------------   Moniteurs   --------------------
    // -----------------------------------------------
    // Définit une route POST pour l'endpoint '/addmoniteur'.
    // Lorsque cette route est appelée, elle exécute la fonction 'addmoniteur' du MoniteurController.
    Route::post('add_moniteur', [MoniteurController::class, 'addMoniteur']);

    // Définit une route GET pour l'endpoint '/listmoniteur'.
    // Lorsque cette route est appelée, elle exécute la fonction 'listmoniteur' du Moniteur Controller.
    Route::get('liste_moniteur', [MoniteurController::class, 'listeMoniteur']);

    // Définit une route DELETE pour l'endpoint '/delete_moniteur à qui est passé l'id du moniteur '.
    // Lorsque cette route est appelée, elle exécute la fonction 'delete_moniteur' du MoniteurController.
    Route::delete('delete_moniteur/{id}', [MoniteurController::class, 'deleteMoniteur']);

    // Définit une route GET pour l'endpoint '/moniteur à qui est passé l'id du moniteur'.
    // Lorsque cette route est appelée, elle exécute la fonction 'getMoniteur' du MoniteurController.
    Route::get('moniteur/{id}', [MoniteurController::class, 'getMoniteur']);

    // Définit une route POST pour l'endpoint '/update_moniteur à qui est passé l'id du moniteur'.
    // Lorsque cette route est appelée, elle exécute la fonction 'updatemoniteur' du MoniteurController.
    Route::post('update_moniteur/{id}', [MoniteurController::class, 'updateMoniteur']);


    // ---------------------------------------------------------------
    // --------------------   Logs   --------------------------
    // ---------------------------------------------------------------
    // Définit une route GET pour l'endpoint '/logs'.
    // Lorsque cette route est appelée, elle retourne les logs associés aux utilisateurs
    Route::get('/logs', [LogController::class, 'index']);

    // Définit une route GET pour l'endpoint '/latest_logs'.
    // Lorsque cette route est appelée, elle retourne les logs associés aux utilisateurs
    Route::get('/latest_logs', [LogController::class, 'latestLogs']);


    // -----------------------------------------------
    // ----------------   Resultats   ---------------
    // -----------------------------------------------
    // Définit une route POST pour l'endpoint '/addresultat'.
    // Lorsque cette route est appelée, elle exécute la fonction 'add' du ResultatController.
    Route::post('add_resultat', [ResultatController::class, 'addResultat']);

    // Définit une route GET pour l'endpoint '/resultats'.
    // Lorsque cette route est appelée, elle exécute la fonction 'list' du ResultatController.
    Route::get('resultats', [ResultatController::class, 'listeResultat']);

    // Définit une route GET pour l'endpoint '/latest_resultat'.
    // Lorsque cette route est appelée, elle exécute la fonction 'getLastFiveResultats' du ResultatController.
    Route::get('latest_resultat', [ResultatController::class, 'getLastFiveResultats']);

    // Définit une route DELETE pour l'endpoint '/delete_resultat à qui est passé l'id du resultat'.
    // Lorsque cette route est appelée, elle exécute la fonction 'destroy' du ResultatController.
    Route::delete('delete_resultat/{id}', [ResultatController::class, 'deleteResultat']);
    /* 
    // Définit une route GET pour l'endpoint '/showresultat à qui est passé l'id du resultat'.
    // Lorsque cette route est appelée, elle exécute la fonction 'show' du ResultatController.
    Route::get('resultat/{id}', [ResultatController::class, 'getResultat']); */

    // Définit une route PUT pour l'endpoint '/update_resultat à qui est passé l'id du resultat'.
    // Lorsque cette route est appelée, elle exécute la fonction 'update' du ResultatController.
    Route::put('update_resultat/{id}', [ResultatController::class, 'updateResultat']);


    // -----------------------------------------------
    // --------------   Programmations  --------------
    // -----------------------------------------------
    // Définit une route GET pour l'endpoint '/programmations'.
    // Lorsque cette route est appelée, elle exécute la fonction 'index' du ProgrammationController.
    Route::get('/programmations', [ProgrammationController::class, 'listeProg']);
    // Définit une route GET pour l'endpoint '/addprogrammation'.
    // Lorsque cette route est appelée, elle exécute la fonction 'store' du ProgrammationController.
    Route::post('/add_programmations', [ProgrammationController::class, 'addProg']);

    Route::delete('/programmations/{id}', [ProgrammationController::class, 'deleteProg']);



    // -----------------------------------------------
    // -----------------   GLobaux  ------------------
    // -----------------------------------------------
    // Définit une route GET pour l'endpoint '/global/evolution-inscriptions'.
    // Lorsque cette route est appelée, elle exécute la fonction 'evolutionInscriptions' du GlobalController.
    Route::get('/global/evolution-inscriptions', [GlobalController::class, 'evolutionInscriptions']);

    // Définit une route GET pour l'endpoint '/global/repartition-categorie'.
    // Lorsque cette route est appelée, elle exécute la fonction 'repartitionParCategorie' du GlobalController.
    Route::get('/global/repartition-categorie', [GlobalController::class, 'repartitionParCategorie']);

    // Définit une route GET pour l'endpoint '/global/repartition-moniteur'.
    // Lorsque cette route est appelée, elle exécute la fonction 'repartitionParMoniteur' du GlobalController.
    Route::get('/global/repartition-moniteur', [GlobalController::class, 'repartitionParMoniteur']);

    // Définit une route GET pour l'endpoint '/global/etudiants-par-etape'.
    // Lorsque cette route est appelée, elle exécute la fonction 'etudiantsParEtape' du GlobalController.
    Route::get('/global/etudiants-par-etape', [GlobalController::class, 'etudiantsParEtape']);

    // Définit une route GET pour l'endpoint '/global/totaux'.
    // Lorsque cette route est appelée, elle exécute la fonction 'totaux' du GlobalController.
    Route::get('/global/totaux', [GlobalController::class, 'totaux']);

    // Définit une route GET pour l'endpoint '/global/repartition-reduction'.
    // Lorsque cette route est appelée, elle exécute la fonction 'repartitionParReduction' du GlobalController.
    Route::get('/global/repartition-reduction', [GlobalController::class, 'repartitionParReduction']);


    // -----------------------------------------------
    // ----------------   Rappels   ------------------
    // -----------------------------------------------
    // Définit une route POST pour l'endpoint '/add_rappel'.
    // Lorsque cette route est appelée, elle exécute la fonction 'addRappel' du RappelController.
    Route::post('add_rappel', [RappelController::class, 'addRappel']);

    // Définit une route POST pour l'endpoint '/update_rappel/{id}'.
    // Lorsque cette route est appelée, elle exécute la fonction 'updateRappel' du RappelController.
    Route::post('update_rappel/{id}', [RappelController::class, 'updateRappel']);

    // Définit une route DELETE pour l'endpoint '/delete_rappel/{id}'.
    // Lorsque cette route est appelée, elle exécute la fonction 'deleteRappel' du RappelController.
    Route::delete('delete_rappel/{id}', [RappelController::class, 'deleteRappel']);

    // Définit une route GET pour l'endpoint '/liste_rappels'.
    // Lorsque cette route est appelée, elle exécute la fonction 'listeRappels' du RappelController.
    Route::get('liste_rappels', [RappelController::class, 'listeRappels']);

    // Définit une route GET pour l'endpoint '/historique_rappels'.
    // Lorsque cette route est appelée, elle exécute la fonction 'historiqueRappels' du RappelController.
    Route::get('historique_rappels', [RappelController::class, 'historiqueRappels']);

    // Définit une route GET pour l'endpoint '/generate_rappels'.
    // Lorsque cette route est appelée, elle exécute la fonction 'generateRappels' du RappelController.
    Route::get('generate_rappels', [RappelController::class, 'generateRappels']);

    // Définit une route GET pour l'endpoint '/rappels_recents'.
    // Lorsque cette route est appelée, elle exécute la fonction 'getRecentRappels' du RappelController.
    Route::get('rappels_recents', [RappelController::class, 'getRecentRappels']);

});
// -----------------------------------------------
// -----------------   Test   --------------------
// -----------------------------------------------
// Définit une route GET pour l'endpoint '/test'.
// Lorsque cette route est appelée, elle retourne un message JSON indiquant que l'API est en ligne.
// Cette route est utilisée pour vérifier si l'API fonctionne correctement.
Route::get('/test', function () {
    return response()->json(['message' => 'API en ligne 🎉']);
});