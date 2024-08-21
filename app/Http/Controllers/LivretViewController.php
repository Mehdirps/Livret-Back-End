<?php

namespace App\Http\Controllers;

use App\Models\LivretView;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class LivretViewController extends Controller
{
    public function stats(Request $request)
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $totalViews = LivretView::where('livret_id', $livret->id)->count();

        $viewsThisWeek = LivretView::where('livret_id', $livret->id)
            ->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $viewsToday = LivretView::where('livret_id', $livret->id)
            ->whereDate('viewed_at', today())
            ->count();

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

    public function statsBetweenDates(Request $request)
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        $validatedData = $request->validate([
            'start_date' => 'required|string',
            'end_date' => 'required|string',
        ]);

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $startDate = $validatedData['start_date'];
        $endDate = $validatedData['end_date'];

        $viewsBetweenDates = null;
        if ($startDate && $endDate) {
            $endDate = $endDate . ' 23:59:59';
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
