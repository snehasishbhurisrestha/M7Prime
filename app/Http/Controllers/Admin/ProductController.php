<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductVariation;
use App\Models\ProductVariationOption;
use Illuminate\Http\Request;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use Illuminate\Support\Facades\Validator;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:Product Show', only: ['index']),
            new Middleware('permission:Product Create', only: ['basic_info_create','basic_info_process']),
            new Middleware('permission:Product Edit', only: ['basic_info_edit','basic_info_edit_process','price_edit','price_edit_process','product_images_edit','product_images_process']),
            new Middleware('permission:Product Delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $proucts = Product::all();
        return view('admin.products.index',compact('proucts'));
    }

    public function basic_info_create(){
        $categorys = Category::where('is_visible',1)->where('parent_id',null)->get();
        $brands = Brand::where('is_visible',1)->where('parent_id',null)->get();
        // $brands = Brand::where('is_visible',1)->get();
        return view('admin.products.basic_info',compact('categorys','brands'));
    }

    public function basic_info_process(Request $request){
        $request->validate([
            'product_name' => 'required|string|max:255',
            'brand' => 'nullable|exists:brands,id',
            'sort_description' => 'nullable',
            'long_description' => 'nullable',
            'product_type' => 'nullable|in:simple,attribute',
        ]);
        $product = new Product();
        $product->name = $request->product_name;
        $product->slug = createSlug($request->product_name, Product::class);
        $product->brand_id = $request->brand;
        $product->product_type = $request->product_type ?? 'simple';
        $product->sort_description = $request->sort_description;
        $product->long_description = $request->long_description;

        // SEO fields
        $product->meta_title = $request->meta_title;
        $product->meta_keywords = $request->meta_keywords;
        $product->meta_description = $request->meta_description;

        // Pricing defaults
        $product->price = 0.00;
        $product->product_price = 0.00;
        $product->discount_rate = 0;
        $product->discount_price = 0.00;
        $product->gst_rate = 0;
        $product->gst_amount = 0.00;
        $product->total_price = 0.00;

        // Visibility & flags
        $product->is_special = $request->is_special;
        $product->is_home = $request->is_home;
        $product->is_featured = $request->is_featured;
        $product->is_visible = $request->is_visible;
        $product->is_best_selling = $request->is_best_selling;

        $res = $product->save();


        if ($request->has('categories')) {  
            $product->categories()->sync($request->categories);
        }

        if ($request->has('brands')) {  
            $product->brands()->sync($request->brands);
        }

        if($res){
            if($product->product_type == 'simple'){
                return redirect(route('products.price-edit',$product->id))->with(['success'=>'Basic Information Added Successfully']);
            }
            else{
                return redirect(route('products.product-veriation-edit',$product->id))->with(['success'=>'Basic Information Added Successfully']);
            }
        }else{
            return redirect()->back()->with(['error'=>'Some error occurs!']);
        }
    }

    public function basic_info_edit(Request $request){
        $categorys = Category::where('is_visible',1)->where('parent_id',null)->get();
        $brands = Brand::where('is_visible',1)->where('parent_id',null)->get();
        $product = Product::find($request->id);
        $selectedCategories = $product->categories->pluck('id')->toArray();
        $selectedBrands = $product->brands->pluck('id')->toArray();
        // $brands = Brand::where('is_visible',1)->get();
        return view('admin.products.basic_info_edit',compact('categorys','product','selectedCategories','brands','selectedBrands'));
    }

    public function basic_info_edit_process(Request $request){
        $product = Product::findOrFail($request->product_id);
        if($product->name != $request->product_name){
            $product->name = $request->product_name;
            $product->slug = createSlug($request->product_name, Product::class);
        }

        // SEO fields
        $product->meta_title = $request->meta_title;
        $product->meta_keywords = $request->meta_keywords;
        $product->meta_description = $request->meta_description;

        $product->brand_id = $request->brand;
        $product->product_type = $request->product_type;
        $product->sort_description = $request->sort_description;
        $product->long_description = $request->long_description;
        $product->is_special = $request->is_special;
        $product->is_home = $request->is_home;
        $product->is_featured = $request->is_featured;
        $product->is_visible = $request->is_visible;
        $product->is_best_selling = $request->is_best_selling;
        $res = $product->update();

        if ($request->has('categories')) {  
            $product->categories()->sync($request->categories);
        }

        if ($request->has('brands')) {  
            $product->brands()->sync($request->brands);
        }

        if($res){
            // return redirect(route('products.price-edit',$product->id))->with(['success'=>'Basic Information Added Successfully']);

            if($product->product_type == 'simple'){
                return redirect(route('products.price-edit',$product->id))->with(['success'=>'Basic Information Added Successfully']);
            }
            else{
                return redirect(route('products.product-veriation-edit',$product->id))->with(['success'=>'Basic Information Added Successfully']);
            }
        }else{
            return redirect()->back()->with(['error'=>'Some error occurs!']);
        }
    }

    public function price_edit(Request $request){
        if(request()->segment(4) == ''){
			return redirect(route('products.basic-info-create'))->with(['error'=>'Please Fill Basic Information']);
		}
        $product = Product::find($request->id);
        return view('admin.products.price_edit',compact('product'));
    }

    public function price_edit_process(Request $request){
        $product = Product::find($request->product_id);
        $product->price = $request->product_price;
        $product->discount_rate = $request->discount_rate;
        // $product->discounted_price = $request->product_price - (($request->discount_rate / 100) * $request->product_price);
        $product->gst_rate = $request->gst_rate;
        $product->total_price = $request->total_price;
        // $product->gst_amount = ($request->gst_rate / 100) * $product->discounted_price;
        $product->discount_price = ($request->discount_rate / 100) * $request->product_price;
        $gstRate = $request->gst_rate/100;
        $product->gst_amount = ($request->total_price * $gstRate) / (1 + $gstRate);
        $product->product_price = $request->total_price - $product->gst_amount;
        $res = $product->update();
        if($res){
            return redirect(route('products.inventory-edit',$product->id))->with(['success'=>'Price Details Updated Successfully']);
        }else{
            return redirect()->back()->with(['error'=>'Some error occurs!']);
        }
    }

    public function inventory_edit(Request $request){
        if(request()->segment(4) == ''){
			return redirect(route('products.basic-info-create'))->with(['error'=>'Please Fill Basic Information']);
		}
        $data['title'] = 'Products';
        $data['product'] = Product::find($request->id);
        return view('admin.products.inventory_edit')->with($data);
    }

    public function inventory_edit_process(Request $request){ 
        $product = Product::find($request->product_id);
        $request->validate([
            'sku' => 'required|unique:products,sku,'.$product->id,
        ]);
        $product->sku = $request->sku;
        $product->stock	= $request->stock ?? 0;
        $res = $product->update();
        if($res){
            // return redirect(route('products.variation-edit',$product->id))->with(['success'=>'Inventory Updated Successfully']);
            return redirect(route('products.product-images-edit',$product->id))->with(['success'=>'Inventory Updated Successfully']);
        }else{
            return redirect()->back()->with(['error'=>'Some error occurs!']);
        }
    }

    public function product_images_edit(Request $request){
        if(request()->segment(4) == ''){
			return redirect(route('products.basic-info-create'))->with('error','Please Fill Basic Information');
		}
        $product = Product::find($request->id);
        $product_images = $product->getMedia('products-media');
        return view('admin.products.product_images_edit',compact('product','product_images'));
    }

    public function product_images_process(Request $request){
        return redirect(route('product.index'))->with(['success'=>'Updated Successfully']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function productGalleryStore(Request $request)
    {
      
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'file.required' => 'Please upload an image file.',
            'file.image' => 'The file must be an image.',
            'file.mimes' => 'The file must be a type of: jpeg, png, jpg, gif, svg, webp.',
            'file.max' => 'The file size must not exceed 2MB.',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('file')) {

            $file = $request->file('file');

            // Retrieve the Service model
            $product = Product::findOrFail($request->product_id);

            // Add the file to Spatie Media Library
            $media = $product
                ->addMedia($file)
                ->toMediaCollection('products-media');

            // Associate the custom file ID
            $media->setCustomProperty('file_id', $request->file_id);
            $media->save();

            return response()->json(['success' => 'File uploaded successfully!']);
        }
        return response()->json(['error' => 'File not uploaded!'], 500);
    
        // return response()->json(['paths' => $filepath, 'message' => 'Images uploaded successfully']);

    }
    /**
     * Store a newly created resource in storage.
     */
    public function productTempImages(Request $request)
    {
        $file_id = $request->file_id;
		$product_id = $request->product_id;

        $product = Product::find($product_id);
        $mediaItems = $product->getMedia('products-media');
        $media = $mediaItems->firstWhere('custom_properties.file_id', $file_id);
        
        if ($media) {
            $html = '<img src="' . $media->getUrl() . '" alt="">' .
                '<a href="javascript:void(0)" class="btn-img-delete btn-delete-product-img" data-file-id="' . $media->getCustomProperty('file_id') . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove this Item"><i class="fa fa-trash-o text-light"></i></a>';
    
            // Check if this is the main image
            if ($media->getCustomProperty('is_main', false)) {
                $html .= '<a href="javascript:void(0)" class="float-start btn btn-success btn-sm waves-effect btn-set-image-main" style="padding: 0 4px;" data-file-id="' . $media->getCustomProperty('file_id') . '">Main</a>';
            } else {
                $html .= '<a href="javascript:void(0)" class="float-start btn btn-secondary btn-sm waves-effect btn-set-image-main" style="padding: 0 4px;" data-file-id="' . $media->getCustomProperty('file_id') . '">Main</a>';
            }
    
            return response()->json(['html' => $html]);
        }
    
        return response()->json(['error' => 'Media not found.'], 404);
    }

    public function delete_product_media(Request $request){
        $file_id=$request->file_id;
        $product_id=$request->product_id;
        $product = Product::findOrFail($product_id);
        $mediaItem = $product->getMedia('products-media')->firstWhere('custom_properties.file_id', $file_id);

        // Check if media exists
        if ($mediaItem) {
            $mediaItem->delete(); // Delete the media
            return response()->json('Media deleted successfully!');
        }
        return response()->json('Media Not Found');
    }

    public function set_main_product_image(Request $request){
        $file_id = $request->file_id;
        $product_id = $request->product_id;

        // Fetch the product
        $product = Product::findOrFail($product_id);

        // Reset 'is_main' for all images in the gallery
        $mediaItems = $product->getMedia('products-media');
        foreach ($mediaItems as $media) {
            $media->setCustomProperty('is_main', false)->save();
        }

        // Set 'is_main' for the selected file
        $mainImage = $mediaItems->firstWhere('custom_properties.file_id', $file_id);
        if ($mainImage) {
            $mainImage->setCustomProperty('is_main', true)->save();
        }

        // Generate HTML for all media items
        $html = '';
        foreach ($mediaItems as $media) {
            $html .= '<li class="media" id="uploaderFile' . $media->getCustomProperty('file_id') . '">
                        <img src="' . $media->getUrl() . '" alt="">' .
                        '<a href="javascript:void(0)" class="btn-img-delete btn-delete-product-img" data-file-id="' . $media->getCustomProperty('file_id') . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove this Item"><i class="fa fa-trash-o text-light"></i></a>';
            if ($media->getCustomProperty('is_main')) {
                $html .= '<a href="javascript:void(0)" class="float-start btn btn-success btn-sm waves-effect btn-set-image-main" style="padding-bottom: 0px;padding-top: 0px;padding-right: 4px;padding-left: 4px;" data-file-id="' . $media->getCustomProperty('file_id') . '">Main</a>';
            } else {
                $html .= '<a href="javascript:void(0)" class="float-start btn btn-secondary btn-sm waves-effect btn-set-image-main" style="padding-bottom: 0px;padding-top: 0px;padding-right: 4px;padding-left: 4px;" data-file-id="' . $media->getCustomProperty('file_id') . '">Main</a>';
            }
            $html .= '</li>';
        }

        return response()->json(['html' => $html]);
    }

    public function veriation_edit(Request $request){
        if(request()->segment(4) == ''){
			return redirect(route('products.basic-info-create'))->with(['error'=>'Please Fill Basic Information']);
		}
        $product = Product::with(['variations.options'])->find($request->id);
        
        return view('admin.products.variations_edit',compact('product'));
    }

    public function storeVariation(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string'
        ]);

        $variation = ProductVariation::create([
            'product_id' => $request->product_id,
            'name' => $request->name
        ]);

        return response()->json(['success' => true, 'variation' => $variation]);
    }

    public function destroyVariation(string $id){
        $product = ProductVariation::findOrFail($id);
        if($product){
            $res = $product->delete();
            if($res){
                return back()->with('success','Deleted Successfully');
            }else{
                return back()->with('error','Not Deleted');
            }
        }else{
            return back()->with('error','Not Found');
        }
    }

    public function storeVariationOption(Request $request)
    {
        $request->validate([
            'variation_id' => 'required|exists:product_variations,id',
            'type' => 'required|in:color,image,text',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'value' => $request->type == 'image' ? 'required|image|max:2048' : 'required',
        ]);

        if ($request->type == 'image' && $request->hasFile('value')) {
            $media = $request->file('value')->store('variations', 'public');
            $value = $media;
        } else {
            $value = $request->value;
        }
    
        // Create the variation option
        $option = ProductVariationOption::create([
            'variation_id' => $request->variation_id,
            'variation_type' => $request->type,
            'variation_name' => $request->variation_name,
            'value' => $value,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);
    
        // If it's an image, use Spatie Media Library
        if ($request->hasFile('value') && $request->type == 'image') {
            $option->addMedia($request->file('value'))->toMediaCollection('variation-option');
        }
    
        return response()->json([
            'success' => true,
            'option' => [
                'id' => $option->id,
                'type' => ucfirst($option->variation_type),
                'value' => $option->type == 'image' ? $option->getFirstMediaUrl('variation-option') : $option->value,
                'price' => $option->price,
                'stock' => $option->stock,
            ],
        ]);
    }

    public function destroyVariationOption(string $id){
        $product = ProductVariationOption::findOrFail($id);
        if($product){
            $res = $product->delete();
            if($res){
                return back()->with('success','Deleted Successfully');
            }else{
                return back()->with('error','Not Deleted');
            }
        }else{
            return back()->with('error','Not Found');
        }
    }


    public function destroy(string $id){
        $product = Product::findOrFail($id);
        if($product){
            $res = $product->delete();
            if($res){
                return back()->with('success','Deleted Successfully');
            }else{
                return back()->with('error','Not Deleted');
            }
        }else{
            return back()->with('error','Not Found');
        }
    }
}
