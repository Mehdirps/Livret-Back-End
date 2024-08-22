<?php

namespace App\Http\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    public function sendEmail($email, $body, $subject)
    {
        try {
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Host = env('MAIL_HOST');
            $mail->Port = env('MAIL_PORT');
            $mail->isHTML(true);
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SetFrom(env('MAIL_FROM_ADDRESS'), "Livret d'accueil");
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AddAddress($email);

            $mail->send();
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email de vérification']);
        }
    }
}
