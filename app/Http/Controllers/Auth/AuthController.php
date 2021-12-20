<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SignupActivate;
use App\Notifications\SignupActivated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravolt\Avatar\Facade as Avatar;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login','register','signupActivate', 'requestNewVerificationToken']]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/register",
     *      tags={"Auth"},
     *      summary="Registration",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"firstName","lastName","email", "password","telephone"},
     *                  @OA\Property(type="string", title="firstName", default="John", property="firstName", minLength=3),
     *                  @OA\Property(type="string", title="lastName", default="Doe", property="lastName", minLength=3),
     *                  @OA\Property(type="email", title="email", default="JohnDoe@mail.com", property="email"),
     *                  @OA\Property(type="password", title="password", default="********", property="password", minLength=8),
     *                  @OA\Property(type="password", title="password_confirmation", default="********", property="password_confirmation", minLength=8),
     *                  @OA\Property(type="telephone", title="telephone", default="07067980879", property="telephone"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", title="success", default="true", property="success"),
     *              @OA\Property(type="string", title="message", example="Created", property="message"),
     *          ),
     *      ),
     *     @OA\Response(
     *          response=409,
     *          description="Request not processed",
     *      ),
     * )
     */
    public function register(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(),[
            'firstName' => 'required|string|min:3',
            'lastName' => 'required|string|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'telephone' => 'required',
        ]);

        // check if there is errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 409);
        }

        // create user
        $user = new User([
            'first_name' => stripslashes(strip_tags(trim($request->input('firstName')))),
            'last_name' => stripslashes(strip_tags(trim($request->input('lastName')))),
            'email' => stripslashes(strip_tags(trim($request->input('email')))),
            'password' => bcrypt(stripslashes(strip_tags(trim($request->input('password'))))),
            'telephone' => stripslashes(strip_tags(trim($request->input('telephone')))),
            'role' => stripslashes(strip_tags(trim($request->input('role')))) ? stripslashes(strip_tags(trim($request->input('role')))) : "customer",
            'activation_token' => Str::random(60)
        ]);

        // create avatar
        $avatar = Avatar::create(Str::upper($user->first_name) . ' ' . Str::upper($user->last_name))->getImageObject()->encode('png');
        //store the avatar
        Storage::disk('public')->put('images/avatars/'. $user->id . '/avatar.png',(string) $avatar);

        // save user details
        $user->save();

        // send notification
        $user->notify(new SignupActivate($user));
        // return message
        return response()->json([
            'success' => true,
            'message'=> 'User Created!'
        ], 201);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/login",
     *      tags={"Auth"},
     *      summary="Login",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(type="email", title="email", default="john-doe@mail.com", property="email"),
     *                  @OA\Property(type="password", title="password", default="********", property="password", minLength=8),
     *                  @OA\Property(type="boolean", title="remember_me", default="false", property="remember_me"),
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", title="success", default="true", property="success"),
     *              @OA\Property(type="string", title="access_token", property="access_token"),
     *              @OA\Property(type="string", title="token_type", example="Bearer", property="token_type"),
     *              @OA\Property(type="object", title="user", ref="#/components/schemas/User", property="user"),
     *          ),
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", title="success", default="false", property="success"),
     *              @OA\Property(type="string", title="message", property="message", default="Invalid Credentials!"),
     *          ),
     *      ),
     *     @OA\Response(
     *          response=409,
     *          description="Request not processed",
     *      ),
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required|string|min:8',
            'remember_me' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 409);
        }

        $credentials = request(['email', 'password']);
        $credentials['active'] = 1;
        $credentials['deleted_at'] = null;

        // check if the user exists
        $user = User::where('email', $request->email)->first();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Credentials!',
            ],401);
        }else {
            $token = $user->createToken('‘authToken’')->plainTextToken;
            if(!$request->input('remember_me') == true){
                return response()->json([
                    'success' => true,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => Auth::user()
                ]);
            }

            return response()->json([
                'success' => true,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => Auth::user()
            ]);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/logout",
     *      tags={"Auth"},
     *      summary="Logout",
     *      security={{ "Bearer":{} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", title="success", default="true", property="success"),
     *              @OA\Property(type="string", title="message", property="message", example="Successfully logout"),
     *          ),
     *      ),
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'You have logout successfully!',
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/signup/activate/{token}",
     *      tags={"Auth"},
     *      summary="activate account",
     *
     *      @OA\Parameter(
     *          name="token",
     *          in="path",
     *          @OA\Schema(type="string"),
     *          required=true,
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="unsuccessful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="string", title="message", property="message", example="This activation token is invalid"),
     *          ),
     *      ),
     * )
     */
    public function signupActivate(Request $request)
    {
        // dd($request->token);
        $user = User::where('activation_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid'
            ],404);
        }

        $user->active =true;
        $user->activation_token = '';
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();

        $user->notify(new SignupActivated($user));
        // return redirect()->away(env('APP_FRONTEND_URL'));
        return $user;
    }

    /**
     * @OA\Post(
     *      path="/api/v1/activation-token",
     *      tags={"Auth"},
     *      summary="Request new activation token",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"email"},
     *                  @OA\Property(type="email", title="email", default="john-doe@mail.com", property="email", example="john@mail.com"),
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", title="success",default="true", property="success"),
     *              @OA\Property(type="string", title="message",default="A new verification link has been sent to your email address", property="message"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", title="success",default="false", property="success"),
     *              @OA\Property(type="string", title="message",default="User not found, please register", property="message"),
     *          ),
     *      ),
     * )
     */
    public function requestNewVerificationToken(Request $request)
    {
        // check if the user exists
        $user = User::where('email', $request->email)->first();
        // if not, return a json error message
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'User not found, please register',
            ],404);
        }

        // if the user already, generate another activation_token
        $user->activation_token = Str::random(60);
        $user->save();

        $user->notify(new SignupActivate($user));
        return response()->json([
            'success' => true,
            'message'=> 'A new verification link has been sent to your email address'
        ], 200);
    }
}
