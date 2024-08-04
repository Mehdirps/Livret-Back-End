<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function doLogin(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Identifiants incorrects'], 401);
        }

        $user = auth()->user();

        if ($user->role == 'admin') {
            return redirect()->route('admin.index');
        }

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
            'user' => $user
        ];

        return response()->json($response);
    }


    public function doRegister(RegisterRequest $request)
    {

        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'etablissement_type' => $validatedData['etablissement_type'],
        ]);

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
            $mail->Subject = 'Merci pour votre inscription !';
            $mail->Body = '
            <h1>Bienvenue sur notre site web</h1>
           <p>Merci de vous être inscrit sur notre site web. Veuillez cliquer sur le lien ci-dessous pour vérifier votre adresse e-mail et compléter votre inscription :</p>
           <p>
              <a href="' . url('/auth/verify_email/' . $user->email) . '">Vérifier l\'Email</a>
           </p>
           <p>Si vous ne vous êtes pas inscrit sur notre site web, veuillez ignorer cet email.</p>
           <p>Meilleures salutations,</p>
           <p>L\'équipe de votre site web</p>';
            $mail->AddAddress($user->email);

            $mail->send();

        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email de vérification']);

        }

        return response()->json(['success' => "Votre inscription à été enregistrer avec succès ! Un email de vérification a été envoyé à votre adresse e-mail. Veuillez vérifier votre boite de réception."]);
    }

    public function verify($email)
    {
        $user = User::where('email', $email)->first();

        if(!$user){
            return response()->json(['error' => 'Aucun utilisateur trouvé']);
        }
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Lien de vérification invalide']);
    }

    public function doLogout()
    {
        Auth::logout();
        return response()->json(['success' => true]);
    }

    public function verifyToken()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Utilisateur non trouvé'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token invalide'], 401);
        }

        return response()->json(['success' => true]);
    }
}
