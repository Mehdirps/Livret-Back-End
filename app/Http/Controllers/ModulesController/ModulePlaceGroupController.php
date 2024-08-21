<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\PlaceGroup;
use Illuminate\Http\Request;

class ModulePlaceGroupController extends Controller
{
    public function addModulePlacesGroups(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $placeGroup = new PlaceGroup();
        $placeGroup->name = $request->groupName;
        $placeGroup->livret_id = $livret->id;
        $placeGroup->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le groupe de lieu a été ajouté avec succès']);
        }

        return response()->json(['message' => 'Votre groupe a été ajouté avec succès']);
    }

    public function deleteModulePlacesGroups($id)
    {
        $placeGroup = PlaceGroup::find($id);

        if (!$placeGroup) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $placeGroup->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le groupe de lieu a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre groupe a été supprimé avec succès']);
    }
}
