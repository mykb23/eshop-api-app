<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    version="1.0.0",
 *    title="E-Commerce API Documentation",
 *    description="API support for a E-Commerce",
 *    @OA\Contact(
 *        email=""
 *    ),
 *    @OA\License(
 *        name="Apache License 2.0",
 *        url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *   )
 * )
 */


/**
 * @OA\SecurityScheme(
 *     scheme="Bearer",
 *     securityScheme="Bearer",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
