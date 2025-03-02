<?php

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
              <a href="' . url('/auth/verify_email/' . $user->email) . '">Vérifier l\'Email</a>
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
    public function verify($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Aucun utilisateur trouvé'], 404);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json(['success' => true], 200);
    }

    /**
     * Déconnecte l'utilisateur authentifié.
     *
     * @return \Illuminate\Http\JsonResponse Réponse confirmant la déconnexion.
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
}
