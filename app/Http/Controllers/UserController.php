<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Fournit des outils pour le hachage sécurisé des mots de passe.
use App\Models\User; // Le modèle User, qui représente la table `users` dans la base de données.
use App\Models\Log; // Le modèle Log, qui représente la table `logs` dans la base de données.

class UserController extends Controller
{
    // Méthode pour enregistrer un nouvel utilisateur dans la base de données.
    function addUser(Request $req)
    {
        // Création d'une nouvelle instance du modèle User.
        $user = new User;

        // Assigner les champs à partir de la requête.
        $user->nom = $req->input('nom');
        $user->prenom = $req->input('prenom');
        $user->pseudo = $req->input('pseudo');

        // Vérifie que le pseudo est unique avant de continuer.
        if (User::where('pseudo', $req->input('pseudo'))->exists()) {
            return response()->json(['error' => 'Le pseudo est déjà utilisé.'], 400);
        }

        // Hache le mot de passe pour le stocker de manière sécurisée.
        $user->password = Hash::make($req->input('password'));

        // Définir le rôle de l'utilisateur (0 par défaut, ou la valeur fournie).
        $user->role = $req->input('role', 0);

        // Sauvegarde les données de l'utilisateur dans la table `users` de la base de données.
        $user->save();

        // Enregistrement du log
        $adminId = $req->input('admin_id'); // L'ID de l'administrateur effectuant l'ajout
        $userId = User::find($adminId);
        Log::create([
            'idUser' => $adminId, // L'utilisateur qui a ajouté
            'user_nom' => $userId->nom,
            'user_prenom' => $userId->prenom,
            'user_pseudo' => $userId->pseudo,
            'user_doc' => $userId->created_at,
            'action' => 'create',
            'table_concernee' => 'users',
            'details' => "Nouvel utilisateur ajouté : {$user->nom} {$user->prenom} (ID: {$user->id}, " . ($user->role == 1 ? 'Admin' : 'Staff') . ")",
            'created_at' => now(),
        ]);

        // Retourne les données de l'utilisateur nouvellement créé (hors mot de passe) en tant que réponse HTTP.
        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
        ], 201);
    }

    // Méthode pour connexion
    function login(Request $req)
    {
        try {
            $user = User::where('pseudo', $req->pseudo)->first();

            if (!$user || !Hash::check($req->password, $user->password)) {
                return response()->json(['error' => 'Pseudo ou mot de passe incorrect'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur login: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur serveur'], 500);
        }

    }

    // Récuperer tous les Users
    function listeUser()
    {
        // Retourne tous les produits sous forme de collection
        $users = User::all();
        // Vérifie si la collection est vide
        if ($users->isEmpty()) {
            return response()->json(['status' => 'Aucun utilisateur trouvé'], 404);
        }
        // Retourne la collection d'utilisateurs
        return response()->json([
            'status' => 'success',
            'users' => $users,
        ], 200);
    }

    // Fonction pour supprimer un user par son ID
    public function deleteUser(Request $request, $id)
    {
        try {
            // Validation de l'ID utilisateur
            $userId = $request->query('user_id'); // Récupère l'ID utilisateur depuis les paramètres de requête
            if (!$userId || !User::find($userId)) {
                return response()->json(['status' => 'Erreur : ID utilisateur invalide.'], 400);
            }

            // Recherche de l'utilisateur à supprimer
            $user = User::find($id);
            if (!$user) {
                return response()->json(['status' => 'Utilisateur introuvable'], 404);
            }

            // Sauvegarde du nom complet pour le log
            $userName = "{$user->nom} {$user->prenom}";

            // Suppression de l'utilisateur
            $user->delete();

            $user_id = User::find($userId);
            // Enregistrement du log
            Log::create([
                'idUser' => $userId,
                'user_nom' => $user_id->nom,
                'user_prenom' => $user_id->prenom,
                'user_pseudo' => $user_id->pseudo,
                'user_doc' => $user_id->created_at,
                'action' => 'delete',
                'table_concernee' => 'users',
                'details' => "Utilisateur supprimé : {$userName} (ID: {$id})",
                'created_at' => now(),
            ]);

            // Statut de suppression
            return response()->json(['status' => 'deleted'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la suppression.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // M-à-j les données d'un user
    public function updateUser(Request $req, $id)
    {
        // Données réquises
        $req->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'pseudo' => 'required|string|max:255|unique:users,pseudo,' . $id,
            'password' => 'nullable|string',
        ]);

        // Retourne l'user correspondant à l'ID donné
        $user = User::find($id);

        // Message d'erreur si l'user n'esxiste pas
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
        }

        // Recuperation de l'ID de l'user authentifié
        $userId = $req->input('user_id');
        if (!$userId || !User::find($userId)) {
            return response()->json(['error' => 'Utilisateur authentifié invalide.'], 400);
        }

        // Recuperation des anicennes valeurs pour le log
        $oldData = $user->toArray();

        // Mettre à jour les champs
        $user->nom = $req->input('nom', $user->nom);
        $user->prenom = $req->input('prenom', $user->prenom);
        $user->pseudo = $req->input('pseudo', $user->pseudo);

        // Verifié si le nouveau pseudo n'esxiste pas deja
        if (
            User::where('pseudo', $req->input('pseudo'))
                ->where('id', '!=', $id)
                ->exists()
        ) {
            return response()->json(['error' => 'Le pseudo est déjà utilisé par un autre utilisateur.'], 400);
        }

        // Role non modifiable
        if ($req->has('role') && $req->input('role') != $user->role) {
            return response()->json(['error' => 'Modification du rôle non autorisée.'], 403);
        }

        // Enregistrer le nouveau mot de passe si fourni
        $passwordChanged = false;
        if ($req->has('password') && !empty($req->input('password'))) {
            $user->password = Hash::make($req->input('password'));
            $passwordChanged = true;
        }

        $user->save();
        $fieldsToIgnore = ['updated_at', 'created_at'];

        // Construire les détails des modifications
        $newData = $user->toArray();
        $modifications = [];
        foreach ($newData as $key => $value) {
            if (in_array($key, $fieldsToIgnore))
                continue;
            if (array_key_exists($key, $oldData) && $oldData[$key] != $value) {
                // Traduction du champ
                switch ($key) {
                    case 'prenom':
                        $modifications[] = "Prénom modifié";
                        break;
                    case 'nom':
                        $modifications[] = "Nom modifié";
                        break;
                    case 'pseudo':
                        $modifications[] = "Pseudo modifié";
                        break;
                    // Ajoute d'autres cas si besoin
                    default:
                        $modifications[] = ucfirst($key) . " modifié";
                }
            }
        }

        if ($passwordChanged) {
            $modifications[] = "Mot de passe modifié";
        }

        // Enregistrer le log
        if (count($modifications) > 0) {
            $user_id = User::find($userId);
            Log::create([
                'idUser' => $userId,
                'user_nom' => $user_id->nom,
                'user_prenom' => $user_id->prenom,
                'user_pseudo' => $user_id->pseudo,
                'user_doc' => $user_id->created_at,
                'action' => 'maj',
                'table_concernee' => 'users',
                'details' => "Changements effectués sur l'utilisateur (ID: {$user->id}): " . implode(", ", $modifications),

                'created_at' => now(),
            ]);
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur mis à jour avec succès.',
            'user' => $user
        ], 200);
    }


    // Fonction pour récupérer un user spécifique par son ID
    function getUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
        }
        // Retourne l'user correspondant à l'ID donné
        return response()->json([
            'status' => 'success',
            'user' => $user,
        ], 200);
    }



}
