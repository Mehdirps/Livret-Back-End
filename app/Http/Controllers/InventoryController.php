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
    /**
     * Récupère tous les états des lieux associés au livret de l'utilisateur.
     *
     * @OA\Get(
     *    path="/dashboard/inventories",
     *   tags={"Dashboard"},
     *  summary="Récupère tous les états des lieux associés au livret de l'utilisateur.",
     * description="Récupère tous les états des lieux associés au livret de l'utilisateur.",
     * @OA\Response(
     *   response=200,
     * description="Liste des états des lieux."
     * ),
     * @OA\Response(
     *  response=404,
     * description="Aucun inventaire trouvé."
     * )
     * )
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
    /**
     * Ajoute un nouvel état des lieux.
     * 
     * @OA\Post(
     *   path="/dashboard/inventory",
     * tags={"Dashboard"},
     * summary="Ajoute un nouvel état des lieux.",
     * description="Ajoute un nouvel état des lieux.",
     * @OA\RequestBody(
     *   required=true,
     * @OA\JsonContent(
     *  required={"start_date", "end_date", "client_name", "status"},
     * @OA\Property(
     *  property="start_date",
     * type="string",
     * format="date",
     * description="Date de début de l'état des lieux."
     * ),
     * @OA\Property(
     * property="end_date",
     * type="string",
     * format="date",
     * description="Date de fin de l'état des lieux."
     * ),
     * @OA\Property(
     * property="client_name",
     * type="string",
     * description="Nom du client."
     * ),
     * @OA\Property(
     * property="status",
     * type="string",
     * description="Statut de l'état des lieux."
     * ),
     * @OA\Property(
     * property="client_comment",
     * type="string",
     * description="Commentaire du client."
     * ),
     * @OA\Property(
     * property="attachment_names",
     * type="array",
     * @OA\Items(
     * type="string",
     * format="file",
     * description="Fichiers joints."
     * ),
     * description="Fichiers joints."
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="L'état des lieux a été ajouté avec succès."
     * )
     * )
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
    /**
     * Met à jour le statut d'un état des lieux.
     * 
     * @OA\Post(
     *  path="/dashboard/inventory/status",
     * tags={"Dashboard"},
     * summary="Met à jour le statut d'un état des lieux.",
     * description="Met à jour le statut d'un état des lieux.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"status", "inventory_id"},
     * @OA\Property(
     * property="status",
     * type="string",
     * description="Nouveau statut de l'état des lieux."
     * ),
     * @OA\Property(
     * property="inventory_id",
     * type="integer",
     * description="ID de l'état des lieux."
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Le statut de l'état des lieux a été mis à jour avec succès."
     * )
     * )
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
    /**
     * Supprime un état des lieux et ses fichiers joints.
     * 
     * @OA\Delete(
     * path="/dashboard/inventory/{id}",
     * tags={"Dashboard"},
     * summary="Supprime un état des lieux et ses fichiers joints.",
     * description="Supprime un état des lieux et ses fichiers joints.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * @OA\Schema(
     * type="integer"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="L'état des lieux a été supprimé avec succès."
     * ),
     * @OA\Response(
     * response=404,
     * description="Inventaire introuvable."
     * )
     * )
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
    /**
     * Recherche des états des lieux selon les critères donnés.
     * 
     * @OA\Post(
     * path="/dashboard/inventories/search",
     * tags={"Dashboard"},
     * summary="Recherche des états des lieux selon les critères donnés.",
     * description="Recherche des états des lieux selon les critères donnés.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"client_name", "start_date", "end_date", "status"},
     * @OA\Property(
     * property="client_name",
     * type="string",
     * description="Nom du client."
     * ),
     * @OA\Property(
     * property="start_date",
     * type="string",
     * format="date",
     * description="Date de début de l'état des lieux."
     * ),
     * @OA\Property(
     * property="end_date",
     * type="string",
     * format="date",
     * description="Date de fin de l'état des lieux."
     * ),
     * @OA\Property(
     * property="status",
     * type="string",
     * description="Statut de l'état des lieux."
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Liste des états des lieux correspondants."
     * )
     * )
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
