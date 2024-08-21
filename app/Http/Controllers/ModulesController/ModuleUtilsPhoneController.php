<?php

namespace App\Http\Controllers;

use App\Models\Livret;
use App\Models\ModuleUtilsPhone;
use Illuminate\Http\Request;

class ModuleUtilsPhoneController extends Controller
{
    public function addModuleUtilsPhone(Request $request)
    {
        if (auth()->user()->role == 'admin') {
            $livret = Livret::find($request->livret_id);
        } else {
            $livret = auth()->user()->livret;
        }

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $utilsPhone = new ModuleUtilsPhone();
        $utilsPhone->name = $request->name;
        $utilsPhone->number = $request->number;
        $utilsPhone->livret = $livret->id;
        $utilsPhone->save();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le numéro utile a été mis à jour avec succès']);
        }

        return response()->json(['message' => 'Votre numéro de téléphone a été mis à jour avec succès']);
    }

    public function deleteModuleUtilsPhone($id)
    {
        $utilsPhone = ModuleUtilsPhone::find($id);

        if (!$utilsPhone) {
            return response()->json(['error' => 'Module introuvable']);
        }

        $utilsPhone->delete();

        if (auth()->user()->role == 'admin') {
            return response()->json(['message' => 'Le numéro utile a été supprimé avec succès']);
        }

        return response()->json(['message' => 'Votre numéro de téléphone a été supprimé avec succès']);
    }
}
