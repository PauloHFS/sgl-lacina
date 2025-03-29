<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Docente;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                    if ($value !== '1234') {
                        $fail('A senha do laboratório está incorreta.');
                    }
                }
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'email_docente' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'exists:' . User::class . ',email',
                function ($attribute, $value, $fail) {
                    $user = User::where('email', $value)->first();

                    if (!$user || !Docente::where('id', $user->id)->exists()) {
                        $fail('O email informado não pertence a um docente válido.');
                    }
                }
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
