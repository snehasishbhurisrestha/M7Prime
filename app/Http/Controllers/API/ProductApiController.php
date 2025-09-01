<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductApiController extends Controller
{
    // 🔹 All products with filters + pagination
    public function index(Request $request)
    {
        $query = Product::with(['categories', 'brands', 'variations.options']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%")
                  ->orWhere('slug', 'LIKE', "%$search%");
        }

        // Filter active only
        $query->where('is_visible', 1);

        $products = $query->paginate(12);

        return apiResponse(true, 'Products', ['products' => $products], 200);
    }

    // 🔹 Products by category
    public function byCategory($categoryId)
    {
        $products = Product::with(['categories', 'brands'])
            ->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            })
            ->where('is_visible', 1)
            ->paginate(12);

        return apiResponse(true, 'Category Products', ['products' => $products], 200);
    }

    // 🔹 Featured products
    public function featured()
    {
        $products = Product::with(['categories', 'brands'])
            ->where('is_featured', 1)
            ->where('is_visible', 1)
            ->limit(10)
            ->get();

        return apiResponse(true, 'Featured Products', ['products' => $products], 200);
    }

    // 🔹 Special products
    public function special()
    {
        $products = Product::with(['categories', 'brand'])
            ->where('is_special', 1)
            ->where('is_visible', 1)
            ->limit(10)
            ->get();

        return apiResponse(true, 'Special Products', ['products' => $products], 200);
    }

    // 🔹 Best selling products (based on orders count)
    public function bestSelling()
    {
        // $products = Product::withCount('orderItems')
        //     ->orderBy('order_items_count', 'desc')
        //     ->where('is_visible', 1)
        //     ->limit(10)
        //     ->get();

        $products = Product::where('is_visible', 1)
            ->where('is_best_selling', 1)
            ->limit(10)
            ->orderBy('created_at','desc')
            ->get();

        return apiResponse(true, 'Best Selling Products', ['products' => $products], 200);
    }

    // 🔹 Product details
    public function show($id)
    {
        $product = Product::with(['categories', 'brands', 'variations.options'])
            ->findOrFail($id);

        return apiResponse(true, 'Product Details', ['product' => $product], 200);
    }
}
