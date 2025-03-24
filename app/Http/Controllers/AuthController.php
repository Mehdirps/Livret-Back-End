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

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Services\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Contrôleur d'authentification.
 *
 * Gère les actions liées à l'authentification des utilisateurs :
 * - Connexion
 * - Inscription
 * - Vérification d'email
 * - Déconnexion
 * - Mise à jour du mot de passe
 * - Validation du token JWT
 */
class AuthController extends Controller
{
    /**
     * Authentifie un utilisateur et génère un token JWT.
     *
     * @param LoginRequest $request Requête contenant les données de connexion validées.
     * @return \Illuminate\Http\JsonResponse Réponse avec le token et les informations utilisateur en cas de succès.
     */
    /**
     * Authentifie un utilisateur et génère un token JWT.
     *
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="Connexion d'un utilisateur",
     *     description="Authentifie un utilisateur avec ses identifiants et retourne un token JWT.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="first_login", type="boolean"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="livret", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants incorrects"
     *     )
     * )
     */
    public function doLogin(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Identifiants incorrects'], 401);
        }

        $user = auth()->user();
        $livret = $user->livret;

        if (!$user->email_verified_at) {
            auth()->logout();
            return response()->json(['error' => 'Votre adresse e-mail n\'a pas été vérifiée. Veuillez vérifier votre boite de réception pour le lien de vérification.']);
        }

        if (!$user->active) {
            auth()->logout();
            return response()->json(['error' => 'Votre compte a été désactivé, veuillez contacter l\'administrateur pour plus d\'informations.']);
        }

        $response = [
            'success' => true,
            'first_login' => $user->first_login,
            'token' => $token,
            'user' => $user,
            'livret' => $livret
        ];

        return response()->json($response, 200);
    }

    /**
     * Enregistre un nouvel utilisateur.
     *
     * @param RegisterRequest $request Requête contenant les données d'inscription validées.
     * @return \Illuminate\Http\JsonResponse Réponse confirmant l'inscription et l'envoi de l'email de vérification.
     */
    /**
     * Enregistre un nouvel utilisateur.
     *
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="Inscription d'un utilisateur",
     *     description="Crée un nouvel utilisateur et envoie un email de vérification.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="etablissement_type", type="string", example="Hôtel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="Votre inscription a été enregistrée avec succès...")
     *         )
     *     )
     * )
     */
    public function doRegister(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'etablissement_type' => $validatedData['etablissement_type'],
        ]);

        $emailBody = '
            <h1>Bienvenue sur notre site web</h1>
           <p>Merci de vous être inscrit sur notre site web. Veuillez cliquer sur le lien ci-dessous pour vérifier votre adresse e-mail et compléter votre inscription :</p>
           <p>
              <a href="' . url('/api/auth/verify_email/' . $user->email) . '">Vérifier l\'Email</a>
           </p>
           <p>Si vous ne vous êtes pas inscrit sur notre site web, veuillez ignorer cet email.</p>
           <p>Meilleures salutations,</p>
           <p>L\'équipe de votre site web</p>';

        $mail = new Email();
        $mail->sendEmail($user->email, $emailBody, 'Merci pour votre inscription !');

        return response()->json([
            'success' => "Votre inscription a été enregistrée avec succès ! Un email de vérification a été envoyé à votre adresse e-mail. Veuillez vérifier votre boite de réception."
        ], 201);
    }

    /**
     * Vérifie l'adresse e-mail d'un utilisateur.
     *
     * @param string $email Adresse e-mail à vérifier.
     * @return \Illuminate\Http\JsonResponse Réponse confirmant ou refusant la vérification de l'email.
     */
    /**
     * Vérifie l'adresse e-mail d'un utilisateur.
     *
     * @OA\Get(
     *     path="/api/auth/verify_email/{email}",
     *     tags={"Auth"},
     *     summary="Vérification d'email",
     *     description="Marque l'email d'un utilisateur comme vérifié.",
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         required=true,
     *         description="Adresse e-mail de l'utilisateur",
     *         @OA\Schema(type="string", format="email", example="user@example.com")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email vérifié avec succès",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur introuvable"
     *     )
     * )
     */
    public function verify($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Aucun utilisateur trouvé'], 404);
        }

        $user->email_verified_at = now();
        $user->save();

        return redirect()->away('https://herbeginfos.fr/connexion');
    }

    /**
     * Déconnecte l'utilisateur authentifié.
     *
     * @return \Illuminate\Http\JsonResponse Réponse confirmant la déconnexion.
     */
    /**
     * Déconnecte l'utilisateur authentifié.
     *
     * @OA\GET(
     *     path="/api/auth/logout",
     *     tags={"Auth"},
     *     summary="Déconnexion",
     *     description="Déconnecte l'utilisateur actuel.",
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))
     *     )
     * )
     */
    public function doLogout()
    {
        Auth::logout();
        return response()->json(['success' => true], 200);
    }

    /**
     * Vérifie la validité du token JWT.
     *
     * @return \Illuminate\Http\JsonResponse Réponse avec les informations utilisateur si le token est valide.
     */
    /**
     * Vérifie la validité du token JWT.
     *
     * @OA\Get(
     *     path="/api/auth/verify_token",
     *     tags={"Auth"},
     *     summary="Validation du token",
     *     description="Valide un token JWT et retourne les informations utilisateur si valide.",
     *     @OA\Response(
     *         response=200,
     *         description="Token valide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide"
     *     )
     * )
     */
    public function verifyToken()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Utilisateur non trouvé'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token invalide'], 401);
        }

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Met à jour le mot de passe de l'utilisateur authentifié.
     *
     * @param Request $request Requête contenant les anciens et nouveaux mots de passe.
     * @return \Illuminate\Http\JsonResponse Réponse confirmant la mise à jour du mot de passe ou indiquant une erreur.
     */
    /**
     * Met à jour le mot de passe de l'utilisateur.
     *
     * @OA\Post(
     *     path="/api/auth/update_password",
     *     tags={"Auth"},
     *     summary="Mise à jour du mot de passe",
     *     description="Met à jour le mot de passe de l'utilisateur authentifié.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="old_password", type="string", format="password", example="oldpassword"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Votre mot de passe a été mis à jour avec succès"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        $validatedData = $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = JWTAuth::parseToken()->authenticate();

        if (!Hash::check($validatedData['old_password'], $user->password)) {
            return response()->json(['error' => 'L\'ancien mot de passe est incorrect'], 400);
        }

        $user->password = Hash::make($validatedData['password']);
        $user->save();

        return response()->json(['message' => 'Votre mot de passe a été mis à jour avec succès']);
    }

    /**
     * Supprime le compte de l'utilisateur et toutes ses données associées.
     *
     * @OA\Delete(
     *     path="/api/auth/delete-account",
     *     tags={"Auth"},
     *     summary="Suppression du compte",
     *     description="Supprime définitivement le compte de l'utilisateur et toutes les données associées.",
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Votre compte a été supprimé avec succès"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function deleteAccount($token)
    {
        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé'], 404);
            }
            
            $livret = $user->livret;

            if ($livret) {
                $livret->wifi()->delete();
                $livret->digicode()->delete();
                $livret->endInfos()->delete();
                $livret->homeInfos()->delete();
                $livret->utilsPhone()->delete();
                $livret->startInfos()->delete();
                $livret->utilsInfos()->delete();
                $livret->placeGroups()->delete();
                $livret->NearbyPlaces()->delete();
                $livret->inventories()->delete();
                $livret->suggestions()->delete();
            }
            
            $livret->delete();
            $user->delete();
            return response()->json(['message' => 'Votre compte a été supprimé avec succès']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Jeton de réinitialisation invalide, veuillez réessayer et si le problème persiste, déconnectez-vous et reconnectez-vous.'], 401);
        }
    }
}
