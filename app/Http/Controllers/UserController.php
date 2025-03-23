<?php
/**
 * Copyright (c) 2025 Mehdi Raposo
 * Ce fichier fait partie du projet Heberginfos.
 *
 * Ce fichier, ainsi que tout le code et les ressources qu'il contient,
 * est protégé par le droit d'auteur. Toute utilisation, modification,
 * distribution ou reproduction non autorisée est strictement interdite
 * sans une autorisation écrite préalable de Mehdi Raposo.
 */

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class UserController
 *
 * Contrôleur responsable de la gestion des utilisateurs.
 * Permet la mise à jour des informations de l'utilisateur, y compris l'avatar.
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * Met à jour les informations de l'utilisateur.
     * Si l'utilisateur est un administrateur, il peut mettre à jour les informations d'un autre utilisateur.
     *
     * @param UpdateUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(UpdateUserRequest $request)
    {
        // Validation des données fournies par la requête
        $validedData = $request->validated();

        // Si l'utilisateur est un administrateur et met à jour un autre utilisateur
        if ($validedData['admin_update']) {
            $user = User::find($request->admin_update);

            if (!$user) {
                return response()->json(['error' => 'Utilisateur introuvable']);
            }
        } else {
            // Si l'utilisateur est authentifié, il met à jour ses propres informations
            $user = JWTAuth::parseToken()->authenticate();
        }

        // Mise à jour des informations de l'utilisateur
        $user->civility = $validedData['civility'];
        $user->name = $validedData['name'];
        $user->phone = $validedData['phone'];
        $user->birth_date = $validedData['birth_date'];
        $user->address = $validedData['address'];

        // Si un avatar est fourni, il est validé et mis à jour
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'mimes:png,jpg,jpeg,webp',
            ]);

            // Récupérer le fichier avatar
            $validatedData = $request->validate([
                'avatar' => 'mimes:png,jpg,jpeg,webp',
            ]);

            $avatar = $validatedData['avatar'];
            $filename = time() . '.' . $avatar->getClientOriginalExtension();

            // Supprimer l'ancien avatar s'il existe
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            // Déplacer le nouveau fichier dans le répertoire de téléchargement
            $avatar->move(public_path('assets/uploads/avatars'), $filename);
            $user->avatar = 'assets/uploads/avatars/' . $filename;
        }

        // Enregistrer les informations mises à jour dans la base de données
        $user->save();

        // Retourner la réponse en fonction du type de mise à jour (administrateur ou utilisateur)
        if ($validedData['admin_update']) {
            return response()->json(['message' => 'Utilisateur mis à jour avec succès', 'user' => $user]);
        } else {
            return response()->json(['message' => 'Votre profil a été mis à jour avec succès', 'user' => $user]);
        }
    }
}
