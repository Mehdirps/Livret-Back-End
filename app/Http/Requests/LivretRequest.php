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

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LivretRequest extends FormRequest
{
    public mixed $user_id;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'livret_name' => 'required|string|max:255',
            'establishment_name' => 'required|string|max:255',
            'establishment_address' => 'required|string|max:255',
            'establishment_phone' => 'required|string|regex:/^(\+\d{1,3})?\d{7,14}$/',
            'establishment_email' => 'required|email|max:255',
            'establishment_type' => 'required|string|max:255',
            'establishment_website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'tripadvisor' => 'nullable|string|max:255',
        ];
    }
}
