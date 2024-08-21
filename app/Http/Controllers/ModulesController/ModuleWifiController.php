<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\ModuleWifi;
use Illuminate\Http\Request;

class ModuleWifiController extends Controller
{
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
}
