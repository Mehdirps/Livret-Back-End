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
            'message' => 'Bienvenu sur l\'API de l\'application Livret',
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
        // Route::get('edit_livret', [App\Http\Controllers\DashboardController::class, 'editLivret']);
        Route::get('stats', [App\Http\Controllers\LivretViewController::class, 'stats']);
        Route::post('statsBetweenDates', [App\Http\Controllers\LivretViewController::class, 'statsBetweenDates']);

        /* Livret Module */
        /* Wifi */
        Route::post('module/wifi', [App\Http\Controllers\ModuleWifiController::class, 'addModuleWifi']);
        Route::get('module/wifi/{id}', [App\Http\Controllers\ModuleWifiController::class, 'deleteModuleWifi']);

        /* Digicode */
        Route::post('module/digicode', [App\Http\Controllers\ModuleDigicodeController::class, 'addModuleDigicode']);
        Route::get('module/digicode/{id}', [App\Http\Controllers\ModuleDigicodeController::class, 'deleteModuleDigicode']);

        /* Utils phone */
        Route::post('module/utils_phone', [App\Http\Controllers\ModuleUtilsPhoneController::class, 'addModuleUtilsPhone']);
        Route::get('module/utils_phone/{id}', [App\Http\Controllers\ModuleUtilsPhoneController::class, 'deleteModuleUtilsPhone']);

        /* Utils infos */
        Route::post('module/utils_infos', [App\Http\Controllers\ModuleUtilsInfoController::class, 'addModuleUtilsInfos']);
        Route::get('module/utils_infos/{id}', [App\Http\Controllers\ModuleUtilsInfoController::class, 'deleteModuleUtilsInfos']);

        /* Start info */
        Route::post('module/start_info', [App\Http\Controllers\ModuleStartInfoController::class, 'addModuleStartInfo']);
        Route::get('module/start_info/{id}', [App\Http\Controllers\ModuleStartInfoController::class, 'deleteModuleStartInfo']);

        /* End info */
        Route::post('module/end_info', [App\Http\Controllers\ModuleEndInfoController::class, 'addModuleEndInfo']);
        Route::get('module/end_info/{id}', [App\Http\Controllers\ModuleEndInfoController::class, 'deleteModuleEndInfo']);

        /* Home Infos */
        Route::post('module/home_infos', [App\Http\Controllers\ModuleHomeInfoController::class, 'addModuleHomeInfos']);

        /* Places groups */
        Route::post('module/places_groups', [App\Http\Controllers\ModulePlaceGroupController::class, 'addModulePlacesGroups']);
        Route::get('module/places_groups/{id}', [App\Http\Controllers\ModulePlaceGroupController::class, 'deleteModulePlacesGroups']);

        /* Nearby Places */
        Route::post('module/nearby_places', [App\Http\Controllers\ModuleNearbyPlaceController::class, 'addModuleNearbyPlaces']);
        Route::get('module/nearby_places/{id}', [App\Http\Controllers\ModuleNearbyPlaceController::class, 'deleteModuleNearbyPlaces']);

        /* Module order */
        Route::post('/update-order', [App\Http\Controllers\ModuleController::class, 'updateOrder']);

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

    Route::post('/stripe-intent', [App\Http\Service\StripeService::class, 'stripeIntent']);

    Route::post('/send-confirmation-email', [App\Http\Controllers\OrderController::class, 'sendConfirmationEmail']);
});
