<?php

namespace App\Http\Controllers\Auth;

use App\Enums\StatusCadastro;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Docente;
use App\Events\ColaboradorRegistrado;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'lab_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $senhaLaboratorio = \App\Models\ConfiguracaoSistema::obterValor('senha_laboratorio');
                    if (!Hash::check($value, $senhaLaboratorio)) {
                        $fail('A senha do laboratório está incorreta.');
                    }
                }
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status_cadastro' => StatusCadastro::IMCOMPLETO,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/pos-cadastro');
    }
}
