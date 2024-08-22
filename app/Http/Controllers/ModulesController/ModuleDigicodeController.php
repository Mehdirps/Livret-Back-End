<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\ModuleDigicode;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ModuleDigicodeController extends Controller
{
    public function addModuleDigicode(Request $request)
    {
        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = JWTAuth::parseToken()->authenticate()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $digicode = new ModuleDigicode();
        $digicode->name = $request->name;
        $digicode->code = $request->code;
        $digicode->livret = $livret->id;
        $digicode->save();

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            return response()->json(['message' => 'Le digicode a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre digicode a été supprimé avec succès']);
    }
}
