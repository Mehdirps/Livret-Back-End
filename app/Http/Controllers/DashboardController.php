<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{
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

    public function seeFirstLogin()
    {
        if (!auth()->user()->first_login && auth()->user()->livret) {
            return response()->json(['first_login' => false]);
        }


        return response()->json(['first_login' => true]);
    }

    public function profile()
    {
        $user = auth()->user();
        $livret = $user->livret;

        if (!$livret) {
            return response()->json(['error' => 'Vous n\'avez pas de livret associé']);
        }

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable']);
        }

        return response()->json([
            'user' => $user,
            'livret' => $livret,
        ]);
    }

    public function editLivret()
    {
        $livret = auth()->user()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        return response()->json([
            'livret' => $livret,
        ]);
    }

    public function contactSupport(Request $request)
    {

        $validatedData = $request->validate([
            'subject' => 'required',
            'message' => 'required',
            'rgpd' => 'required',
        ]);

        if ($validatedData['rgpd'] !== true) {
            return response()->json(['error' => 'Vous devez accepter les conditions d\'utilisation']);
        }
        $user = JWTAuth::parseToken()->authenticate();

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
            $mail->Subject = 'Nouveau support - Livret d\'accueil';
            $mail->Body = '
                <html>
                <body>
                  <h1>Demande de support</h1>
                   <p>De : ' . $user->name . '</p>
                   <p>Email : ' . $user->email . '</p>
                   <p>Sujet : ' . $validatedData['subject'] . '</p>
                   <p>' . $validatedData['message'] . '</p>
                </body>
                </html>
            ';
            $mail->AddAddress('mehdi.raposo77@gmail.com');

            $mail->send();

            return response()->json(['message' => 'Votre demande de support a été envoyée avec succès']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de l\'envoi de votre demande']);
        }
    }

    /*  public function searchProducts(Request $request)
      {
          $categories = ProductCategory::all();
          $products = Product::where('name', 'like', '%' . $request->search . '%')->paginate(15);

          return view('dashboard.shop', [
              'categories' => $categories,
              'products' => $products,
          ]);
      }*/

    // public function exportDatas(Request $request)
    // {
    //     $data = $request->input('data');

    //     $type = $request->input('type');

    //     if ($type == 'suggestions') {
    //         $pdf = PDF::loadView('dashboard.partials.suggestions_pdf', ['data' => $data]);
    //     } elseif ($type == 'inventories') {
    //         $pdf = PDF::loadView('dashboard.partials.inventories_pdf', ['data' => $data]);
    //     } elseif ($type == 'stats') {
    //         $pdf = PDF::loadView('dashboard.partials.stats_pdf', ['data' => $data]);
    //     }

    //     $output = $pdf->output();

    //     return response()->json([
    //         'status' => 'success',
    //         'pdf_base64' => base64_encode($output)
    //     ]);
    // }
}
