<?php

namespace App\Http\Controllers;

use App\Http\Services\Email;
use App\Models\Livret;
use App\Models\Suggest;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class SuggestionController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'livret_id' => 'required|integer',
            'rgpd' => 'required|boolean'
        ]);

        if ($validatedData['rgpd'] !== true) {
            return response()->json(['error' => 'Vous devez accepter les conditions d\'utilisation.'], 400);
        } else {


            $suggestion = new Suggest();
            $suggestion->livret_id = $validatedData['livret_id'];
            $suggestion->name = $validatedData['name'];
            $suggestion->email = $validatedData['email'];
            $suggestion->title = $validatedData['title'];
            $suggestion->message = $validatedData['message'];
            $suggestion->status = 'pending';
            $suggestion->save();

            $emailToSend = Livret::find($validatedData['livret_id'])->email;

            $body = '<h1>Nouvelle suggestion de ' . $request->name . '</h1>
                              <p>Titre: ' . $request->title . '</p>
                              <p>Message: ' . $request->message . '</p>';

            $mail = new Email();
            $mail->sendEmail($emailToSend, $body, 'Nouvelle suggestion');

            return response()->json(['message' => 'Votre suggestion a été envoyée avec succès.'], 201);
        }
    }

    public function suggestions()
    {
        $livret = auth()->user()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $suggestions = $livret->suggestions()->get();

        if (!$suggestions) {
            return response()->json(['error' => 'Aucune suggestion trouvée']);
        }

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    public function enableSuggestion($id)
    {
        $livret = Livret::find($id);
        $livret->suggest = !$livret->suggest;
        $livret->save();

        return response()->json(['message' => 'Les suggestions ont été activées avec succès', 'livret' => $livret]);
    }

    public function statusSuggestion(Request $request)
    {
        $validatedData = $request->validate([
            'status_suggest' => 'required|string|in:pending,accepted,refused',
            'suggestion_id' => 'required|integer',
        ]);

        $suggestion = Suggest::find($validatedData['suggestion_id']);
        $suggestion->status = $validatedData['status_suggest'];
        $suggestion->save();

        return response()->json(['message' => 'Le status de la suggestion a été mis à jour avec succès']);
    }

    public function searchSuggestions(Request $request)
    {

        $validator = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'title' => 'nullable|string',
            'message' => 'nullable|string',
            'status' => 'nullable|string|in:all,pending,accepted,refused',
        ]);

        $validatedData = $validator;

        $name = $validatedData['name'];
        $email = $validatedData['email'];
        $title = $validatedData['title'];
        $message = $validatedData['message'];
        $status = $validatedData['status'];

        $query = Suggest::query();

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

        $suggestions = $query->get();

        return response()->json([
            /*'livret' => JWTAuth::parseToken()->authenticate()->livret,*/
            'suggestions' => $suggestions,
        ]);
    }
}
