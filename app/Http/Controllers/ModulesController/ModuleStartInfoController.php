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
use App\Models\ModuleStartInfos;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class ModuleStartInfoController extends Controller
{
    public function addModuleStartInfo(Request $request)
    {
        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = JWTAuth::parseToken()->authenticate()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        if(!$request->name || !$request->text){
            return response()->json(['error' => 'Veuillez remplir tous les champs']);
        }

        $startInfo = new ModuleStartInfos();
        $startInfo->name = $request->name;
        $startInfo->text = $request->text;
        $startInfo->livret = $livret->id;
        $startInfo->save();

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
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

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            return response()->json(['message' => 'L\'info d\'arrivé a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre information d\'arrivée a été supprimé avec succès']);
    }
}
