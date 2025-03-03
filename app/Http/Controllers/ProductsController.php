<?php

    namespace App\Http\Controllers;

    use App\Models\Products;
    use App\Models\Product_images;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Facades\DB;


    class ProductsController extends Controller
    {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function index()
        {
            $allproducts = Products::with('images')->get();
            $count = $allproducts->count();
            if($count > 0){
                return response()->json(['status'=>true,'allproducts'=> $allproducts]); 
            }else{
                return response()->json(['status'=>false,'success'=> "No record found"]);
            }
        }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function add_product(Request $request)
        {
            $validator = Validator::make($request->all(), [ 
                'cat_id' => 'required', 
                'prod_name' => 'required', 
                'prod_description' => 'required',
                'quantity' => 'required',
                'amount' => 'required',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Validate multiple images
            ]);
        
            if ($validator->fails()) { 
                return response()->json(['error' => $validator->errors()], 401);            
            }
        
            // Save product details
            $p = new Products();
            $p->cat_id  = $request->cat_id;
            $p->prod_name  = $request->prod_name;
            $p->prod_description  = $request->prod_description;
            $p->qty  = $request->quantity;
            $p->amount  = $request->amount;
            $p->save();
        
            $pid = $p->id ?? 1;
        
            if ($pid > 0) {
                // Check if images exist
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $image) {
                        $imageName = time() . '_' . $image->getClientOriginalName();
                        $uploaded_path = $image->move(public_path('uploads/products'), $imageName); 
                        DB::table('products_images')->insert([
                            'product_id' => $pid,
                            'image_path' => 'uploads/products/' . $imageName,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
        
                return response()->json(['status' => true, 'success' => "New product and images added successfully"]); 
            } else {
                return response()->json(['status' => false, 'success' => "Something went wrong"]);
            }
        }
        


        public function edit_product($pro_id){
            $product = Products::where('id',$pro_id)->with('images')->first();

            if($product){
                return response()->json(['status'=>true,'product'=> $product]);
            }else{
                return response()->json(['status'=>false,'success'=> "No record found"]);
            }
        }

        


        public function update_product(Request $request, $id)
        {
        // Validate request
        $validator = Validator::make($request->all(), [ 
            'prod_name' => 'required|string|max:255', 
            'prod_description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'remove_images' => 'array' // Optional: IDs of images to be removed
        ]);

        if ($validator->fails()) { 
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Find existing product
        $product = Products::find($id);
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found'], 404);
        }

        try {
            DB::beginTransaction();

            $product->update([
                'prod_name' => $request->prod_name,
                'prod_description' => $request->prod_description,
                'qty' => $request->quantity,
                'cat_id' => $request->cat_id,
                'amount' => $request->amount,
            ]);


            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $imageId) {
                    $image = Product_images::find($imageId); 
                    if ($image) {
                        $imagePath = public_path($image->image_path);
                        if (file_exists($imagePath)) {
                            unlink($imagePath); // Delete image file
                        }
                        $image->delete(); // Delete image record
                    }
                }
            }


            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/products'), $imageName);

                    Product_images::create([
                        'product_id' => $id,
                        'image_path' => 'uploads/products/' . $imageName
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => "Product updated successfully"]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => "Something went wrong", 'error' => $e->getMessage()], 500);
        }
    }

        public function delete_product($pid)
        {
            $product = Products::find($pid);
            
            if (!$product) {
                return response()->json(['status' => false, 'message' => 'Product not found'], 404);
            }

            // Delete associated images
            $images = DB::table('products_images')->where('product_id', $pid)->get();
            foreach ($images as $image) {
                $imagePath = public_path($image->image_path);
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Delete image file from storage
                }
            }
            
            // Delete images from the database
            DB::table('products_images')->where('product_id', $pid)->delete();

            // Delete product from database
            $product->delete();

            return response()->json(['status' => true, 'message' => 'Product deleted successfully']);
        }


        

    }