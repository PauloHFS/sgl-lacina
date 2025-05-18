<?php

namespace App\Http\Controllers;

use App\Enums\StatusCadastro;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Banco;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $bancos = Banco::all();
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'bancos' => $bancos,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var \Illuminate\Http\Request $request */
        $user = Auth::user();

        // Remover formatação do CPF e CEP
        $request->merge([
            'cpf' => preg_replace('/\D/', '', $request->input('cpf')),
            'cep' => preg_replace('/\D/', '', $request->input('cep')),
        ]);

        if ($request->hasFile('foto_url')) {
            $path = $request->file('foto_url')->store('fotos', 'public');
            $user->foto_url = $path;
        }

        $user->fill($request->except('foto_url'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'Cadastro atualizado com sucesso!');
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

        // Remover formatação do CPF e CEP
        $request->merge([
            'cpf' => preg_replace('/\D/', '', $request->input('cpf')),
            'cep' => preg_replace('/\D/', '', $request->input('cep')),
            'telefone' => preg_replace('/\D/', '', $request->input('telefone')),
            'conta_bancaria' => preg_replace('/\D/', '', $request->input('conta_bancaria')),
            'agencia' => preg_replace('/\D/', '', $request->input('agencia')),
        ]);

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
            'banco_id' => 'required|uuid|exists:bancos,id',
            'conta_bancaria' => 'required|string|max:20',
            'agencia' => 'required|string|max:10',

            // Dados profissionais
            'curriculo_lattes_url' => 'required|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'figma_url' => 'nullable|url|max:255',
        ]);


        $user->status_cadastro = StatusCadastro::PENDENTE;

        $user->fill($request->except('foto_url'));

        if ($request->hasFile('foto_url')) {
            $path = $request->file('foto_url')->store('fotos', 'public');
            $user->foto_url = $path;
        }

        $user->save();

        // Reautenticar o usuário para garantir que as informações atualizadas sejam refletidas
        Auth::login($user);

        if (!$request->routeIs('waiting-approval')) {
            return Redirect::route('waiting-approval');
        }

        return Redirect::route('profile.edit')->with('status', 'Cadastro atualizado com sucesso!');
    }
}
