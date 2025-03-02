<?php

namespace App\Http\Controllers;

use App\Http\Requests\LivretRequest;
use App\Models\Livret;
use App\Models\LivretView;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class LivretController
 *
 * Contrôleur responsable de la gestion des livrets. Permet de créer, afficher, 
 * mettre à jour les livrets et gérer leur design ainsi que les modules associés.
 *
 * @package App\Http\Controllers
 */
class LivretController extends Controller
{
    /**
     * Store a newly created livret in the database.
     *
     * Cette méthode permet de créer un nouveau livret en fonction des données 
     * validées dans la requête.
     *
     * @param LivretRequest $request La requête contenant les données du livret.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(LivretRequest $request)
    {
        $validatedData = $request->validated();

        $user = JWTAuth::parseToken()->authenticate();

        $user_id = $user->id;

        Livret::create([
            'livret_name' => $validatedData['livret_name'],
            'slug' => \Str::slug($validatedData['livret_name']),
            'establishment_type' => $validatedData['establishment_type'],
            'establishment_name' => $validatedData['establishment_name'],
            'establishment_address' => $validatedData['establishment_address'],
            'establishment_phone' => $validatedData['establishment_phone'],
            'establishment_email' => $validatedData['establishment_email'],
            'establishment_website' => $validatedData['establishment_website'],
            'user_id' => $user_id,
        ]);

        $user = User::find($user_id);
        $user->first_login = 0;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Livret créé avec succès.'], 201);
    }

    /**
     * Affiche un livret spécifique selon son slug et son ID.
     *
     * Cette méthode retourne un livret et ses modules associés en fonction de 
     * l'ID et du slug passés en paramètre.
     *
     * @param string $slug Le slug du livret.
     * @param int $id L'ID du livret.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug, $id)
    {
        $livret = Livret::where('id', $id)->where('slug', $slug)->first();

        $modules = [
            'wifi' => [
                'data' => $livret->wifi,
                'order' => $livret->wifi[0]->order ?? null
            ],
            'digicode' => [
                'data' => $livret->digicode,
                'order' => $livret->digicode[0]->order ?? null
            ],
            'endInfos' => [
                'data' => $livret->endInfos,
                'order' => $livret->endInfos[0]->order ?? null
            ],
            'homeInfos' => [
                'data' => $livret->homeInfos,
                'order' => null
            ],
            'utilsPhone' => [
                'data' => $livret->utilsPhone,
                'order' => $livret->utilsPhone[0]->order ?? null
            ],
            'startInfos' => [
                'data' => $livret->startInfos,
                'order' => $livret->startInfos[0]->order ?? null
            ],
            'utilsInfos' => [
                'data' => $livret->utilsInfos,
                'order' => $livret->utilsInfos[0]->order ?? null
            ],
            'placeGroups' => [
                'data' => $livret->placeGroups,
                'order' => null
            ],
            'NearbyPlaces' => [
                'data' => $livret->NearbyPlaces,
                'order' => $livret->NearbyPlaces[0]->order ?? null
            ],
        ];

        if ($livret) {
            LivretView::create([
                'livret_id' => $livret->id,
                'viewed_at' => now(),
            ]);
            return response()->json([
                'livret' => $livret,
                'modules' => $modules,
            ], 200);
        } else {
            return response()->json(['error' => 'Ce livret n\'existe pas.'], 404);
        }
    }

    /**
     * Met à jour un livret existant.
     *
     * Cette méthode permet de mettre à jour un livret avec les nouvelles données
     * envoyées dans la requête. Elle prend en compte la mise à jour du logo si
     * un fichier est envoyé.
     *
     * @param LivretRequest $request La requête contenant les données à mettre à jour.
     * @return \Illuminate\Http\JsonResponse
     */
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
            return response()->json(['message' => 'Votre livret a été mis à jour avec succès', 'livret' => $livret], 200);
        }
    }

    /**
     * Met à jour le design du texte d'un livret.
     *
     * Cette méthode permet de changer la famille de police et la couleur du texte
     * pour un livret donné. Les changements sont appliqués à l'utilisateur actuel.
     *
     * @param Request $request La requête contenant les données du design du texte.
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTextDesign(Request $request)
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $validatedData = $request->validate([
            'fontFamily' => 'required|string',
            'fontColor' => 'required|string',
        ]);

        $livret->font = $validatedData['fontFamily'];
        $livret->text_color = $validatedData['fontColor'];

        $livret->save();

        return response()->json(['message' => 'Le design du texte a été mis à jour avec succès', 'livret' => $livret]);
    }

    /**
     * Retourne tous les modules d'un livret pour l'utilisateur authentifié.
     *
     * Cette méthode renvoie tous les modules (comme Wi-Fi, Infos pratiques, etc.) 
     * associés au livret de l'utilisateur.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllLivretModules()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $livret = $user->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $modules = [];

        if ($livret->endInfos) {
            $modules[] = [
                'type' => ['name' => 'endInfos', 'title' => 'Infos de départ'],
                'icon' => 'bi bi-arrow-down-left',
                'data' => $livret->endInfos,
                'order' => $livret->endInfos[0]->order ?? null
            ];
        }
        if ($livret->digicode) {
            $modules[] = [
                'type' => ['name' => 'digicode', 'title' => 'Digicode'],
                'icon' => 'bi bi-key',
                'data' => $livret->digicode,
                'order' => $livret->digicode[0]->order ?? null
            ];
        }
        if ($livret->wifi) {
            $modules[] = [
                'type' => ['name' => 'wifi', 'title' => 'Informations Wi-Fi'],
                'icon' => 'bi bi-wifi',
                'data' => $livret->wifi,
                'order' => $livret->wifi[0]->order ?? null
            ];
        }
        if ($livret->homeInfos) {
            $modules[] = [
                'type' => ['name' => 'homeInfos', 'title' => 'Mot d\'accueil'],
                'icon' => 'bi bi-envelope',
                'data' => $livret->homeInfos,
                'order' => null
            ];
        }
        if ($livret->utilsPhone) {
            $modules[] = [
                'type' => ['name' => 'utilsPhone', 'title' => 'Numéros utiles'],
                'icon' => 'bi bi-telephone',
                'data' => $livret->utilsPhone,
                'order' => $livret->utilsPhone[0]->order ?? null
            ];
        }
        if ($livret->startInfos) {
            $modules[] = [
                'type' => ['name' => 'startInfos', 'title' => 'Infos d\'arrivée'],
                'icon' => 'bi bi-arrow-up-right',
                'data' => $livret->startInfos,
                'order' => $livret->startInfos[0]->order ?? null
            ];
        }
        if ($livret->utilsInfos) {
            $modules[] = [
                'type' => ['name' => 'utilsInfos', 'title' => 'Infos pratiques'],
                'icon' => 'bi bi-info-circle',
                'data' => $livret->utilsInfos,
                'order' => $livret->utilsInfos[0]->order ?? null
            ];
        }

        return response()->json([
            'modules' => $modules,
        ]);
    }
}
