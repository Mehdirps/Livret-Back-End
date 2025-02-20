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
            $moduleName = $item['type']['name'];

            if ($moduleName == 'wifi') {
                $modules = $livret->wifi;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'digicode') {
                $modules = $livret->digicode;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'endInfos') {
                $modules = $livret->endInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'utilsPhone') {
                $modules = $livret->utilsPhone;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'startInfos') {
                $modules = $livret->startInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'utilsInfos') {
                $modules = $livret->utilsInfos;
                foreach ($modules as $module) {
                    $module->order = $index;
                    $module->save();
                }
            } elseif ($moduleName == 'nearbyPlaces') {
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
