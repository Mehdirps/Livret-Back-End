<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\ModuleStartInfos;
use Illuminate\Http\Request;

class ModuleStartInfoController extends Controller
{
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
}
