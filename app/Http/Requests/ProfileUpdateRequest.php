<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore(Auth::user()->id),
            ],
            // Foto
            'foto_url' => 'nullable|image|max:2048',
            // Dados pessoais
            'genero' => 'required|string|max:50',
            'data_nascimento' => 'required|date',
            // Documentos
            'cpf' => 'required|string|max:14|unique:users,cpf,' . Auth::user()->id,
            'rg' => 'required|string|min:6|max:16|unique:users,rg,' . Auth::user()->id,
            'uf_rg' => 'required|string|max:2',
            'orgao_emissor_rg' => 'required|string|max:255',
            // Endereço
            'cep' => 'required|string|max:9',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            // Dados de contato
            'telefone' => 'required|string|max:20',
            // Dados bancários
            'conta_bancaria' => 'required|string|max:20',
            'agencia' => 'required|string|max:10',
            'banco_id' => 'required|uuid|exists:bancos,id',
            // Dados profissionais
            'curriculo' => 'required|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'figma_url' => 'nullable|url|max:255',
            'area_atuacao' => 'nullable|string|max:255',
            'tecnologias' => 'nullable|string|max:255',
        ];
    }
}
