<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;

/**
 * Class ProductController
 *
 * Contrôleur responsable de la gestion des produits et des catégories.
 * Permet de récupérer les catégories de produits et leurs produits associés.
 *
 * @package App\Http\Controllers
 */
class ProductController extends Controller
{
    /**
     * Retourne toutes les catégories de produits avec leurs produits associés.
     *
     * Cette méthode récupère toutes les catégories de produits et les produits qui y sont associés.
     * Si aucune catégorie n'est trouvée, une erreur est retournée.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function products()
    {
        // Récupérer toutes les catégories de produits avec les produits associés
        $categories = ProductCategory::with('products')->get();

        // Si aucune catégorie n'est trouvée, renvoyer une erreur
        if (!$categories) {
            return response()->json(['error' => 'Aucun produit trouvé']);
        }

        // Retourner les catégories et leurs produits
        return response()->json([
            'categories' => $categories,
        ]);
    }
}
