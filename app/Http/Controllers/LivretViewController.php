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

use App\Models\LivretView;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class LivretViewController
 *
 * Contrôleur responsable de la gestion des statistiques de visualisation des livrets.
 * Permet de récupérer des statistiques telles que le nombre de vues totales, hebdomadaires, mensuelles,
 * ainsi que des vues entre des dates spécifiées.
 *
 * @package App\Http\Controllers
 */
class LivretViewController extends Controller
{
    /**
     * Retourne les statistiques de vues d'un livret pour l'utilisateur authentifié.
     *
     * Cette méthode renvoie le nombre total de vues, les vues de la semaine en cours,
     * les vues d'aujourd'hui et les vues du mois en cours pour le livret de l'utilisateur.
     *
     * @param Request $request La requête HTTP.
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        // Vérification que le livret existe
        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        // Récupération du nombre total de vues
        $totalViews = LivretView::where('livret_id', $livret->id)->count();

        // Récupération du nombre de vues de la semaine en cours
        $viewsThisWeek = LivretView::where('livret_id', $livret->id)
            ->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Récupération du nombre de vues aujourd'hui
        $viewsToday = LivretView::where('livret_id', $livret->id)
            ->whereDate('viewed_at', today())
            ->count();

        // Récupération du nombre de vues du mois en cours
        $viewsThisMonth = LivretView::where('livret_id', $livret->id)
            ->whereBetween('viewed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return response()->json([
            'totalViews' => $totalViews,
            'viewsThisWeek' => $viewsThisWeek,
            'viewsToday' => $viewsToday,
            'viewsThisMonth' => $viewsThisMonth,
        ]);
    }

    /**
     * Retourne les statistiques de vues d'un livret entre deux dates spécifiées.
     *
     * Cette méthode permet de récupérer le nombre de vues d'un livret entre une date de début et une date de fin.
     *
     * @param Request $request La requête HTTP contenant les dates de début et de fin.
     * @return \Illuminate\Http\JsonResponse
     */
    public function statsBetweenDates(Request $request)
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        // Validation des dates dans la requête
        $validatedData = $request->validate([
            'start_date' => 'required|string',
            'end_date' => 'required|string',
        ]);

        // Vérification que le livret existe
        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $startDate = $validatedData['start_date'];
        $endDate = $validatedData['end_date'];

        // Initialisation de la variable de vues entre les dates
        $viewsBetweenDates = null;

        // Si les dates de début et de fin sont présentes, calculer les vues entre ces dates
        if ($startDate && $endDate) {
            $endDate = $endDate . ' 23:59:59'; // Ajouter l'heure de fin de journée à la date de fin
            $viewsBetweenDates = LivretView::where('livret_id', $livret->id)
                ->whereBetween('viewed_at', [$startDate, $endDate])
                ->count();
        }

        return response()->json([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'viewsBetweenDates' => $viewsBetweenDates,
        ]);
    }
}
