<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{

    public function userOrders()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $orders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['error' => 'Aucune commande trouvée']);
        }

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function sendConfirmationEmail(Request $request)
    {
        $orderDetails = $request->all();

        $productIds = array_map(function ($product) {
            return $product['id'];
        }, $orderDetails['cart']);

        $order = new Order();
        $order->user_id = $orderDetails['user']['id'];
        $order->order_id = $orderDetails['orderId'];
        $order->product_ids = json_encode($productIds);
        $order->total_price = $orderDetails['totalAmount'];
        $order->save();

        try {
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = 'ssl0.ovh.net';
            $mail->Port = '465';
            $mail->Username = "contact@maplaque-nfc.fr";
            $mail->Password = "3v;jcPFeUPMBCP9";
            $mail->SetFrom("contact@maplaque-nfc.fr", "Livret d'accueil");
            $mail->addAddress($orderDetails['user']['email'], $orderDetails['user']['name']);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de votre commande';
            $body = '<div style="font-family: Arial, sans-serif; color: #333;">';
            $body .= '<h1 style="color: #4CAF50;">Merci pour votre commande</h1>';
            $body .= '<p>ID de la commande : <strong>' . $orderDetails['orderId'] . '</strong></p>';
            $body .= '<p>Total : <strong>' . number_format($orderDetails['totalAmount'], 2) . ' €</strong></p>';
            $body .= '<h2 style="color: #4CAF50;">Détails de la commande :</h2>';
            $body .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Produit</th>';
            $body .= '<th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Quantité</th>';
            $body .= '<th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Prix</th>';
            $body .= '</tr>';
            $body .= '</thead>';
            $body .= '<tbody>';

            foreach ($orderDetails['cart'] as $product) {
                $body .= '<tr>';
                $body .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $product['name'] . '</td>';
                $body .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $product['quantity'] . '</td>';
                $body .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($product['price'], 2) . ' €</td>';
                $body .= '</tr>';
            }
            $body .= '</tbody>';
            $body .= '</table>';
            $body .= '<h2 style="color: #4CAF50;">Informations de livraison :</h2>';
            $body .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
            $body .= '<tr>';
            $body .= '<td style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Nom</td>';
            $body .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $orderDetails['user']['name'] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Adresse</td>';
            $body .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $orderDetails['user']['address'] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Téléphone</td>';
            $body .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $orderDetails['user']['phone'] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Email</td>';
            $body .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $orderDetails['user']['email'] . '</td>';
            $body .= '</tr>';
            $body .= '</table>';
            $body .= '<p style="color: #666;">Si vous avez des questions, n\'hésitez pas à nous contacter.</p>';
            $body .= '</div>';
            $mail->Body = $body;
            $mail->send();
            return response()->json(['message' => 'E-mail envoyé avec succès.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Echec de l\'envoi de l\'e-mail : ' . $mail->ErrorInfo], 500);
        }
    }
}
