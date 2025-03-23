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
use App\Models\PlaceGroup;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class ModulePlaceGroupController extends Controller
{
    public function addModulePlacesGroups(Request $request)
    {
        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = JWTAuth::parseToken()->authenticate()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        if(!$request->groupName){
            return response()->json(['error' => 'Veuillez remplir tous les champs']);
        }

        $placeGroup = new PlaceGroup();
        $placeGroup->name = $request->groupName;
        $placeGroup->livret_id = $livret->id;
        $placeGroup->save();

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            return response()->json(['message' => 'Le groupe de lieu a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre groupe a été supprimé avec succès']);
    }

    public function getPlacesGroups(Request $request)
    {
        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = JWTAuth::parseToken()->authenticate()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $placeGroups = PlaceGroup::where('livret_id', $livret->id)->get();

        return response()->json(["placeGroups" => $placeGroups]);
    }
}
