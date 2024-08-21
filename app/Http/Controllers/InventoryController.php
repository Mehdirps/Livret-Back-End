<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class InventoryController extends Controller
{
    public function inventories()
    {
        $livret = JWTAuth::parseToken()->authenticate()->livret;

        if (!$livret) {
            return response()->json(['error' => 'Livret introuvable']);
        }

        $inventories = Inventory::where('livret_id', $livret->id)->get();


        if (!$inventories) {
            return response()->json(['error' => 'Aucun inventaire trouvé']);
        }

        return response()->json([
            'inventories' => $inventories,
        ]);
    }

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
        $inventory->start_date = $validatedData['start_date'];
        $inventory->end_date = $validatedData['end_date'];
        $inventory->client_name = $validatedData['client_name'];
        $inventory->status = $validatedData['status'];
        $inventory->client_comment = $validatedData['client_comment'];

        if ($request->hasFile('attachment_names')) {

            $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'pdf', 'xlsx', 'xls', 'doc', 'docx', 'odt', 'ods', 'ppt', 'pptx'];
            $files = $request->file('attachment_names');
            $attachments = [];
            $i = 0;

            foreach ($files as $attachment) {
                $extension = $attachment->getClientOriginalExtension();
                if (in_array(strtolower($extension), $allowedExtensions)) {
                    $filename = $i . time() . '.' . $extension;
                    $attachment->move(public_path('assets/uploads/inventory_attachments'), $filename);
                    $attachments[] = 'assets/uploads/inventory_attachments/' . $filename;
                    $i++;
                } else {
                    return response()->json(['error' => 'Les fichiers doivent être de type png, jpg, jpeg, webp, pdf, xlsx, xls, doc, docx, odt, ods, ppt, pptx']);
                }
            }

            $inventory->attachment_names = json_encode($attachments);
        }


        $inventory->save();

        return response()->json(['message' => 'L\'état des lieux a été ajouté avec succès']);
    }

    public function statusInventory(Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required|string',
            'inventory_id' => 'required|integer',
        ]);


        $inventory = Inventory::find($validatedData['inventory_id']);
        $inventory->status = $validatedData['status'];
        $inventory->save();

        return response()->json(['message' => 'Le status de l\'état des lieux a été mis à jour avec succès']);
    }

    public function deleteInventory($id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['error' => 'Inventaire introuvable']);
        }

        $attachments = json_decode($inventory->attachment_names);

        if ($attachments) {
            foreach ($attachments as $attachment) {
                if (file_exists(public_path($attachment))) {
                    unlink(public_path($attachment));
                }
            }
        }

        $inventory->delete();

        return response()->json(['message' => 'L\'état des lieux a été supprimé avec succès']);
    }

    public function searchInventories(Request $request)
    {
        $validatedData = $request->validate([
            'client_name' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|in:in_progress,completed',
        ]);


        $client_name = $validatedData['client_name'];
        $start_date = $validatedData['start_date'];
        $end_date = $validatedData['end_date'];
        $status = $validatedData['status'];

        $query = Inventory::query();

        if ($client_name) {
            $query->where('client_name', 'like', '%' . $client_name . '%');
        }

        if ($start_date) {
            $query->where('start_date', '>=', $start_date);
        }

        if ($end_date) {
            $query->where('end_date', '<=', $end_date);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $inventories = $query->get();

        return response()->json([
            'inventories' => $inventories,
        ]);
    }
}
