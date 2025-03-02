<?php

namespace App\Http\Controllers;

use App\Models\Background;
use App\Models\BackgroundGroup;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Contrôleur des arrière-plans.
 *
 * Gère les actions liées aux arrière-plans et aux groupes d'arrière-plans :
 * - Liste des arrière-plans et groupes
 * - Mise à jour de l'arrière-plan d'un livret
 */
class BackgroundController extends Controller
{
    /**
     * Récupère tous les groupes et arrière-plans disponibles.
     *
     * @return \Illuminate\Http\JsonResponse Réponse avec les groupes et arrière-plans en cas de succès.
     */
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

    /**
     * Met à jour l'arrière-plan d'un livret avec l'arrière-plan sélectionné.
     *
     * @param int $id ID de l'arrière-plan sélectionné.
     * @return \Illuminate\Http\JsonResponse Réponse confirmant la mise à jour ou indiquant une erreur.
     */
    public function updateBackground($id)
    {
        $background = Background::find($id);

        if (!$background) {
            return response()->json(['error' => 'Arrière-plan introuvable']);
        }

        $livret = JWTAuth::parseToken()->authenticate()->livret;
        $livret->background = $background->path;
        $livret->save();

        return response()->json([
            'message' => 'Arrière-plan mis à jour avec succès',
            'livret' => $livret,
        ]);
    }
}
