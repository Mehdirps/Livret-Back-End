<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\ModuleEndInfos;
use Illuminate\Http\Request;

class ModuleEndInfoController extends Controller
{
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
}
