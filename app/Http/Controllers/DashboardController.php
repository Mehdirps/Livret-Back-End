<?php

namespace App\Http\Controllers;

use App\Http\Services\Email;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Contrôleur du tableau de bord.
 *
 * Gère les fonctionnalités principales du tableau de bord utilisateur :
 * - Affichage des informations de l'utilisateur et du livret associé.
 * - Gestion de la première connexion.
 * - Envoi de demandes de support.
 */
class DashboardController extends Controller
{
    /**
     * Récupère les informations de l'utilisateur connecté et de son livret.
     *
     * @return \Illuminate\Http\JsonResponse Réponse JSON contenant les informations utilisateur et livret.
     */
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->first_login) {
            return response()->json(['first_login' => true]);
        }

        $livret = $user->livret;

        return response()->json([
            'first_login' => false,
            'livret' => $livret,
        ]);
    }

    /**
     * Vérifie si l'utilisateur est à sa première connexion.
     *
     * @return \Illuminate\Http\JsonResponse Réponse JSON indiquant l'état de la première connexion.
     */
    public function seeFirstLogin()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user->first_login && $user->livret) {
            return response()->json(['first_login' => false]);
        }

        return response()->json(['first_login' => true]);
    }

    /**
     * Récupère le profil utilisateur et les informations du livret associé.
     *
     * @return \Illuminate\Http\JsonResponse Réponse JSON contenant les informations utilisateur et livret.
     */
    public function profile()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $livret = $user->livret;

        if (!$livret) {
            return response()->json(['error' => 'Vous n\'avez pas de livret associé']);
        }

        return response()->json([
            'user' => $user,
            'livret' => $livret,
        ]);
    }

    /**
     * Envoie une demande de support à l'équipe technique.
     *
     * @param Request $request Requête contenant les informations de support.
     * @return \Illuminate\Http\JsonResponse Réponse JSON confirmant l'envoi de la demande.
     */
    public function contactSupport(Request $request)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'rgpd' => 'required|boolean',
        ]);

        if (!$validatedData['rgpd']) {
            return response()->json(['error' => 'Vous devez accepter les conditions d\'utilisation']);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $emailBody = '
            <html>
            <body>
                <h1>Demande de support</h1>
                <p><strong>De :</strong> ' . $user->name . '</p>
                <p><strong>Email :</strong> ' . $user->email . '</p>
                <p><strong>Sujet :</strong> ' . $validatedData['subject'] . '</p>
                <p>' . nl2br($validatedData['message']) . '</p>
            </body>
            </html>
        ';

        $mail = new Email();
        $mail->sendEmail(
            env('MAIL_FROM_ADDRESS'),
            $emailBody,
            'Nouveau support - Livret d\'accueil'
        );

        return response()->json(['message' => 'Votre demande de support a été envoyée avec succès']);
    }
}
