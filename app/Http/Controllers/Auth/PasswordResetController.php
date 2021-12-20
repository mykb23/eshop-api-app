<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class PasswordResetController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/v1/password-reset/create",
     *      tags={"Auth"},
     *      summary="Password reset request",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"email","redirect_url"},
     *                  @OA\Property(type="email", title="email", default="john-doe@mail.com", property="email"),
     *                  @OA\Property(type="string", title="redirect_url", example="http://example.com", property="redirect_url"),
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean",title="success",default="true",property="success"),
     *              @OA\Property(type="string",title="message",default="We have e-mailed your password reset link!",property="message"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean",title="success",default="false",property="success"),
     *              @OA\Property(type="string",title="message",default="We can't find a user with that email address.",property="message"),
     *          ),
     *      ),
     * )
     * Create token password reset
     *
     * @param [string] email
     * @return [string] message
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 409);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "We can't find a user with that email address."
            ], 404);
        }

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            ['email' => $user->email, 'token' => Str::random(60),'redirect_url' => $request->redirect_url],
        );

        if ($user && $passwordReset) {
            $user->notify(new PasswordResetRequest($passwordReset->token));
        }

        return response()->json([
            'success' => true,
            'message' => 'We have e-mailed your password reset link!'
        ],201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/password-reset/{token}",
     *      tags={"Auth"},
     *      description="Password reset with token",
     *      @OA\Parameter(
     *          name="token",
     *          in="path",
     *          @OA\Schema(type="string"),
     *          required=true,
     *      ),
     *
     *
     *      @OA\Response(
     *          response=200,
     *          description="Password reset with token",
     *          @OA\JsonContent(
     *              @OA\Property(type="string",title="token",example="cVirkKfR6wg4NPbfjMAdEmzHacztBeP2twIPm6StoTDsvHUWymvcrlJD9cDB",property="token"),
     *              @OA\Property(type="email",title="email",example="example@example.com",property="email"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Password reset with token",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean",title="success",default="false",property="success"),
     *              @OA\Property(type="string",title="message",default="This password reset token is invalid.",property="message"),
     *          ),
     *      )
     * )
     * Find token password reset
     *
     * @param [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     *
     */

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset) {
            return response()->json([
                "success"=>false,
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                "success" => false,
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        if ($passwordReset->redirect_url !== env('FRONTEND_URL')) {
            // return redirect()->away(env('FRONTEND_URL'))->with("token", 'cVirkKfR6wg4NPbfjMAdEmzHacztBeP2twIPm6StoTDsvHUWymvcrlJD9cDB');
            return response()->json($passwordReset);
        }
        // dd($passwordReset);
        // urlencode((string) $token);
        return redirect()->away(env('FRONTEND_URL').'?token='.Crypt::encryptString($token).'&?email='.Crypt::encryptString($passwordReset->email));
    }

    /**
     * Reset Password
     *
     * @OA\Post(
     *      path="/api/v1/password-reset/",
     *      tags={"Auth"},
     *      summary="Change Password",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"email","password","password_confirmation","token"},
     *                  @OA\Property(type="email", title="email", default="JohnDoe@mail.com", property="email"),
     *                  @OA\Property(type="password", title="password", default="********", property="password", minLength=8),
     *                  @OA\Property(type="password", title="password_confirmation", default="********", property="password_confirmation", minLength=8),
     *                  @OA\Property(type="string",title="token",example="cVirkKfR6wg4NPbfjMAdEmzHacztBeP2twIPm6StoTDsvHUWymvcrlJD9cDB",property="token"),
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean",title="success",default="true",property="success"),
     *              @OA\Property(type="object",title="user",ref="#/components/schemas/User",property="user"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean",title="success",default="false",property="success"),
     *              @OA\Property(type="string",title="message",default="We can't find a user with that email address.",property="message"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Errors",
     *      ),
     * )
     * @param [string] email
     * @param [string] password
     * @param [string] password_confirmation
     * @param [string] token
     * @return [string] message
     * @return [json] user object
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|string',
            'password' => 'required|string|confirmed',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 409);
        }

        $passwordReset = PasswordReset::where([
            'token' => $request->input('token'),
            'email' => $request->input('email')
        ])->first();

        if (!$passwordReset) {
            return response()->json([
                "success"=>false,
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json([
                "success"=>false,
                'message' => 'We can"t find a user with that e-mail address.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();


        $passwordReset->delete();

        $user->notify(new PasswordResetSuccess($passwordReset));

        return response()->json([
            "success" => true,
            "user" => $user,
        ],201);
    }
}
