<?php

namespace App\Http\Controllers\ModulesController;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class ModuleController extends Controller
{
    public function updateOrder(Request $request)
    {
        $validatedData = $request->validate([
            'order' => 'required|array',
        ]);

        $order = $validatedData['order'];

        $livret = JWTAuth::parseToken()->authenticate()->livret;

        foreach ($order as $item) {
            $index = $item['order'];
            $moduleName = $item['type']['title'];

            if ($moduleName == 'Wifi') {
                $modules = $livret->wifi;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Digicode') {
                $modules = $livret->digicode;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Infos de départ') {
                $modules = $livret->endInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Numéros utiles') {
                $modules = $livret->utilsPhone;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Infos d\'arrivée') {
                $modules = $livret->startInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Infos utiles') {
                $modules = $livret->utilsInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'Lieux à proximité') {
                $modules = $livret->nearbyPlaces;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
