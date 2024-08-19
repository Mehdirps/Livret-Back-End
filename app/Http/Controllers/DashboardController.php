<?php

namespace App\Http\Controllers;

use App\Http\Requests\LivretRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Background;
use App\Models\BackgroundGroup;
use App\Models\Inventory;
use App\Models\Livret;
use App\Models\LivretView;
use App\Models\ModuleDigicode;
use App\Models\ModuleEndInfos;
use App\Models\ModuleHome;
use App\Models\ModuleStartInfos;
use App\Models\ModuleUtilsInfos;
use App\Models\ModuleUtilsPhone;
use App\Models\ModuleWifi;
use App\Models\NearbyPlace;
use App\Models\Order;
use App\Models\PlaceGroup;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Suggest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Models\User;
use PDF;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

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

    public function updateUser(UpdateUserRequest $request)
    {

        $validedData = $request->validated();

        if ($validedData['admin_update']) {
            $user = User::find($request->admin_update);

            if (!$user) {
                return response()->json(['error' => 'Utilisateur introuvable']);
            }
        } else {
            $user = JWTAuth::parseToken()->authenticate();
        }

        $user->civility = $validedData['civility'];
        $user->name = $validedData['name'];
        $user->phone = $validedData['phone'];
        $user->birth_date = $validedData['birth_date'];
        $user->address = $validedData['address'];

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'mimes:png,jpg,jpeg,webp',
            ]);

            $validatedData = $request->validate([
                'avatar' => 'mimes:png,jpg,jpeg,webp',
            ]);

            $avatar = $validatedData['avatar'];
            $filename = time() . '.' . $avatar->getClientOriginalExtension();

            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            $avatar->move(public_path('assets/uploads/avatars'), $filename);
            $user->avatar = 'assets/uploads/avatars/' . $filename;
        }

        $user->save();

        if ($validedData['admin_update']) {
            return response()->json(['message' => 'Utilisateur mis à jour avec succès', 'user' => $user]);
        } else {
            return response()->json(['message' => 'Votre profil a été mis à jour avec succès', 'user' => $user]);
        }
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

    public function updateLivret(LivretRequest $request)
    {

        $validatedData = $request->validated();

        if (isset($validatedData['livret_id'])) {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = Livret::where('user_id', JWTAuth::parseToken()->authenticate()->id)->first();
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $livret->livret_name = $validatedData['livret_name'];
        $livret->slug = \Str::slug($validatedData['livret_name']);
        $livret->description = $validatedData['description'];
        $livret->establishment_type = $validatedData['establishment_type'];
        $livret->establishment_name = $validatedData['establishment_name'];
        $livret->establishment_address = $validatedData['establishment_address'];
        $livret->establishment_phone = $validatedData['establishment_phone'];
        $livret->establishment_email = $validatedData['establishment_email'];
        $livret->establishment_website = $validatedData['establishment_website'];
        $livret->facebook = $validatedData['facebook'];
        $livret->twitter = $validatedData['twitter'];
        $livret->instagram = $validatedData['instagram'];
        $livret->linkedin = $validatedData['linkedin'];
        $livret->tripadvisor = $validatedData['tripadvisor'];

        if ($request->hasFile('logo')) {

            $validatedDataLogo = $request->validate([
                'logo' => 'mimes:png,jpg,jpeg,webp',
            ]);

            $logo = $validatedDataLogo['logo'];
            $filename = time() . '.' . $logo->getClientOriginalExtension();

            if ($livret->logo && file_exists(public_path($livret->logo))) {
                unlink(public_path($livret->logo));
            }

            $logo->move(public_path('assets/uploads/logos'), $filename);
            $livret->logo = 'assets/uploads/logos/' . $filename;
        }

        $livret->save();

        if (isset($validatedData['livret_id'])) {
            return response()->json(['message' => 'Livret mis à jour avec succès', 'livret' => $livret]);
        } else {
            return response()->json(['message' => 'Votre livret a été mis à jour avec succès', 'livret' => $livret]);
        }
    }

    public function background()
    {
        $background_groups = BackgroundGroup::all();

        if (!$background_groups) {
            return response()->json(['error' => 'Groupe d\'arrière-plan introuvable']);
        }

        return response()->json([
            'background_groups' => $background_groups,
            'backgrounds' => Background::all(),
        ]);
    }

    public function updateBackground($id)
    {
        $background = Background::find($id);

        if (!$background) {
            return response()->json(['error' => 'Arrière-plan introuvable']);
        }

        $livret = JWTAuth::parseToken()->authenticate()->livret;
        $livret->background = $background->path;
        $livret->save();

        return response()->json(['message' => 'Arrière-plan mis à jour avec succès', 'livret' => $livret]);
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

    public function addModuleWifi(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }


        $wifi = new ModuleWifi();
        $wifi->ssid = $request->wifiName;
        $wifi->password = $request->wifiPassword;
        $wifi->livret = $livret->id;
        $wifi->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Votre réseau wifi a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre réseau wifi a été mis à jour avec succès']);
    }

    public function deleteModuleWifi($id)
    {
        $wifi = ModuleWifi::find($id);

        if (!$wifi) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $wifi->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Votre réseau wifi a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre réseau wifi a été supprimé avec succès']);
    }

    public function addModuleDigicode(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $digicode = new ModuleDigicode();
        $digicode->name = $request->name;
        $digicode->code = $request->code;
        $digicode->livret = $livret->id;
        $digicode->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le digicode a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre digicode a été mis à jour avec succès']);
    }

    public function deleteModuleDigicode($id)
    {
        $digicode = ModuleDigicode::find($id);

        if (!$digicode) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $digicode->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le digicode a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre digicode a été supprimé avec succès']);
    }

    public function addModuleUtilsPhone(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $utilsPhone = new ModuleUtilsPhone();
        $utilsPhone->name = $request->name;
        $utilsPhone->number = $request->number;
        $utilsPhone->livret = $livret->id;
        $utilsPhone->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le numéro utile a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre numéro de téléphone a été mis à jour avec succès']);
    }

    public function deleteModuleUtilsPhone($id)
    {
        $utilsPhone = ModuleUtilsPhone::find($id);

        if (!$utilsPhone) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $utilsPhone->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le numéro utile a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre numéro de téléphone a été supprimé avec succès']);
    }

    public function addModuleUtilsInfos(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $utilsInfos = new ModuleUtilsInfos();
        $utilsInfos->name = 'Infos pratiques';
        $utilsInfos->sub_name = $request->sub_name;
        $utilsInfos->text = $request->text;
        $utilsInfos->livret = $livret->id;
        $utilsInfos->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'L\'info utile a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre information pratique a été mis à jour avec succès']);
    }

    public function deleteModuleUtilsInfos($id)
    {
        $utilsInfos = ModuleUtilsInfos::find($id);

        if (!$utilsInfos) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $utilsInfos->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'L\'info utile a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre information pratique a été supprimé avec succès']);
    }

    public function addModuleStartInfo(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $startInfo = new ModuleStartInfos();
        $startInfo->name = $request->name;
        $startInfo->text = $request->text;
        $startInfo->livret = $livret->id;
        $startInfo->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'L\'info d\'arrivé a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre information d\'arrivée a été mis à jour avec succès']);
    }

    public function deleteModuleStartInfo($id)
    {
        $startInfo = ModuleStartInfos::find($id);

        if (!$startInfo) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $startInfo->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'L\'info d\'arrivé a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre information d\'arrivée a été supprimé avec succès']);
    }

    public function addModuleEndInfo(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $startInfo = new ModuleEndInfos();
        $startInfo->name = $request->name;
        $startInfo->text = $request->text;
        $startInfo->livret = $livret->id;
        $startInfo->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'L\'info de départ a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre information de départ a été mis à jour avec succès']);
    }

    public function deleteModuleEndInfo($id)
    {
        $startInfo = ModuleEndInfos::find($id);

        if (!$startInfo) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $startInfo->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'L\'info de départ a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre information de départ a été supprimé avec succès']);
    }

    public function addModuleHomeInfos(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        if ($livret->homeInfos) {
            $homeInfos = $livret->homeInfos;
            $homeInfos->name = $request->name;
            $homeInfos->text = $request->text;
            $homeInfos->save();
        } else {
            $homeInfos = new ModuleHome();
            $homeInfos->name = $request->name;
            $homeInfos->text = $request->text;
            $homeInfos->livret = $livret->id;
            $homeInfos->save();
        }

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'L\'info de départ a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre information de départ a été mis à jour avec succès']);
    }

    /*   public function deleteModuleHomeInfos($id)
       {
           $homeInfos = ModuleHome::find($id);
           $homeInfos->delete();

           if(auth()->user()->role == 'admin'){
               return redirect()->route('admin.livrets.index')->with('success', 'Votre réseau wifi a été supprimé avec succès');
           }

           return redirect()->route('dashboard.edit_livret')->with('success', 'Votre de départ information a été supprimé avec succès');
       }*/

    public function stats(Request $request)
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $totalViews = LivretView::where('livret_id', $livret->id)->count();

        $viewsThisWeek = LivretView::where('livret_id', $livret->id)
            ->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $viewsToday = LivretView::where('livret_id', $livret->id)
            ->whereDate('viewed_at', today())
            ->count();

        $viewsThisMonth = LivretView::where('livret_id', $livret->id)
            ->whereBetween('viewed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();


        return response()->json([
            'totalViews' => $totalViews,
            'viewsThisWeek' => $viewsThisWeek,
            'viewsToday' => $viewsToday,
            'viewsThisMonth' => $viewsThisMonth,
        ]);
    }

    public function statsBetweenDates(Request $request)
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        $validatedData = $request->validate([
            'start_date' => 'required|string',
            'end_date' => 'required|string',
        ]);

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $startDate = $validatedData['start_date'];
        $endDate = $validatedData['end_date'];

        $viewsBetweenDates = null;
        if ($startDate && $endDate) {
            $endDate = $endDate . ' 23:59:59';
            $viewsBetweenDates = LivretView::where('livret_id', $livret->id)
                ->whereBetween('viewed_at', [$startDate, $endDate])
                ->count();
        }

        return response()->json([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'viewsBetweenDates' => $viewsBetweenDates,
        ]);
    }

    public function addModulePlacesGroups(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $placeGroup = new PlaceGroup();
        $placeGroup->name = $request->groupName;
        $placeGroup->livret_id = $livret->id;
        $placeGroup->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le groupe de lieu a été ajouté avec succès']);
        }

        return response()->json(['message' => 'Votre groupe a été ajouté avec succès']);
    }

    public function deleteModulePlacesGroups($id)
    {
        $placeGroup = PlaceGroup::find($id);

        if (!$placeGroup) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $placeGroup->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le groupe de lieu a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre groupe a été supprimé avec succès']);
    }

    public function addModuleNearbyPlaces(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $nearbyPlace = new NearbyPlace();
        $nearbyPlace->name = $request->placeName;
        $nearbyPlace->address = $request->placeAddress;
        $nearbyPlace->phone = $request->placePhone;
        $nearbyPlace->description = $request->placeDescription;
        $nearbyPlace->place_group_id = $request->placeGroup;
        $nearbyPlace->travel_time = $request->travelTime;
        $nearbyPlace->livret_id = $livret->id;
        $nearbyPlace->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le lieu a été ajouté avec succès']);
        }

        return response()->json(['message' => 'Votre lieu a été ajouté avec succès']);
    }

    public function deleteModuleNearbyPlaces($id)
    {
        $nearbyPlace = NearbyPlace::find($id);

        if (!$nearbyPlace) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $nearbyPlace->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le lieu a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre lieu a été supprimé avec succès']);
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

    public function inventories()
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $inventories = Inventory::where('livret_id', $livret->id)->get();


        if (!$inventories) {
            return response()->json(['error' => 'Aucun inventaire trouvé']);
        }

        return response()->json([
            'inventories' => $inventories,
        ]);
    }

    public function addInventory(Request $request)
    {
        $validatedData = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'client_name' => 'required|string',
            'status' => 'required|string',
            'client_comment' => 'nullable|string',
            'attachment_names.*' => 'nullable|file|mimes:pdf,png,jpeg,webp,jpg',
        ]);

        $inventory = new Inventory;
        $inventory->livret_id = JWTAuth::parseToken()->authenticate()->livret->id;
        $inventory->start_date = $validatedData['start_date'];
        $inventory->end_date = $validatedData['end_date'];
        $inventory->client_name = $validatedData['client_name'];
        $inventory->status = $validatedData['status'];
        $inventory->client_comment = $validatedData['client_comment'];

        if ($request->hasFile('attachment_names')) {

            $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'pdf', 'xlsx', 'xls', 'doc', 'docx', 'odt', 'ods', 'ppt', 'pptx'];
            $files = $request->file('attachment_names');
            $attachments = [];
            $i = 0;

            foreach ($files as $attachment) {
                $extension = $attachment->getClientOriginalExtension();
                if (in_array(strtolower($extension), $allowedExtensions)) {
                    $filename = $i . time() . '.' . $extension;
                    $attachment->move(public_path('assets/uploads/inventory_attachments'), $filename);
                    $attachments[] = 'assets/uploads/inventory_attachments/' . $filename;
                    $i++;
                } else {
                    return response()->json(['error' => 'Les fichiers doivent être de type png, jpg, jpeg, webp, pdf, xlsx, xls, doc, docx, odt, ods, ppt, pptx']);
                }
            }

            $inventory->attachment_names = json_encode($attachments);
        }


        $inventory->save();

        return response()->json(['message' => 'L\'état des lieux a été ajouté avec succès']);
    }

    public function statusInventory(Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required|string',
            'inventory_id' => 'required|integer',
        ]);


        $inventory = Inventory::find($validatedData['inventory_id']);
        $inventory->status = $validatedData['status'];
        $inventory->save();

        return response()->json(['message' => 'Le status de l\'état des lieux a été mis à jour avec succès']);
    }

    public function deleteInventory($id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['error' => 'Inventaire introuvable']);
        }

        $attachments = json_decode($inventory->attachment_names);

        if ($attachments) {
            foreach ($attachments as $attachment) {
                if (file_exists(public_path($attachment))) {
                    unlink(public_path($attachment));
                }
            }
        }

        $inventory->delete();

        return response()->json(['message' => 'L\'état des lieux a été supprimé avec succès']);
    }

    public function searchInventories(Request $request)
    {
        $validatedData = $request->validate([
            'client_name' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|in:in_progress,completed',
        ]);


        $client_name = $validatedData['client_name'];
        $start_date = $validatedData['start_date'];
        $end_date = $validatedData['end_date'];
        $status = $validatedData['status'];

        $query = Inventory::query();

        if ($client_name) {
            $query->where('client_name', 'like', '%' . $client_name . '%');
        }

        if ($start_date) {
            $query->where('start_date', '>=', $start_date);
        }

        if ($end_date) {
            $query->where('end_date', '<=', $end_date);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $inventories = $query->get();

        return response()->json([
            'inventories' => $inventories,
        ]);
    }

    public function suggestions()
    {
        $livret = auth()->user()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $suggestions = $livret->suggestions()->get();

        if (!$suggestions) {
            return response()->json(['error' => 'Aucune suggestion trouvée']);
        }

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    public function enableSuggestion($id)
    {
        $livret = Livret::find($id);
        $livret->suggest = !$livret->suggest;
        $livret->save();

        return response()->json(['message' => 'Les suggestions ont été activées avec succès', 'livret' => $livret]);
    }

    public function statusSuggestion(Request $request)
    {
        $validatedData = $request->validate([
            'status_suggest' => 'required|string|in:pending,accepted,refused',
            'suggestion_id' => 'required|integer',
        ]);

        $suggestion = Suggest::find($validatedData['suggestion_id']);
        $suggestion->status = $validatedData['status_suggest'];
        $suggestion->save();

        return response()->json(['message' => 'Le status de la suggestion a été mis à jour avec succès']);
    }

    public function searchSuggestions(Request $request)
    {

        $validator = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'title' => 'nullable|string',
            'message' => 'nullable|string',
            'status' => 'nullable|string|in:all,pending,accepted,refused',
        ]);

        $validatedData = $validator;

        $name = $validatedData['name'];
        $email = $validatedData['email'];
        $title = $validatedData['title'];
        $message = $validatedData['message'];
        $status = $validatedData['status'];

        $query = Suggest::query();

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if ($email) {
            $query->where('email', 'like', '%' . $email . '%');
        }

        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        if ($message) {
            $query->where('message', 'like', '%' . $message . '%');
        }

        if ($status && $status != 'all') {
            $query->where('status', $status);
        }

        $suggestions = $query->get();

        return response()->json([
            /*'livret' => JWTAuth::parseToken()->authenticate()->livret,*/
            'suggestions' => $suggestions,
        ]);
    }

    public function products()
    {
        $categories = ProductCategory::with('products')->get();

        if (!$categories) {
            return response()->json(['error' => 'Aucun produit trouvé']);
        }

        return response()->json([
            'categories' => $categories,
        ]);
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

    public function updateOrder(Request $request)
    {
        $order = $request->input('order');
        $livret = auth()->user()->livret;

        foreach ($order as $item) {
            $index = $item['order'];
            $moduleName = $item['module'];

            if ($moduleName == 'Wifi') {
                $modules = $livret->wifi;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Digicode') {
                $modules = $livret->digicode;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Infos de départ') {
                $modules = $livret->endInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Numéros utiles') {
                $modules = $livret->utilsPhone;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Infos de d\'arrivée') {
                $modules = $livret->startInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Infos utiles') {
                $modules = $livret->utilsInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Lieux à proximité') {
                $modules = $livret->nearbyPlaces;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function updateTextDesign(Request $request)
    {
        $livret = auth()->user()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $livret->font = $request->input('fontFamily');
        $livret->text_color = $request->input('fontColor');

        $livret->save();

        return response()->json(['message' => 'Le design du texte a été mis à jour avec succès']);
    }

    public function exportDatas(Request $request)
    {
        $data = $request->input('data');

        $type = $request->input('type');

        if ($type == 'suggestions') {
            $pdf = PDF::loadView('dashboard.partials.suggestions_pdf', ['data' => $data]);
        } elseif ($type == 'inventories') {
            $pdf = PDF::loadView('dashboard.partials.inventories_pdf', ['data' => $data]);
        } elseif ($type == 'stats') {
            $pdf = PDF::loadView('dashboard.partials.stats_pdf', ['data' => $data]);
        }

        $output = $pdf->output();

        return response()->json([
            'status' => 'success',
            'pdf_base64' => base64_encode($output)
        ]);
    }

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

}
