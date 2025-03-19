<?php

use Illuminate\Support\Facades\Route;

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
            'message' => 'Bienvenue sur l\'API de l\'application Heberginfos !',
            'status' => 200
        ]);
    });

    Route::get('/livret/{slug}/{id}', [App\Http\Controllers\LivretController::class, 'show']);

    /* Suggestions */
    Route::post('/suggestions', [App\Http\Controllers\SuggestionController::class, 'store']);

    /* Authentification */
    Route::prefix('auth')->group(function () {
        Route::post('login', [App\Http\Controllers\AuthController::class, 'doLogin']);
        Route::post('register', [App\Http\Controllers\AuthController::class, 'doRegister']);
        Route::get('verify_email/{email}', [App\Http\Controllers\AuthController::class, 'verify']);
        Route::get('logout', [App\Http\Controllers\AuthController::class, 'doLogout']);
    });

    /* Dashboard */
    Route::prefix('dashboard')->middleware('auth.jwt')->group(function () {
        Route::get('verify_token', [App\Http\Controllers\AuthController::class, 'verifyToken']);
        /* First login / Create livret*/
        Route::post('first_login', [App\Http\Controllers\LivretController::class, 'store']);

        /* Dashboard index*/
        Route::get('/', [App\Http\Controllers\DashboardController::class, 'index']);

        /* Order */
        Route::get('userOrders', [App\Http\Controllers\OrderController::class, 'userOrders']);

        /* Profile */
        Route::post('profile', [App\Http\Controllers\UserController::class, 'updateUser']);

        /* Password */
        Route::post('profile/update_password', [App\Http\Controllers\AuthController::class, 'updatePassword']);

        /* Livret */
        Route::post('profile/update_livret', [App\Http\Controllers\LivretController::class, 'updateLivret']);
        Route::get('background', [App\Http\Controllers\BackgroundController::class, 'background']);
        Route::get('background/{id}', [App\Http\Controllers\BackgroundController::class, 'updateBackground']);
        Route::get('stats', [App\Http\Controllers\LivretViewController::class, 'stats']);
        Route::post('statsBetweenDates', [App\Http\Controllers\LivretViewController::class, 'statsBetweenDates']);

        /* Livret Module */
        Route::get('get_all_livret_modules', [App\Http\Controllers\LivretController::class, 'getAllLivretModules']);
        /* Wifi */
        Route::post('module/wifi', [App\Http\Controllers\ModulesController\ModuleWifiController::class, 'addModuleWifi']);
        Route::delete('module/wifi/{id}', [App\Http\Controllers\ModulesController\ModuleWifiController::class, 'deleteModuleWifi']);

        /* Digicode */
        Route::post('module/digicode', [App\Http\Controllers\ModulesController\ModuleDigicodeController::class, 'addModuleDigicode']);
        Route::delete('module/digicode/{id}', [App\Http\Controllers\ModulesController\ModuleDigicodeController::class, 'deleteModuleDigicode']);

        /* Utils phone */
        Route::post('module/utils_phone', [App\Http\Controllers\ModulesController\ModuleUtilsPhoneController::class, 'addModuleUtilsPhone']);
        Route::delete('module/utils_phone/{id}', [App\Http\Controllers\ModulesController\ModuleUtilsPhoneController::class, 'deleteModuleUtilsPhone']);

        /* Utils infos */
        Route::post('module/utils_infos', [App\Http\Controllers\ModulesController\ModuleUtilsInfoController::class, 'addModuleUtilsInfos']);
        Route::delete('module/utils_infos/{id}', [App\Http\Controllers\ModulesController\ModuleUtilsInfoController::class, 'deleteModuleUtilsInfos']);

        /* Start info */
        Route::post('module/start_info', [App\Http\Controllers\ModulesController\ModuleStartInfoController::class, 'addModuleStartInfo']);
        Route::delete('module/start_info/{id}', [App\Http\Controllers\ModulesController\ModuleStartInfoController::class, 'deleteModuleStartInfo']);

        /* End info */
        Route::post('module/end_info', [App\Http\Controllers\ModulesController\ModuleEndInfoController::class, 'addModuleEndInfo']);
        Route::delete('module/end_info/{id}', [App\Http\Controllers\ModulesController\ModuleEndInfoController::class, 'deleteModuleEndInfo']);

        /* Home Infos */
        Route::post('module/home_infos', [App\Http\Controllers\ModulesController\ModuleHomeInfoController::class, 'addModuleHomeInfos']);

        /* Places groups */
        Route::get('module/places_groups', [App\Http\Controllers\ModulesController\ModulePlaceGroupController::class, 'getPlacesGroups']);
        Route::post('module/places_groups', [App\Http\Controllers\ModulesController\ModulePlaceGroupController::class, 'addModulePlacesGroups']);
        Route::delete('module/places_groups/{id}', [App\Http\Controllers\ModulesController\ModulePlaceGroupController::class, 'deleteModulePlacesGroups']);

        /* Nearby Places */
        Route::post('module/nearby_places', [App\Http\Controllers\ModulesController\ModuleNearbyPlaceController::class, 'addModuleNearbyPlaces']);
        Route::delete('module/nearby_places/{id}', [App\Http\Controllers\ModulesController\ModuleNearbyPlaceController::class, 'deleteModuleNearbyPlaces']);
        /* Module order */
        Route::post('/update-order', [App\Http\Controllers\ModulesController\ModuleController::class, 'updateOrder']);

        /* Inventories */
        Route::get('inventories', [App\Http\Controllers\InventoryController::class, 'inventories']);
        Route::post('inventories', [App\Http\Controllers\InventoryController::class, 'addInventory']);
        Route::post('inventories/status', [App\Http\Controllers\InventoryController::class, 'statusInventory']);
        Route::delete('inventories/{id}', [App\Http\Controllers\InventoryController::class, 'deleteInventory']);
        Route::post('inventories/search', [App\Http\Controllers\InventoryController::class, 'searchInventories']);

        /* Contact */
        Route::post('module/contact', [App\Http\Controllers\DashboardController::class, 'contactSupport']);

        /* Suggestions */
        Route::get('suggestions', [App\Http\Controllers\SuggestionController::class, 'suggestions']);
        Route::get('suggestion/enable/{id}', [App\Http\Controllers\SuggestionController::class, 'enableSuggestion']);
        Route::post('suggestion/status', [App\Http\Controllers\SuggestionController::class, 'statusSuggestion']);
        Route::post('suggestion/search', [App\Http\Controllers\SuggestionController::class, 'searchSuggestions']);

        /* Products */
        Route::get('products', [App\Http\Controllers\ProductController::class, 'products']);
        /*    Route::get('products/search', [App\Http\Controllers\DashboardController::class, 'searchProducts']);*/

        /* Text design */
        Route::post('/update-text-design', [App\Http\Controllers\LivretController::class, 'updateTextDesign']);

        /* PDF Export */
        // Route::post('datas/export', [App\Http\Controllers\DashboardController::class, 'exportDatas']);
    });

    Route::post('/stripe-intent', [App\Http\Services\StripeService::class, 'stripeIntent']);

    Route::post('/send-confirmation-email', [App\Http\Controllers\OrderController::class, 'sendConfirmationEmail']);
});
