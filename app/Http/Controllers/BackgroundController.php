<?php

namespace App\Http\Controllers;

use App\Models\Background;
use App\Models\BackgroundGroup;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class BackgroundController extends Controller
{
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
}
