<?php

namespace App\Http\Controllers\ModulesController;

use App\Models\Livret;
use App\Models\ModuleUtilsPhone;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class ModuleUtilsPhoneController extends Controller
{
    public function addModuleUtilsPhone(Request $request)
    {
        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = JWTAuth::parseToken()->authenticate()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        if(!$request->name || !$request->number){
            return response()->json(['error' => 'Veuillez remplir tous les champs']);
        }

        $utilsPhone = new ModuleUtilsPhone();
        $utilsPhone->name = $request->name;
        $utilsPhone->number = $request->number;
        $utilsPhone->livret = $livret->id;
        $utilsPhone->save();

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            return response()->json(['message' => 'Le numéro utile a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre numéro de téléphone a été supprimé avec succès']);
    }
}
