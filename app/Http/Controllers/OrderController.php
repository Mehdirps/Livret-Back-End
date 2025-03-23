<?php
/**
 * Copyright (c) 2025 Mehdi Raposo
 * Ce fichier fait partie du projet Heberginfos.
 *
 * Ce fichier, ainsi que tout le code et les ressources qu'il contient,
 * est protégé par le droit d'auteur. Toute utilisation, modification,
 * distribution ou reproduction non autorisée est strictement interdite
 * sans une autorisation écrite préalable de Mehdi Raposo.
 */

namespace App\Http\Controllers;

use App\Http\Services\Email;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class OrderController
 *
 * Contrôleur responsable de la gestion des commandes des utilisateurs et de l'envoi d'e-mails de confirmation.
 * Permet de récupérer les commandes d'un utilisateur et d'envoyer un e-mail de confirmation de commande.
 *
 * @package App\Http\Controllers
 */
class OrderController extends Controller
{
    /**
     * Retourne la liste des commandes d'un utilisateur authentifié.
     *
     * Cette méthode récupère toutes les commandes passées par un utilisateur, triées par date de création.
     * Si l'utilisateur n'a aucune commande, une erreur est retournée.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userOrders()
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Récupérer les commandes de l'utilisateur
        $orders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($orders as $order) {
            // Assurez-vous que product_ids est un tableau
            $order->product_ids = json_decode($order->product_ids, true);
            $order->products = $order->getProducts();
        }

        // Si aucune commande n'est trouvée, renvoyer une erreur
        if ($orders->isEmpty()) {
            return response()->json(['error' => 'Aucune commande trouvée']);
        }

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Envoie un e-mail de confirmation de commande à l'utilisateur.
     *
     * Cette méthode envoie un e-mail avec les détails de la commande après que l'utilisateur ait passé une commande.
     * Les détails de la commande et de l'utilisateur sont envoyés dans l'e-mail.
     *
     * @param Request $request La requête HTTP contenant les détails de la commande.
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function sendConfirmationEmail(Request $request)
    {
        $orderDetails = $request->all();

        // Extraction des identifiants des produits de la commande
        $productIds = array_map(function ($product) {
            return $product['id'];
        }, $orderDetails['cart']);

        // Création de la commande dans la base de données
        $order = new Order();
        $order->user_id = $orderDetails['user']['id'];
        $order->order_id = $orderDetails['orderId'];
        $order->product_ids = json_encode($productIds);
        $order->total_price = $orderDetails['totalAmount'];
        $order->save();

        // Corps de l'e-mail avec les détails de la commande
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

        // Boucle sur les produits dans le panier pour afficher leurs détails
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

        // Envoi de l'e-mail
        $mail = new Email();
        $mail->sendEmail($orderDetails['user']['email'], $body, 'Confirmation de votre commande');

        return response()->json(['message' => 'E-mail envoyé avec succès.'], 200);
    }
}
