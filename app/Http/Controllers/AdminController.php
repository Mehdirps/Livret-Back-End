<?php
/**
 * Copyright (c) 2025 Mehdi Raposo
 * Ce fichier fait partie du projet Heberginfos.
 *
 * Ce fichier, ainsi que tout le code et les ressources qu'il contient,
 * est protégé par le droit d'auteur. Toute utilisation, modification,
 * distribution ou reproduction non autorisée est strictement interdite
 * sans une autorisation écrite préalable de Mehdi Raposo.
 */

namespace App\Http\Controllers;

use App\Models\Background;
use App\Models\BackgroundGroup;
use App\Models\Livret;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Bienvenue sur la page admin']);
    }

    public function users()
    {
        $users = User::where('role', 'user')->paginate(15);

        if($users->isEmpty()){
            return response()->json(['message' => 'Aucun utilisateur trouvé']);
        }

        return response()->json($users);
    }

    public function enableUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $user->active = !$user->active;
        $user->save();

        return response()->json(['message' => $user->active ? 'Utilisateur activé' : 'Utilisateur désactivé', 'user' => $user]);
    }

    public function searchUsers(Request $request)
    {
        $search = $request->get('search');
        $users = User::where('role', 'user')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%');
            })
            ->paginate(15);

        if($users->isEmpty()){
            return response()->json(['message' => 'Aucun utilisateur trouvé']);
        }

        return response()->json($users);
    }

    public function backgrounds()
    {
        $background_groups = BackgroundGroup::all();

        if($background_groups->isEmpty()){
            return response()->json(['message' => 'Aucun groupe de fond d\'écran trouvé']);
        }

        return response()->json($background_groups);
    }

    public function addBackgroundGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $background = new BackgroundGroup();
        $background->name = $request->name;
        $background->description = $request->description;
        $background->save();

        $directoryName = str_replace(' ', '_', strtolower($request->name));
        $directoryPath = public_path('assets/backgrounds/' . $directoryName);

        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        return response()->json(['message' => 'Background ajouté', 'background_group' => $background]);
    }

    public function deleteBackgroundGroup($id)
    {
        $backgroundGroup = BackgroundGroup::find($id);

        if (!$backgroundGroup) {
            return response()->json(['error' => 'Groupe de fond d\'écran non trouvé'], 404);
        }

        foreach ($backgroundGroup->backgrounds as $background) {
            $file_path = public_path($background->path);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $background->delete();
        }

        $backgroundGroup->delete();

        return response()->json(['message' => 'Background supprimé']);
    }

    public function addBackground(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'background_group_id' => 'required|exists:background_groups,id',
            'file' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $backgroundGroup = BackgroundGroup::find($request->background_group_id);

        $backgroundFile = $request->file('file');
        $backgroundName = time() . '.' . $backgroundFile->extension();
        $backgroundFile->move(public_path('assets/backgrounds/' . str_replace(' ', '_', strtolower($backgroundGroup->name))), $backgroundName);

        $background = new Background();
        $background->name = $request->name;
        $background->path = 'assets/backgrounds/' . str_replace(' ', '_', strtolower($backgroundGroup->name)) . '/' . $backgroundName;
        $background->background_group_id = $request->background_group_id;
        $background->save();

        return response()->json(['message' => 'Fond d\'écran ajouté', 'background' => $background]);
    }

    public function deleteBackground($id)
    {
        $background = Background::find($id);

        if (!$background) {
            return response()->json(['error' => 'Fond d\'écran non trouvé'], 404);
        }

        $file_path = public_path($background->path);
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $background->delete();

        return response()->json(['message' => 'Fond d\'écran supprimé']);
    }

    public function livrets()
    {
        $livrets = Livret::paginate(15);
        return response()->json($livrets);
    }

    public function searchLivrets(Request $request)
    {
        $search = $request->get('search');

        $livrets = Livret::where('livret_name', 'like', '%' . $search . '%')
            ->orWhere('establishment_name', 'like', '%' . $search . '%')
            ->orWhere('establishment_address', 'like', '%' . $search . '%')
            ->orWhere('establishment_phone', 'like', '%' . $search . '%')
            ->paginate(15);

        if($livrets->isEmpty()){
            return response()->json(['message' => 'Aucun livret trouvé']);
        }

        return response()->json($livrets);
    }

    public function products()
    {
        $products = Product::paginate(15);
        $categories = ProductCategory::all();

        if($products->isEmpty()){
            return response()->json(['message' => 'Aucun produit trouvé']);
        }

        if($categories->isEmpty()){
            return response()->json(['message' => 'Aucune catégorie de produit trouvée']);
        }

        return response()->json(['products' => $products, 'categories' => $categories]);
    }

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required',
            'url' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp',
            'category' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->url = $request->url;
        $product->price = $request->price;
        $product->category_id = $request->category;

        $productImage = $request->file('image');
        $productImageName = time() . '.' . $productImage->extension();
        $productImage->move(public_path('assets/uploads/products'), $productImageName);

        $product->image = 'assets/uploads/products/' . $productImageName;
        $product->save();

        return response()->json(['message' => 'Produit ajouté', 'product' => $product]);
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if(!$product){
            return response()->json(['error' => 'Produit non trouvé'], 404);
        }

        $file_path = public_path($product->image);

        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $product->delete();

        return response()->json(['message' => 'Produit supprimé']);
    }

    public function updateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required',
            'url' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,webp',
            'category' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->url = $request->url;
        $product->price = $request->price;
        $product->category_id = $request->category;

        if ($request->hasFile('image')) {
            $file_path = public_path($product->image);
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            $productImage = $request->file('image');
            $productImageName = time() . '.' . $productImage->extension();
            $productImage->move(public_path('assets/uploads/products'), $productImageName);

            $product->image = 'assets/uploads/products/' . $productImageName;
        }

        $product->save();

        return response()->json(['message' => 'Produit modifié']);
    }

    public function searchProducts(Request $request)
    {
        $search = $request->search;

        $products = Product::where('name', 'like', '%' . $search . '%')
            ->orWhere('price', 'like', '%' . $search . '%')
            ->orWhere('url', 'like', '%' . $search . '%')
            ->paginate(1);

        if($products->isEmpty()){
            return response()->json(['message' => 'Aucun produit trouvé']);
        }

        return response()->json($products);
    }

    public function addProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $productCategory = new ProductCategory();
        $productCategory->name = $request->name;
        $productCategory->slug = \Illuminate\Support\Str::slug($request->name);
        $productCategory->description = $request->description;
        $productCategory->save();

        return response()->json(['message' => 'Catégorie de produit ajoutée']);
    }

    public function deleteProductCategory($id)
    {
        $productCategory = ProductCategory::find($id);

        if(!$productCategory){
            return response()->json(['error' => 'Catégorie de produit non trouvée'], 404);
        }

        if ($productCategory->products->count() > 0) {
            foreach ($productCategory->products as $product) {
                $file_path = public_path($product->image);
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $product->delete();
            }
        }

        $productCategory->delete();

        return response()->json(['message' => 'Catégorie de produit supprimée']);
    }

    public function updateProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $productCategory = ProductCategory::find($request->id);
        $productCategory->name = $request->name;
        $productCategory->slug = \Illuminate\Support\Str::slug($request->name);
        $productCategory->description = $request->description;
        $productCategory->save();

        return response()->json(['message' => 'Catégorie de produit modifiée']);
    }
}
