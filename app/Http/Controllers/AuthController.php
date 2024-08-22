<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Services\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $livret = $user->livret;

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
            'user' => $user,
            'livret' => $livret
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


        return response()->json(['success' => "Votre inscription à été enregistrer avec succès ! Un email de vérification a été envoyé à votre adresse e-mail. Veuillez vérifier votre boite de réception."]);
    }

    public function verify($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
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

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function updatePassword(Request $request)
    {

        $validatedData = $request->validate([
            'old_password' => 'required',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ]);

        /*        $validatedData = $validator->validated();*/

        $user = JWTAuth::parseToken()->authenticate();

        if (!Hash::check($validatedData['old_password'], $user->password)) {
            return response()->json(['error' => 'L\'ancien mot de passe est incorrect']);
        }

        $user->password = Hash::make($validatedData['password']);
        $user->save();

        return response()->json(['message' => 'Votre mot de passe a été mis à jour avec succès']);
    }
}
