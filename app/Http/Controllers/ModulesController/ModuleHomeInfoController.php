<?php

namespace App\Http\Controllers\ModulesController;

use App\Models\Livret;
use App\Models\ModuleHome;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class ModuleHomeInfoController extends Controller
{
    public function addModuleHomeInfos(Request $request)
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

        if ($livret->homeInfos) {
            $homeInfos = $livret->homeInfos;
            $homeInfos->name = $request->name;
            $homeInfos->text = $request->text;
            $homeInfos->save();
        } else {
            $homeInfos = new ModuleHome();
            $homeInfos->name = $request->name;
            $homeInfos->text = $request->text;
            $homeInfos->livret = $livret->id;
            $homeInfos->save();
        }

        if (JWTAuth::parseToken()->authenticate()->role == 'admin') {
            return response()->json(['message' => 'L\'info de départ a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre information de départ a été mis à jour avec succès']);
    }


    /*   public function deleteModuleHomeInfos($id)
       {
           $homeInfos = ModuleHome::find($id);
           $homeInfos->delete();

           if(JWTAuth::parseToken()->authenticate()->role == 'admin'){
               return redirect()->route('admin.livrets.index')->with('success', 'Votre réseau wifi a été supprimé avec succès');
           }

           return redirect()->route('dashboard.edit_livret')->with('success', 'Votre de départ information a été supprimé avec succès');
       }*/
}
