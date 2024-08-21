<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;

class ProductController extends Controller
{
    public function products()
    {
        $categories = ProductCategory::with('products')->get();

        if (!$categories) {
            return response()->json(['error' => 'Aucun produit trouvé']);
        }

        return response()->json([
            'categories' => $categories,
        ]);
    }
}
