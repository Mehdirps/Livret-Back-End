<?php

namespace App\Http\Controllers;

use App\Http\Requests\LivretRequest;
use App\Models\Livret;
use App\Models\LivretView;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class LivretController extends Controller
{
    public function store(LivretRequest $request)
    {
        $validatedData = $request->validated();

        $user = JWTAuth::parseToken()->authenticate();

        $user_id = $user->id;

        Livret::create([
            'livret_name' => $validatedData['livret_name'],
            'slug' => \Str::slug($validatedData['livret_name']),
            'establishment_type' => $validatedData['establishment_type'],
            'establishment_name' => $validatedData['establishment_name'],
            'establishment_address' => $validatedData['establishment_address'],
            'establishment_phone' => $validatedData['establishment_phone'],
            'establishment_email' => $validatedData['establishment_email'],
            'establishment_website' => $validatedData['establishment_website'],
            'user_id' => $user_id,
        ]);

        $user = User::find($user_id);
        $user->first_login = 0;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Livret créé avec succès.'], 201);
    }

    public function show($slug, $id)
    {
        $livret = Livret::where('id', $id)->where('slug', $slug)->first();

        $modules = [
            'wifi' => $livret->wifi,
            'digicode' => $livret->digicode,
            'endInfos' => $livret->endInfos,
            'homeInfos' => $livret->homeInfos,
            'utilsPhone' => $livret->utilsPhone,
            'startInfos' => $livret->startInfos,
            'utilsInfos' => $livret->utilsInfos,
            'placeGroups' => $livret->placeGroups,
            'NearbyPlaces' => $livret->NearbyPlaces,
        ];

        if ($livret) {
            LivretView::create([
                'livret_id' => $livret->id,
                'viewed_at' => now(),
            ]);
            return response()->json([
                'livret' => $livret,
                'modules' => $modules,
            ], 200);
        } else {
            return response()->json(['error' => 'Ce livret n\'existe pas.'], 404);
        }
    }
}
