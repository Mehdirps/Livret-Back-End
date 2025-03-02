<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Contrôleur de gestion des états des lieux.
 *
 * Permet d'effectuer les opérations CRUD sur les états des lieux associés à un livret.
 */
class InventoryController extends Controller
{
    /**
     * Récupère tous les états des lieux associés au livret de l'utilisateur.
     *
     * @return \Illuminate\Http\JsonResponse Réponse JSON contenant les états des lieux.
     */
    public function inventories()
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $inventories = Inventory::where('livret_id', $livret->id)->get();

        if ($inventories->isEmpty()) {
            return response()->json(['error' => 'Aucun inventaire trouvé']);
        }

        return response()->json(['inventories' => $inventories]);
    }

    /**
     * Ajoute un nouvel état des lieux.
     *
     * @param Request $request Requête contenant les informations de l'état des lieux.
     * @return \Illuminate\Http\JsonResponse Réponse JSON confirmant l'ajout de l'état des lieux.
     */
    public function addInventory(Request $request)
    {
        $validatedData = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'client_name' => 'required|string',
            'status' => 'required|string',
            'client_comment' => 'nullable|string',
            'attachment_names.*' => 'nullable|file|mimes:pdf,png,jpeg,webp,jpg',
        ]);

        $inventory = new Inventory;
        $inventory->livret_id = JWTAuth::parseToken()->authenticate()->livret->id;
        $inventory->fill($validatedData);

        // Gestion des fichiers joints
        if ($request->hasFile('attachment_names')) {
            $attachments = [];
            foreach ($request->file('attachment_names') as $key => $attachment) {
                $extension = $attachment->getClientOriginalExtension();
                $filename = $key . time() . '.' . $extension;
                $attachment->move(public_path('assets/uploads/inventory_attachments'), $filename);
                $attachments[] = 'assets/uploads/inventory_attachments/' . $filename;
            }
            $inventory->attachment_names = json_encode($attachments);
        }

        $inventory->save();

        return response()->json(['message' => 'L\'état des lieux a été ajouté avec succès']);
    }

    /**
     * Met à jour le statut d'un état des lieux.
     *
     * @param Request $request Requête contenant l'ID de l'état des lieux et son nouveau statut.
     * @return \Illuminate\Http\JsonResponse Réponse JSON confirmant la mise à jour.
     */
    public function statusInventory(Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required|string',
            'inventory_id' => 'required|integer',
        ]);

        $inventory = Inventory::find($validatedData['inventory_id']);

        if (!$inventory) {
            return response()->json(['error' => 'Inventaire introuvable']);
        }

        $inventory->status = $validatedData['status'];
        $inventory->save();

        return response()->json(['message' => 'Le statut de l\'état des lieux a été mis à jour avec succès']);
    }

    /**
     * Supprime un état des lieux et ses fichiers joints.
     *
     * @param int $id ID de l'état des lieux à supprimer.
     * @return \Illuminate\Http\JsonResponse Réponse JSON confirmant la suppression.
     */
    public function deleteInventory($id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['error' => 'Inventaire introuvable']);
        }

        // Suppression des fichiers joints
        if ($inventory->attachment_names) {
            foreach (json_decode($inventory->attachment_names) as $attachment) {
                $filePath = public_path($attachment);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $inventory->delete();

        return response()->json(['message' => 'L\'état des lieux a été supprimé avec succès']);
    }

    /**
     * Recherche des états des lieux selon les critères donnés.
     *
     * @param Request $request Requête contenant les critères de recherche.
     * @return \Illuminate\Http\JsonResponse Réponse JSON contenant les états des lieux correspondants.
     */
    public function searchInventories(Request $request)
    {
        $validatedData = $request->validate([
            'client_name' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|in:in_progress,completed',
        ]);

        $query = Inventory::query();

        if ($validatedData['client_name']) {
            $query->where('client_name', 'like', '%' . $validatedData['client_name'] . '%');
        }

        if ($validatedData['start_date']) {
            $query->where('start_date', '>=', $validatedData['start_date']);
        }

        if ($validatedData['end_date']) {
            $query->where('end_date', '<=', $validatedData['end_date']);
        }

        if ($validatedData['status']) {
            $query->where('status', $validatedData['status']);
        }

        return response()->json(['inventories' => $query->get()]);
    }
}
