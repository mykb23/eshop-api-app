<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class Products extends Component
{
    use WithFileUploads;

    public $products, $product_id, $title, $price, $description, $slug, $featured, $fileName, $category;
    public $updateMode = false;
    protected $listeners = ['fileSelected' => 'fileSelected'];
    public function render()
    {
        $this->products = Product::all();
        return view('livewire.products');
    }

    /**
     *  Show a single product
     */
    public function show(Product $product)
    {
        $product = Product::where('slug', $product->slug)->first();
        $this->product_id = $product->id;
        $this->title = $product->title;
        $this->price = $product->isbn;
        $this->description = $product->author;
        $this->slug = $product->slug;
        $this->location = $product->location;
        $this->featured = $product->featured;
        $this->images = $product->images;
        $this->extra = $product->extra;
    }
    /**
     *  Create a new Product in the database
     *  @param Product $product
     */
    public function store(Request $request)
    {
        // if (Auth::user()->role !== 'agent') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You are Unauthorized to view this page'
        //     ], 401);
        // }
        dd($request->all());
        // Validate requests
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|unique:Products',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'category' => 'required|string|min:3',
            'featured' => 'required|boolean',
            'fileName' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
        ]);

        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->messages], 409);
        // }


        $slug = str_replace(' ', '-', $request->input('title'));
        $filename = $this->fileName->store('products','public');
        // if ($uploadImage = $request->file('image')) {
        //     $this->image->store(public_path('/images/products/'),$slug);
        // //     foreach ($images as $image) {
        //         $filename = $uploadImage->getClientOriginalName();
        // //         $image->move(public_path() . "/images/products/" . $slug, $filename);
        // //         // $img[] = $filename;
        // // //     }
        // }
        // dd($request->all(), $filename);


        // $table->string('title');
        // $table->string('slug');
        // $table->decimal('price',10,2);
        // $table->text('description');
        // $table->string('image');
        // $table->enum('featured',[true, false]);
        // $table->string('category');
        dd($filename);
       Product::create([
            'title' => $request->input('title'),
            'price' => $request->input('price'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'featured' => $request->input('featured'),
            'image' =>  $filename,
            'category' => $request->input('category'),
        ]);

        // if ($product->save()) {
            session()->flash('success', 'Product successfully created!');
            $this->resetInputFields();
            $this->emit('productStore');
        // }
    }

    /**
     *
     *  Update a product
     *  @param Product $product
     *  @return Product
     */
    public function update(Request $request, Product $product)
    {
        if (Auth::user()->role !== 'agent') {
            return response()->json([
                'success' => false,
                'message' => 'You are Unauthorized to view this page'
            ], 401);
        }

        $product = Product::findOrFail($product->slug);

        $img = array();

        if ($images = $request->file('images')) {
            foreach (explode(",", $product->images) as $imageDelete) {
                Storage::delete(public_path() . "/images/products/" . $product->slug, $imageDelete);
                Storage::deleteDirectory($product->slug);
            }

            foreach ($images as $image) {
                $filename = $image->getClientOriginalName();
                $image->move(public_path() . "/images/products/" . $request->slug, $filename);
                $img[] = $filename;
            }
        }
        $slug = str_replace(' ', '-', $request->input('title'));
        $product->id = $product;
        $product->title = $request->input('title');
        $product->price = $request->input('price');
        $product->slug = $slug;
        $product->description = $request->input('description');
        $product->location = $request->input('location');
        $product->featured = $request->input('featured');
        $product->images = $request->input('images') ? implode(",", $img) : $product->images;


        if ($product->save()) {
            session()->flash('success', $product->title . ' was successfully updated!');
        }
    }

    /**
     *
     * Delete a product from the database by its ID
     *
     */
    public function destroy(Product $product)
    {
        $product = Product::findOrFail($product);
        if ($product->delete()) {
            session()->flash('success', 'The ' . $product->title . ' was successfully deleted!');
        }
    }

    /**
     * Reset input fields to default
     */
    public function resetInputFields()
    {
        $this->title ='';
        $this->price ='';
        $this->description ='';
        $this->featured = '';
        $this->images = '';
        $this->extra = '';
        // $featured, $images, $extra;
    }

    /**
     *  Cancel update operation
     */
    public function cancel()
    {
        $this->updateMode = false;
        $this->resetInputFields();
    }
}
