<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ModuleController extends Controller
{
    public function updateOrder(Request $request)
    {
        $order = $request->input('order');
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        foreach ($order as $item) {
            $index = $item['order'];
            $moduleName = $item['module'];

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
            } elseif ($moduleName == 'Infos de d\'arrivée') {
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
