<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class ProductController extends Controller
{


    /**
     * Display a listing of the resource.
     *   @OA\Get(
     *       path="/api/v1/product",
     *       operationId="productsList",
     *       tags={"Product"},
     *       summary="Retrieve products",
     *       description="Get all products",
     *       @OA\Response(
     *           response=200,
     *           description="Success operation",
     *           @OA\JsonContent(
     *              @OA\Property(type="object", title="data", ref="#/components/schemas/Product", property="data"),
     *              @OA\Property(type="string", title="status", default="success", property="status"),
     *              @OA\Property(type="number", title="status_code", property="status_code", default=200),
     *           )
     *       )
     *   )
     *
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return (ProductResource::collection(Product::all()))->additional([
            // return (ProductResource::collection(Product::paginate(10)))->additional([
            'status_code' => 200,
            "status" => "success",
        ]);
    }

    /**
     * @OA\Post(
     *      tags={"Product"},
     *      path="/api/v1/product",
     *      summary="Create a new product",
     *      security={{ "Bearer":{} }},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"title","description","category","image","price"},
     *                  @OA\Property(type="string", title="title", default="Black Shirt", property="title", minLength=5),
     *                  @OA\Property(type="double", title="price", default="$100.00", property="price", minLength=2),
     *                  @OA\Property(type="string", title="description", default="Black Shirt unisex", property="description", minLength=10),
     *                  @OA\Property(type="file", title="image", property="image"),
     *                  @OA\Property(type="boolean", title="featured", example="false", property="featured"),
     *                  @OA\Property(type="string", title="category", default="men's clothing", property="category", minLength=3)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="object", title="data", ref="#/components/schemas/Product", property="data"),
     *              @OA\Property(type="string", title="status", default="success", property="status"),
     *              @OA\Property(type="number", title="status_code", property="status_code", default=201),
     *              @OA\Property(type="string", title="message", example="The Product was successfully created", property="message"),
     *          )
     *      )
     *  )
     *
     * Create a new product
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //allow agent to create products
        if (Auth::user()->role !== 'agent') {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }

        // Validate requests
        $validator = Validator::make($request->all(), [
            'title' => "required|string|min:3|unique:products|max:255",
            'price' => "required|numeric",
            'description' => 'required|string|min:10',
            'category' => 'required|string|min:10',
            'image' => 'required|mimes:jpeg,jpg,png,gif,svg|max:2048',
        ]);

        // check if there is errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 409);
        }

        //generate slug for product
        $slug = str_replace(' ', '-', $request->input('title'));

        //generate image path for product on cloudinary
        $uploadedFileUrl = Cloudinary::upload(
            $request->file('image')->getRealPath(),
            [
                'folder' => 'e-com-app/images/products/' . $slug,
                'public_id' => $slug
            ]
        )->getSecurePath();

        //generate image path for product locally
        // $request->file('image')->storeAs('public/images/products', $filename);

        if ($request->input('feature') === "true")
            $featured = 1;
        else
            $featured = 0;
        //Assign various attributes to the product
        $product = new Product([
            'title' => $request->input('title'),
            'price' => $request->input('price'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'feature' => $featured,
            'image' =>  $uploadedFileUrl,
        ]);
        // save new product and return the product
        if ($product->save()) {
            return (new ProductResource($product))->additional([
                'status_code' => 201,
                'status' => 'success',
                'message' => 'The ' . $product->title . ' was created successfully',
            ]);
        }
    }

    /**
     * @OA\Get(
     *      tags={"Product"},
     *      path="/api/v1/product/{id}",
     *      summary="Get product detail",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="product id",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *          required=true,
     *          example=1
     *      ),
     *    @OA\Response(
     *      response=200,
     *      description="successful operation",
     *       @OA\JsonContent(
     *              @OA\Property(type="object", title="data", ref="#/components/schemas/Product", property="data"),
     *              @OA\Property(type="string", title="status", default="success", property="status"),
     *              @OA\Property(type="number", title="status_code", property="status_code", default=200),
     *       )
     *    )
     * )
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return (new ProductResource($product))->additional([
            'status' => 'success',
            'status_code' => 200
        ]);
    }

    /**
     * @OA\Put(
     *      tags={"Product"},
     *      path="/api/v1/product/{id}",
     *      summary="Update product",
     *      security={{ "Bearer":{} }},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="product id",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *          required=true,
     *          example=1
     *      ),
     *
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"title","description","category","price","image"},
     *                  @OA\Property(type="string", title="title", default="Black Shirt", property="title", minLength=5),
     *                  @OA\Property(type="double", title="price", default="$100.00", property="price", minLength=2),
     *                  @OA\Property(type="string", title="description", default="Black Shirt unisex", property="description", minLength=10),
     *                  @OA\Property(type="string", title="image", default="black-shirt.jpg", property="image"),
     *                  @OA\Property(type="boolean", title="feature", default="false", property="featured"),
     *                  @OA\Property(type="string", title="category", default="men's clothing", property="category", minLength=3)
     *              )
     *          )
     *      ),
     *      @OA\Response(response=204,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="object", title="data", ref="#/components/schemas/Product", property="data"),
     *              @OA\Property(type="string", title="status", default="success", property="status"),
     *              @OA\Property(type="number", title="status_code", property="status_code", default=204),
     *              @OA\Property(type="string", title="message", example="The Product was successfully updated", property="message"),
     *          )
     *      ),
     * )
     *
     *
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'agent') {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }

        $product = Product::findOrFail($id);

        //generate slug for product
        $slug = str_replace(' ', '-', $request->input('title'));

        // check if request contains file
        if ($request->file('image') !== null) {
            //delete the image from cloud storage
            Cloudinary::destroy('e-com-app/images/products/' . $product->slug);

            //delete from local storage
            // Storage::disk('public')->delete('/images/products/' . $product->image);
            // $preImage = $filename;

            // generate image path for product
            $uploadedFileUrl = Cloudinary::upload(
                $request->file('image')->getRealPath(),
                [
                    'folder' => 'e-com-app/images/products/',
                    'public_id' => $slug
                ]
            )->getSecurePath();

            //local storing on of image
            // $request->file('image')->storeAs('public/images/products', $filename);
        }


        //generate image path for product
        // $filename = $slug . '.jpg';
        if ($request->input('feature') === "true")
            $featured = 1;
        else
            $featured = 0;

        $product->id = $id;
        $product->title = $request->input('title');
        $product->price = $request->input('price');
        $product->slug = $slug;
        $product->description = $request->input('description');
        $product->category = $request->input('category');
        $product->feature = $featured;
        $product->image = $request->file('image') === null ? $product->image : $uploadedFileUrl;

        if ($product->save()) {
            return (new ProductResource($product))->additional([
                'status_code' => 204,
                'status' => 'success',
                'message' => 'The ' . $product->title . ' was updated successfully',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @OA\Delete(
     *      path="/api/v1/product/{id}",
     *      tags={"Product"},
     *      security={{ "Bearer":{} }},
     *      summary="Delete a product",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="product id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *          example=1
     *      ),
     *
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(type="string",title="status_code",default="204", property="status_code"),
     *              @OA\Property(type="string",title="status",default="success", property="status"),
     *              @OA\Property(type="string",title="message",default="The Product deleted successfully", property="message"),
     *          )
     *      )
     * )
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::user()->role !== 'agent') {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }

        $delete_product = Product::findOrFail($id);

        // delete from cloud before deleting product
        Cloudinary::destroy('e-com-app/images/products/' . $delete_product->slug);
        if ($delete_product->delete()) {
            return (new ProductResource($delete_product))->additional([
                'status_code' => 204,
                'status' => 'success',
                'message' => 'The ' . $delete_product->title . ' was deleted successfully',
            ]);
        }
    }
}
