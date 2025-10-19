<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpf implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->isValidCpf($value)) {
            $fail('O :attribute não é um CPF válido.');
        }
    }

    /**
     * Valida se o CPF é válido
     */
    private function isValidCpf(?string $cpf): bool
    {
        if (empty($cpf)) {
            return false;
        }

        // Remove formatação
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Validação do primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cpf[$i] * (10 - $i);
        }
        $resto = ($soma * 10) % 11;
        $digitoVerificador1 = ($resto === 10 || $resto === 11) ? 0 : $resto;

        if ($digitoVerificador1 !== (int) $cpf[9]) {
            return false;
        }

        // Validação do segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int) $cpf[$i] * (11 - $i);
        }
        $resto = ($soma * 10) % 11;
        $digitoVerificador2 = ($resto === 10 || $resto === 11) ? 0 : $resto;

        return $digitoVerificador2 === (int) $cpf[10];
    }
}
