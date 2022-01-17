<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Notifications\SignupActivate;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @OA\Get(
     *  path="/api/v1/profile",
     *  tags={"Auth"},
     *  summary="User profile",
     *  security={{ "Bearer":{} }},
     *  description="Get the authenticated user",
     *  @OA\Response(
     *      response=200,
     *      description="User profile",
     *      @OA\Property(type="object",title="User", ref="#/components/schema/User", property="User"),
     *  )
     * )
     */
    public function profile()
    {
        return response()->json(['user' => Auth::user()], 200);
    }

    /**
     * @OA\Patch(
     *  path="/api/v1/profile-update/{id}",
     *  tags={"Auth"},
     *  summary="Update profile",
     *  security={{ "Bearer":{} }},
     *  description="Update user profile",
     * @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="user id",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *          required=true,
     *          example=1
     *      ),
     *  @OA\Response(
     *      response=204,
     *      description="update a user",
     *  )
     * )
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function profileUpdate(Request $request, int $id)
    {
        if (auth()->user()->id !== $id) {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }

        $user = User::findOrFail($id);

        if ($request->file('image')) {
            Cloudinary::destroy('e-com-app/images/avatars/' . $user->id);

            $uploadedFileUrl = Cloudinary::upload(
                $request->file('image')->getRealPath(),
                [
                    'folder' => 'e-com-app/images/avatars/' . $id,
                    'public_id' => $id
                ]
            )->getSecurePath();
        }

        $user->id = $id;
        $user->first_name = $request->input('firstName');
        $user->last_name = $request->input('lastName');
        $user->email = $request->input('email');
        $user->telephone = $request->input('telephone');
        $user->profile_picture_path = empty($uploadedFileUrl) ? $user->profile_picture_path : $uploadedFileUrl;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile successfully updated!',
            'user' => $user
        ]);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/admin/users",
     *  tags={"Admin"},
     *  summary="List all users",
     *  description="List all users",
     *  security={{ "Bearer":{} }},
     *  @OA\Response(
     *      response=200,
     *      description="list all users",
     *      @OA\Property(type="object",title="Users", ref="#/components/schema/User", property="Users"),
     *  )
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->hasAnyRoles(['Agent', 'Customer'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page',
            ], 401);
        }

        $user = User::with('roles')->get();
        // dd(User::whereHas('roles', function ($q) {
        //     $q->where('name', 'Admin');
        // })->get()->count());
        return response()->json([
            'success' => true,
            'message' => 'List of all users',
            'numberOfUsers' => [
                'numberOfAdministrators' => User::whereHas('roles', function ($q) {
                    $q->where('name', '=', 'Admin');
                })->get()->count(),
                'numberOfAgents' => User::whereHas('roles', function ($q) {
                    $q->where('name', '=', 'Agent');
                })->get()->count(),
                'numberOfCustomers' => User::whereHas('roles', function ($q) {
                    $q->where('name', '=', 'Customer');
                })->get()->count()
            ],
            'users_list' => User::with('roles')->paginate(6),
            'roles' => Role::all()
        ], 200);
    }

    /**
     *  @OA\Get(
     *  path="/api/v1/admin/users/{id}",
     *  tags={"Admin"},
     *  summary="get a single user",
     *  description="get a single user",
     *  security={{ "Bearer":{} }},
     *  @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="user id",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *          required=true,
     *          example=1
     *      ),
     *  @OA\Response(
     *      response=200,
     *      description="get a single user",
     *      @OA\Property(type="object",title="User", ref="#/components/schema/User", property="User"),
     *  )
     * )
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (auth()->user()->hasAnyRoles(['Agent', 'Customer'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }
        // dd(auth()->user());
        $user = User::findOrFail($id);
        return response()->json([
            "user" => $user,
            'success' => true,
        ], 200);
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasAnyRoles(['Agent', 'Customer'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }
        // Validate request
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|min:3',
            'lastName' => 'required|string|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role' => 'required'
        ]);

        // dd($request->all());
        // check if there is errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 409);
        }

        // dd($request->all());
        // create user
        $user = new User([
            'first_name' => stripslashes(strip_tags(trim($request->input('firstName')))),
            'last_name' => stripslashes(strip_tags(trim($request->input('lastName')))),
            'email' => stripslashes(strip_tags(trim($request->input('email')))),
            'password' => bcrypt(stripslashes(strip_tags(trim($request->input('password'))))),
            'telephone' => stripslashes(strip_tags(trim($request->input('telephone')))),
            'activation_token' => Str::random(60)
        ]);

        $user->save();

        $user_role =  Role::where("name", $request->input('role'))->first();

        $user->roles()->sync($request->input('role'));

        // send notification
        $user->notify(new SignupActivate($user));
        // return message
        return response()->json([
            'success' => true,
            'message' => 'User Created!'
        ], 201);
    }
    /**
     * @OA\Patch(
     *  path="/api/v1/admin/users/{id}",
     *  tags={"Admin"},
     *  summary="Update a user",
     *  security={{ "Bearer":{} }},
     *  description="Update a user",
     * @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="user id",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *          required=true,
     *          example=1
     *      ),
     *  @OA\Response(
     *      response=204,
     *      description="update a user",
     *  )
     * )
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (auth()->user()->hasAnyRoles(['Agent', 'Customer'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }

        // dd(request()->input('role'));
        $user = User::findOrFail($id);
        // if ($request->isMethod('PATCH')) {
        // $user->id = $request->id;
        // $user->active = $request->active;
        // $user->update();

        $user->roles()->sync($request->input('role'));
        return response()->json([
            "user" => $user,
            'success' => true,
        ], 204);
        // }
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/admin/users/{id}",
     *  security={{ "Bearer":{} }},
     *  description="Delete a user",
     *  summary="Delete a user",
     *  tags={"Admin"},
     *  @OA\Response(
     *      response=204,
     *      description="delete a user",
     *  )
     * )
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (auth()->user()->hasAnyRoles(['Agent', 'Customer'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User Deleted Successfully!'
        ], 200);
    }
}
