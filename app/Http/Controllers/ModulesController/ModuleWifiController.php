<?php

namespace App\Http\Controllers\ModulesController;

use App\Models\Livret;
use App\Models\ModuleWifi;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class ModuleWifiController extends Controller
{
    public function addModuleWifi(Request $request)
    {
        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = JWTAuth::parseToken()->authenticate()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        if(!$request->wifiName || !$request->wifiPassword){
            return response()->json(['error' => 'Veuillez remplir tous les champs']);
        }

        $wifi = new ModuleWifi();
        $wifi->ssid = $request->wifiName;
        $wifi->password = $request->wifiPassword;
        $wifi->livret = $livret->id;
        $wifi->save();

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            return response()->json(['message' => 'Votre réseau wifi a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre réseau wifi a été supprimé avec succès']);
    }
}
