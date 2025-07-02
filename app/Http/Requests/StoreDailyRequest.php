<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDailyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'usuario_projeto_id' => 'required|uuid|exists:usuario_projeto,id',
            'data' => 'required|date|before_or_equal:today',
            'ontem' => 'required|string|max:2000',
            'observacoes' => 'nullable|string|max:2000',
            'hoje' => 'required|string|max:2000',
            'carga_horaria' => 'required|integer|min:1|max:9',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'usuario_projeto_id.required' => 'O projeto é obrigatório.',
            'usuario_projeto_id.exists' => 'O projeto selecionado não existe.',
            'data.required' => 'A data é obrigatória.',
            'data.date' => 'A data deve ser uma data válida.',
            'data.before_or_equal' => 'A data não pode ser no futuro.',
            'ontem.required' => 'O campo "O que fiz ontem" é obrigatório.',
            'ontem.max' => 'O campo "O que fiz ontem" deve ter no máximo 2000 caracteres.',
            'observacoes.max' => 'O campo "Observações" deve ter no máximo 2000 caracteres.',
            'hoje.required' => 'O campo "O que farei hoje" é obrigatório.',
            'hoje.max' => 'O campo "O que farei hoje" deve ter no máximo 2000 caracteres.',
            'carga_horaria.required' => 'A carga horária é obrigatória.',
            'carga_horaria.integer' => 'A carga horária deve ser um número inteiro.',
            'carga_horaria.min' => 'A carga horária deve ser de pelo menos 1 hora.',
            'carga_horaria.max' => 'A carga horária deve ser de no máximo 9 horas.',
        ];
    }
}
