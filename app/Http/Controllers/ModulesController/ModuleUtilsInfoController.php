<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\ModuleUtilsInfos;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ModuleUtilsInfoController extends Controller
{
    public function addModuleUtilsInfos(Request $request)
    {
        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = JWTAuth::parseToken()->authenticate()->livret;
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            return response()->json(['message' => 'L\'info utile a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre information pratique a été supprimé avec succès']);
    }
}
