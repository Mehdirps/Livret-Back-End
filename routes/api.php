<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->group(function () {

    Route::get('/', function () {
        return response()->json([
            'message' => 'Bienvenu sur l\'API de l\'application Livret',
            'status' => 200
        ]);
    });

    Route::get('/livret/{slug}/{id}', [App\Http\Controllers\LivretController::class, 'show']);

    /* Suggestions */
    Route::post('/suggestions', [App\Http\Controllers\SuggestionController::class, 'store']);

    /* Authentification */
    Route::prefix('auth')->group(function () {
        Route::post('login', [App\Http\Controllers\AuthController::class, 'doLogin'])->name('home.login');
        Route::post('register', [App\Http\Controllers\AuthController::class, 'doRegister'])->name('home.register');
        Route::get('verify_email/{email}', [App\Http\Controllers\AuthController::class, 'verify'])->name('home.verify');
        Route::get('logout', [App\Http\Controllers\AuthController::class, 'doLogout'])->name('dashboard.logout');
    });

    /* Dashboard */
    Route::prefix('dashboard')->middleware('auth.jwt')->group(function () {
        Route::get('verify_token', [App\Http\Controllers\AuthController::class, 'verifyToken'])->name('dashboard.verify_token');
        /* First login / Create livret*/
        Route::post('first_login', [App\Http\Controllers\LivretController::class, 'store'])->name('dashboard.first_login');

        /* Dashboard index*/
        Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');

        /* Profile */
        Route::post('profile', [App\Http\Controllers\DashboardController::class, 'updateUser'])->name('dashboard.profile.update_user');

        /* Password */
        Route::post('profile/update_password', [App\Http\Controllers\DashboardController::class, 'updatePassword'])->name('dashboard.profile.update_password');

        /* Livret */
        Route::post('profile/update_livret', [App\Http\Controllers\DashboardController::class, 'updateLivret'])->name('dashboard.profile.update_livret');
        Route::get('background', [App\Http\Controllers\DashboardController::class, 'background'])->name('dashboard.background');
        Route::get('background/{id}', [App\Http\Controllers\DashboardController::class, 'updateBackground'])->name('dashboard.background.update');
        Route::get('edit_livret', [App\Http\Controllers\DashboardController::class, 'editLivret'])->name('dashboard.edit_livret');
        Route::get('stats', [App\Http\Controllers\DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::post('statsBetweenDates', [App\Http\Controllers\DashboardController::class, 'statsBetweenDates'])->name('dashboard.statsBetweenDates');
        /* Livret Module */
        /* Wifi */
        Route::post('module/wifi', [App\Http\Controllers\DashboardController::class, 'addModuleWifi'])->name('dashboard.module.wifi');
        Route::get('module/wifi/{id}', [App\Http\Controllers\DashboardController::class, 'deleteModuleWifi'])->name('dashboard.module.wifi.delete');

        /* Digicode */
        Route::post('module/digicode', [App\Http\Controllers\DashboardController::class, 'addModuleDigicode'])->name('dashboard.module.digicode');
        Route::get('module/digicode/{id}', [App\Http\Controllers\DashboardController::class, 'deleteModuleDigicode'])->name('dashboard.module.digicode.delete');

        /* Utils phone */
        Route::post('module/utils_phone', [App\Http\Controllers\DashboardController::class, 'addModuleUtilsPhone'])->name('dashboard.module.utils_phone');
        Route::get('module/utils_phone/{id}', [App\Http\Controllers\DashboardController::class, 'deleteModuleUtilsPhone'])->name('dashboard.module.utils_phone.delete');

        /* Utils infos */
        Route::post('module/utils_infos', [App\Http\Controllers\DashboardController::class, 'addModuleUtilsInfos'])->name('dashboard.module.utils_infos');
        Route::get('module/utils_infos/{id}', [App\Http\Controllers\DashboardController::class, 'deleteModuleUtilsInfos'])->name('dashboard.module.utils_infos.delete');

        /* Start info */
        Route::post('module/start_info', [App\Http\Controllers\DashboardController::class, 'addModuleStartInfo'])->name('dashboard.module.start_info');
        Route::get('module/start_info/{id}', [App\Http\Controllers\DashboardController::class, 'deleteModuleStartInfo'])->name('dashboard.module.start_info.delete');

        /* End info */
        Route::post('module/end_info', [App\Http\Controllers\DashboardController::class, 'addModuleEndInfo'])->name('dashboard.module.end_info');
        Route::get('module/end_info/{id}', [App\Http\Controllers\DashboardController::class, 'deleteModuleEndInfo'])->name('dashboard.module.end_info.delete');

        /* Home Infos */
        Route::post('module/home_infos', [App\Http\Controllers\DashboardController::class, 'addModuleHomeInfos'])->name('dashboard.module.home_infos');

        /* Places groups */
        Route::post('module/places_groups', [App\Http\Controllers\DashboardController::class, 'addModulePlacesGroups'])->name('dashboard.module.places_groups');
        Route::get('module/places_groups/{id}', [App\Http\Controllers\DashboardController::class, 'deleteModulePlacesGroups'])->name('dashboard.module.places_groups.delete');

        /* Nearby Places */
        Route::post('module/nearby_places', [App\Http\Controllers\DashboardController::class, 'addModuleNearbyPlaces'])->name('dashboard.module.nearby_places');
        Route::get('module/nearby_places/{id}', [App\Http\Controllers\DashboardController::class, 'deleteModuleNearbyPlaces'])->name('dashboard.module.nearby_places.delete');

        /* Module order */
        Route::post('/update-order', [App\Http\Controllers\DashboardController::class, 'updateOrder'])->name('dashboard.update-order');

        /* Inventories */
        Route::get('inventories', [App\Http\Controllers\DashboardController::class, 'inventories'])->name('dashboard.inventories');
        Route::post('inventories', [App\Http\Controllers\DashboardController::class, 'addInventory'])->name('dashboard.inventories.add');
        Route::post('inventories/status', [App\Http\Controllers\DashboardController::class, 'statusInventory'])->name('dashboard.inventories.status');
        Route::delete('inventories/{id}', [App\Http\Controllers\DashboardController::class, 'deleteInventory'])->name('dashboard.inventories.delete');
        Route::post('inventories/search', [App\Http\Controllers\DashboardController::class, 'searchInventories'])->name('dashboard.inventories.search');

        /* Contact */
        Route::post('module/contact', [App\Http\Controllers\DashboardController::class, 'contactSupport'])->name('dashboard.contact');

        /* Suggestions */
        Route::get('suggestions', [App\Http\Controllers\DashboardController::class, 'suggestions'])->name('dashboard.suggestions');
        Route::get('suggestion/enable/{id}', [App\Http\Controllers\DashboardController::class, 'enableSuggestion'])->name('dashboard.suggestion.enable');
        Route::post('suggestion/status', [App\Http\Controllers\DashboardController::class, 'statusSuggestion'])->name('dashboard.suggestion.status');
        Route::post('suggestion/search', [App\Http\Controllers\DashboardController::class, 'searchSuggestions'])->name('dashboard.suggestion.search');

        /* Products */
        Route::get('products', [App\Http\Controllers\DashboardController::class, 'products'])->name('dashboard.products');
        /*    Route::get('products/search', [App\Http\Controllers\DashboardController::class, 'searchProducts'])->name('dashboard.products.search');*/

        /* Text design */
        Route::post('/update-text-design', [App\Http\Controllers\DashboardController::class, 'updateTextDesign'])->name('dashboard.update-text-design');

        /* PDF Export */
        Route::post('datas/export', [App\Http\Controllers\DashboardController::class, 'exportDatas'])->name('dashboard.datas.export');
    });

    /* Admin */
    Route::prefix('admin')->middleware('auth.jwt')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');

        /* Users */
        Route::get('users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users.index');
        Route::get('users/enable/{id}', [App\Http\Controllers\AdminController::class, 'enableUser'])->name('admin.user.enable');
        Route::get('/admin/user/search', [App\Http\Controllers\AdminController::class, 'searchUsers'])->name('admin.user.searchUsers');

        /* Backgrounds */
        Route::get('backgrounds', [App\Http\Controllers\AdminController::class, 'backgrounds'])->name('admin.backgrounds.index');
        Route::post('background_groups', [App\Http\Controllers\AdminController::class, 'addBackgroundGroup'])->name('admin.background_groups.add');
        Route::get('background_groups/{id}', [App\Http\Controllers\AdminController::class, 'deleteBackgroundGroup'])->name('admin.background_groups.delete');
        Route::post('backgrounds', [App\Http\Controllers\AdminController::class, 'addBackground'])->name('admin.backgrounds.add');
        Route::get('backgrounds/{id}', [App\Http\Controllers\AdminController::class, 'deleteBackground'])->name('admin.backgrounds.delete');

        /* Livrets */
        Route::get('livrets', [App\Http\Controllers\AdminController::class, 'livrets'])->name('admin.livrets.index');
        Route::get('livret/search', [App\Http\Controllers\AdminController::class, 'searchLivrets'])->name('admin.livret.searchLivrets');

        /* Products */
        Route::get('products', [App\Http\Controllers\AdminController::class, 'products'])->name('admin.products.index');
        Route::post('products', [App\Http\Controllers\AdminController::class, 'addProduct'])->name('admin.products.add');
        Route::get('products/{id}', [App\Http\Controllers\AdminController::class, 'deleteProduct'])->name('admin.products.delete');
        Route::post('products/update', [App\Http\Controllers\AdminController::class, 'updateProduct'])->name('admin.products.update');
        Route::post('products/search', [App\Http\Controllers\AdminController::class, 'searchProducts'])->name('admin.products.searchProducts');

        /* Product Catégories */
        Route::post('product_categories', [App\Http\Controllers\AdminController::class, 'addProductCategory'])->name('admin.product_categories.add');
        Route::get('product_categories/{id}', [App\Http\Controllers\AdminController::class, 'deleteProductCategory'])->name('admin.product_categories.delete');
        Route::post('product_categories/update', [App\Http\Controllers\AdminController::class, 'updateProductCategory'])->name('admin.product_categories.update');
    });

    Route::post('/stripe-intent', function (Request $request) {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $amount = $request->amount * 100;
        $currency = 'eur';

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ]);

        return response()->json(['client_secret' => $paymentIntent->client_secret]);
    });

    Route::post('/send-confirmation-email', function (\Illuminate\Http\Request $request) {
        $orderDetails = $request->all();
        // TODO Faire table commande et faire creation de commande chaque commande
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
    });
});
