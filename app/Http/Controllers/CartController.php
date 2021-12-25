<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *      name="Cart",
 * )
 */
class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @OA\Get(
     *      path="/api/v1/cart",
     *      description="Get all products in cart",
     *      tags={"Cart"},
     *      summary="Get all products in cart",
     *      security={{ "Bearer":{} }},
     *      @OA\Response(
     *          response=200,
     *          description="Success operation",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Cart"
     *          )
     *      )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // get the user id from auth
        $user_id = auth()->user()->id;
        // get all the products associated with this user
        $cart = Cart::where('user_id', $user_id)->get();

        return response()->json([
            "success" => true,
            "cart" => $cart
        ], 200);
    }

    /**
     * @OA\Post(
     *  tags={"Cart"},
     *  path="/api/v1/cart",
     *  security={{ "Bearer":{} }},
     *  summary="Add new item cart",
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              required={"product_name","product_id","product_price","quantity"},
     *              @OA\Property(type="integer",title="product_id",example=1,property="product_id"),
     *              @OA\Property(type="string",title="product_name",example="Gucci Shirt",property="product_name"),
     *              @OA\Property(type="integer",title="product_price",example=1,property="product_price"),
     *              @OA\Property(type="integer",title="quantity",example=1,property="quantity"),
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response=201,
     *      description="Successful operation",
     *      @OA\JsonContent(
     *          @OA\Property(type="object", title="cart", ref="#/components/schemas/Cart", property="cart"),
     *              @OA\Property(type="string", title="status", default="success", property="status"),
     *              @OA\Property(type="number", title="status_code", property="status_code", default=201),
     *      )
     *  )
     * )
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id = auth()->user()->id;
        $cart = new Cart([
            'user_id' => $user_id,
            'product_name' => $request->input('product_name'),
            'product_id' => $request->input('product_id'),
            'product_price' => $request->input('product_price'),
            'quantity' => $request->input('quantity'),
        ]);

        $cart->save();

        return response()->json([
            "cart" => $cart,
            'status_code' => 201,
            'status' => 'success',
        ]);
    }

    /**
     * @OA\Patch(
     *  path="/api/v1/cart/update/id",
     *  tags={"Cart"},
     *  summary="update quantity in cart",
     *  security={{ "Bearer":{} }},
     *  @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              required={"quantity"},
     *              @OA\Property(type="integer",title="quantity", example="1", property="quantity")
     *          )
     *      ),
     *  ),
     *  @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *          type="integer",
     *      ),
     *      example=1
     *  ),
     *  @OA\Response(
     *      response=204,
     *      description="Success operation",
     *  )
     * )
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $cart = Cart::findOrFail($id);

        $cart->update($request->all());

        return response()->json([
            'cart' => $cart,
            'status_code' => 204,
            'status' => 'success',
        ], 204);
    }

    /**
     *  @OA\Delete(
     *      path="/api/v1/cart/remove/{id}",
     *      tags={"Cart"},
     *      description="remove an item from cart",
     *      summary="remove an item from cart",
     *      security={{ "Bearer":{} }},
     *
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean",title="success",default="true", property="success"),
     *          )
     *      )
     * )
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Cart::where('id', $id)->delete();
        return response()->json([
            "success" => true,
        ], 204);
    }

    /**
     *  Clear all cart items associated with the current user.
     * @OA\Delete(
     *      path="/api/v1/cart/clear",
     *      tags={"Cart"},
     *      description="clear all items cart",
     *      security={{ "Bearer":{} }},
     *
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean",title="success",default="true", property="success"),
     *          )
     *      )
     * )
     */
    public function clearAllCart()
    {
        $user_id = auth()->user()->id;
        Cart::where('user_id', $user_id)->delete();
        return response()->json([
            "success" => true,
        ], 204);
    }
}
