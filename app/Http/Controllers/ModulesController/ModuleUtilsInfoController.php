<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\ModuleUtilsInfos;
use Illuminate\Http\Request;

class ModuleUtilsInfoController extends Controller
{
    public function addModuleUtilsInfos(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
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

        if (auth()->user()->role == 'admin') {
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

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'L\'info utile a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre information pratique a été supprimé avec succès']);
    }
}
