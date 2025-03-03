<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\Info(title="Mon API", version="1.0")
 * 
 * @OA\Get(
 *     path="/api",
 *     tags={"Welcome"},
 *     summary="Message de bienvenue",
 *     @OA\Response(
 *         response=200,
 *         description="Message de bienvenue",
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
