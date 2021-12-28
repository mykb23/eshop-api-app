<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return response()->json(['User' => Auth::user()], 200);
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
    public function profileUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        // dd($request->all(), $id);

        $user->update($request->except(['roles']));
        if (!empty($request->input('roles'))) {
            $user->roles()->sync($request->roles);
        }

        return response()->json([
            "user" => $user,
            'success' => true,
        ], 204);
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
        // dd($user);
        return response()->json([
            'success' => true,
            'message' => 'List of all users',
            // 'user' => $user
            'user' => User::with('roles')->paginate(2),
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

        $user = User::findOrFail($id);
        if ($request->isMethod('PATCH')) {
            $user->id = $request->id;
            $user->active = $request->active;
            $user->update();

            $user->roles->sync($request->input('role'));
            return response()->json([
                "user" => $user,
                'success' => true,
            ], 204);
        }
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
