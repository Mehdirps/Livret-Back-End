<?php
/**
 * Copyright (c) 2025 Mehdi Raposo
 * Ce fichier fait partie du projet Heberginfos.
 *
 * Ce fichier, ainsi que tout le code et les ressources qu'il contient,
 * est protégé par le droit d'auteur. Toute utilisation, modification,
 * distribution ou reproduction non autorisée est strictement interdite
 * sans une autorisation écrite préalable de Mehdi Raposo.
 */

namespace App\Http\Controllers\ModulesController;

use App\Models\Livret;
use App\Models\NearbyPlace;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class ModuleNearbyPlaceController extends Controller
{
    public function addModuleNearbyPlaces(Request $request)
    {
        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = JWTAuth::parseToken()->authenticate()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        if(!$request->placeName || !$request->placeGroup){
            return response()->json(['error' => 'Veuillez remplir les champs obligatoires, nom du lieu et groupe de lieu']);
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            return response()->json(['message' => 'Le lieu a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre lieu a été supprimé avec succès']);
    }
}
