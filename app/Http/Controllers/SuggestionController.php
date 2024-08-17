<?php

namespace App\Http\Controllers;

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

            try {

                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'ssl';
                $mail->Host = 'ssl0.ovh.net';
                $mail->Port = '465';
                $mail->isHTML(true);
                $mail->Username = "contact@maplaque-nfc.fr";
                $mail->Password = "3v;jcPFeUPMBCP9";
                $mail->SetFrom("contact@maplaque-nfc.fr", "Livret d'accueil");
                $mail->Subject = 'Nouvelle suggestion pour votre livret';
                $mail->Body = '<h1>Nouvelle suggestion de ' . $request->name . '</h1>
                              <p>Titre: ' . $request->title . '</p>
                              <p>Message: ' . $request->message . '</p>';
                $mail->AddAddress('mehdi.raposo77@gmail.com');
                $mail->send();

                return response()->json(['message' => 'Votre suggestion a été envoyée avec succès.'], 201);
            } catch (Exception $e) {
                return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email.'], 500);
            }
        }
    }
}
