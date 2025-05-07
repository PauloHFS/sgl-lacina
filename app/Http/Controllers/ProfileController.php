<?php

namespace App\Http\Controllers;

use App\Enums\StatusCadastro;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }


    /**
     * Completa o cadastro do usuário.
     * 
     * Veja a interface do forms em resources/js/Pages/PosCadastro.jsx
     */
    public function completarCadastro(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            // Foto
            'foto_url' => 'nullable|image|max:2048',

            // Dados pessoais
            'genero' => 'required|string|max:50',
            'data_nascimento' => 'required|date',

            // Documentos
            'cpf' => 'required|string|max:14|unique:users,cpf,' . $user->id,
            'rg' => 'required|string|max:12|unique:users,rg,' . $user->id,
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
        ]);

        // Remover formatação do CPF
        $request->merge(['cpf' => preg_replace('/\D/', '', $request->input('cpf'))]);

        // Remover formatação do CEP
        $request->merge(['cep' => preg_replace('/\D/', '', $request->input('cep'))]);

        $user->status_cadastro = StatusCadastro::PENDENTE;

        if ($request->hasFile('foto_url')) {
            $path = $request->file('foto_url')->store('fotos', 'public');
            $user->foto_url = $path;
        }

        $user->fill($request->except('foto_url'));
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'Cadastro atualizado com sucesso!');
    }
}
