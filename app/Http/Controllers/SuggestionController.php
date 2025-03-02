<?php

namespace App\Http\Controllers;

use App\Http\Services\Email;
use App\Models\Livret;
use App\Models\Suggest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class SuggestionController
 *
 * Contrôleur responsable de la gestion des suggestions des utilisateurs.
 * Permet de créer, consulter, mettre à jour et rechercher des suggestions pour un livret.
 *
 * @package App\Http\Controllers
 */
class SuggestionController extends Controller
{
    /**
     * Enregistre une nouvelle suggestion dans la base de données.
     * Envoie également un email au propriétaire du livret pour l'informer de la suggestion.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validation des données de la suggestion
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'livret_id' => 'required|integer',
            'rgpd' => 'required|boolean'
        ]);

        // Vérification de l'acceptation des conditions d'utilisation (RGPD)
        if ($validatedData['rgpd'] !== true) {
            return response()->json(['error' => 'Vous devez accepter les conditions d\'utilisation.'], 400);
        } else {
            // Création de la suggestion
            $suggestion = new Suggest();
            $suggestion->livret_id = $validatedData['livret_id'];
            $suggestion->name = $validatedData['name'];
            $suggestion->email = $validatedData['email'];
            $suggestion->title = $validatedData['title'];
            $suggestion->message = $validatedData['message'];
            $suggestion->status = 'pending';
            $suggestion->save();

            // Envoi de l'email au propriétaire du livret
            $emailToSend = Livret::find($validatedData['livret_id'])->user->email;
            $body = '<h1>Nouvelle suggestion de ' . $request->name . '</h1>
                     <p>Titre: ' . $request->title . '</p>
                     <p>Message: ' . $request->message . '</p>';

            $mail = new Email();
            $mail->sendEmail($emailToSend, $body, 'Nouvelle suggestion');

            // Réponse en cas de succès
            return response()->json(['message' => 'Votre suggestion a été envoyée avec succès.'], 201);
        }
    }

    /**
     * Récupère toutes les suggestions associées à un livret.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions()
    {
        // Récupérer le livret de l'utilisateur authentifié
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        // Récupérer les suggestions du livret
        $suggestions = $livret->suggestions()->get();

        if (!$suggestions) {
            return response()->json(['error' => 'Aucune suggestion trouvée']);
        }

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Active ou désactive les suggestions pour un livret.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function enableSuggestion($id)
    {
        // Récupérer le livret
        $livret = Livret::find($id);

        // Activer ou désactiver les suggestions
        $livret->suggest = !$livret->suggest;
        $livret->save();

        return response()->json(['message' => 'Les suggestions ont été activées avec succès', 'livret' => $livret]);
    }

    /**
     * Met à jour le statut d'une suggestion.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusSuggestion(Request $request)
    {
        // Validation des données pour la mise à jour du statut
        $validatedData = $request->validate([
            'status_suggest' => 'required|string|in:pending,accepted,refused',
            'suggestion_id' => 'required|integer',
        ]);

        // Récupérer la suggestion et mettre à jour son statut
        $suggestion = Suggest::find($validatedData['suggestion_id']);
        $suggestion->status = $validatedData['status_suggest'];
        $suggestion->save();

        return response()->json(['message' => 'Le status de la suggestion a été mis à jour avec succès']);
    }

    /**
     * Recherche des suggestions en fonction des critères fournis.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchSuggestions(Request $request)
    {
        // Validation des critères de recherche
        $validator = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'title' => 'nullable|string',
            'message' => 'nullable|string',
            'status' => 'nullable|string|in:all,pending,accepted,refused',
        ]);

        $validatedData = $validator;

        // Construction de la requête de recherche
        $name = $validatedData['name'];
        $email = $validatedData['email'];
        $title = $validatedData['title'];
        $message = $validatedData['message'];
        $status = $validatedData['status'];

        $query = Suggest::query();

        // Filtrage des suggestions en fonction des critères
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if ($email) {
            $query->where('email', 'like', '%' . $email . '%');
        }

        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        if ($message) {
            $query->where('message', 'like', '%' . $message . '%');
        }

        if ($status && $status != 'all') {
            $query->where('status', $status);
        }

        // Récupérer les suggestions filtrées
        $suggestions = $query->get();

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }
}
