<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\NearbyPlace;
use Illuminate\Http\Request;

class ModuleNearbyPlaceController extends Controller
{
    public function addModuleNearbyPlaces(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $nearbyPlace = new NearbyPlace();
        $nearbyPlace->name = $request->placeName;
        $nearbyPlace->address = $request->placeAddress;
        $nearbyPlace->phone = $request->placePhone;
        $nearbyPlace->description = $request->placeDescription;
        $nearbyPlace->place_group_id = $request->placeGroup;
        $nearbyPlace->travel_time = $request->travelTime;
        $nearbyPlace->livret_id = $livret->id;
        $nearbyPlace->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le lieu a été ajouté avec succès']);
        }

        return response()->json(['message' => 'Votre lieu a été ajouté avec succès']);
    }

    public function deleteModuleNearbyPlaces($id)
    {
        $nearbyPlace = NearbyPlace::find($id);

        if (!$nearbyPlace) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $nearbyPlace->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le lieu a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre lieu a été supprimé avec succès']);
    }
}
