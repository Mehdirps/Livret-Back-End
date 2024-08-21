<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function updateUser(UpdateUserRequest $request)
    {

        $validedData = $request->validated();

        if ($validedData['admin_update']) {
            $user = User::find($request->admin_update);

            if (!$user) {
                return response()->json(['error' => 'Utilisateur introuvable']);
            }
        } else {
            $user = JWTAuth::parseToken()->authenticate();
        }

        $user->civility = $validedData['civility'];
        $user->name = $validedData['name'];
        $user->phone = $validedData['phone'];
        $user->birth_date = $validedData['birth_date'];
        $user->address = $validedData['address'];

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'mimes:png,jpg,jpeg,webp',
            ]);

            $validatedData = $request->validate([
                'avatar' => 'mimes:png,jpg,jpeg,webp',
            ]);

            $avatar = $validatedData['avatar'];
            $filename = time() . '.' . $avatar->getClientOriginalExtension();

            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            $avatar->move(public_path('assets/uploads/avatars'), $filename);
            $user->avatar = 'assets/uploads/avatars/' . $filename;
        }

        $user->save();

        if ($validedData['admin_update']) {
            return response()->json(['message' => 'Utilisateur mis à jour avec succès', 'user' => $user]);
        } else {
            return response()->json(['message' => 'Votre profil a été mis à jour avec succès', 'user' => $user]);
        }
    }
}
