<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;

class CartApiController extends Controller
{
    public function add_to_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return apiResponse(false, 'Validation error', $validator->errors(), 422);
        }

        $userId = Auth::check() ? Auth::id() : Cookie::get('guest_user_id');

        // Check if product has variations and if a variation is selected
        $product = Product::find($request->product_id);
        $isAttributeProduct = $product->product_type === 'attribute';

        // If the product is an attribute product, ensure variation_id is provided
        if ($isAttributeProduct && !$request->has('variation_id')) {
            return response()->json(['error' => 'Please select a variation before adding to cart.'], 422);
        }

        // Get the variation name if it exists
        $variationName = null;
        if ($isAttributeProduct && $request->has('variation_id')) {
            $variation = ProductVariationOption::find($request->variation_id);
            $variationName = $variation ? $variation->variation_name : 'No variation selected';
        }

        // Initialize stock check
        $availableStock = $product->stock; // Default stock for simple products
        $variationName = null;

        // If variation is selected, get stock from variation table
        if ($isAttributeProduct && $request->has('variation_id')) {
            $variation = ProductVariationOption::find($request->variation_id);
            if (!$variation) {
                return response()->json([
                    'status' => 'false',
                    'massage' => 'Selected variation is invalid.'
                ]);
            }
            $availableStock = $variation->stock;
            $variationName = $variation->variation_name;
        }

        // Check if stock is available
        if ($availableStock < $request->quantity) {
            return response()->json([
                'status' => 'false',
                'massage' => 'Only ' . $availableStock . ' left in stock for ' . $product->name . ($variationName ? ' (' . $variationName . ')' : '') . '.'
            ]);
        }

        // Check for an existing cart item (either based on product_id or product_id + variation_id)
        $existingCartItem = Cart::where('user_id', $userId)
                                ->where('product_id', $request->product_id)
                                ->where('product_variation_options_id', $request->variation_id ?? null)
                                ->first();

        if ($existingCartItem) {

            // Check if total quantity exceeds stock before updating
            if ($existingCartItem->quantity + $request->quantity > $availableStock) {
                return response()->json([
                    'status' => 'false',
                    'massage' => 'Only ' . $availableStock . ' left in stock for ' . $product->name . ($variationName ? ' (' . $variationName . ')' : '') . '.'
                ]);
            }

            // Update quantity if the product is already in the cart
            $existingCartItem->quantity += $request->quantity;
            $existingCartItem->save();

            return response()->json([
                'status' => 'true',
                'massage' => $existingCartItem->product->name . ($variationName ? ' (' . $variationName . ')' : '') . ' updated in cart successfully'
            ]);
        }

        // Create a new cart item if no existing item is found
        $cartItem = Cart::create([
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'product_variation_options_id' => $request->variation_id ?? null,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'status' => 'true',
            'massage' => $cartItem->product->name . ($variationName ? ' (' . $variationName . ')' : '') . ' added to cart successfully'
        ]);
    }

}
