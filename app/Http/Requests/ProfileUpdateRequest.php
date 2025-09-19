<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ValidCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('campos_extras') && is_string($this->input('campos_extras'))) {
            try {
                $camposExtras = json_decode($this->input('campos_extras'), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($camposExtras)) {
                    $this->merge(['campos_extras' => $camposExtras]);
                } else {
                    $this->offsetUnset('campos_extras');
                }
            } catch (\Exception $e) {
                $this->offsetUnset('campos_extras');
                Log::warning('Erro ao decodificar campos_extras no FormRequest', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::user()->id;

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($userId)],

            // Foto
            'foto_url' => ['nullable', 'file', 'mimes:jpeg,png,bmp,gif,svg,webp', 'max:5120'], // Max 5MB

            // Dados pessoais
            'genero' => ['nullable', 'string', 'max:50'],
            'data_nascimento' => ['nullable', 'date'],

            // Documentos
            'cpf' => ['nullable', 'string', 'max:14', new ValidCpf, Rule::unique(User::class, 'cpf')->ignore($userId)],
            'rg' => ['nullable', 'string', 'min:6', 'max:16', Rule::unique('users', 'rg')->ignore($userId)],
            'uf_rg' => ['nullable', 'string', 'max:2'],
            'orgao_emissor_rg' => ['nullable', 'string', 'max:255'],

            // Endereço
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:10'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'max:2'],

            // Dados de contato
            'telefone' => ['nullable', 'string', 'max:20'],

            // Dados bancários
            'conta_bancaria' => ['nullable', 'string', 'max:20'],
            'agencia' => ['nullable', 'string', 'max:10'],
            'banco_id' => ['nullable', 'uuid', 'exists:bancos,id'],

            // Dados profissionais
            'curriculo_lattes_url' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'area_atuacao' => ['nullable', 'array', 'max:3'],
            'tecnologias' => ['nullable', 'array', 'max:5'],

            'campos_extras' => ['nullable', 'array'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'O campo nome deve ser um texto.',
            'name.max' => 'O campo nome não pode ter mais de :max caracteres.',

            'email.string' => 'O campo e-mail deve ser um texto.',
            'email.lowercase' => 'O campo e-mail deve estar em letras minúsculas.',
            'email.email' => 'Por favor, insira um endereço de e-mail válido.',
            'email.max' => 'O campo e-mail não pode ter mais de :max caracteres.',
            'email.unique' => 'Este e-mail já está cadastrado.',

            'foto_url.file' => 'O campo foto deve ser um arquivo.',
            'foto_url.mimes' => 'A foto deve ser uma imagem do tipo: :values.',
            'foto_url.max' => 'A foto não pode ser maior que :max kilobytes.',

            'genero.string' => 'O campo gênero deve ser um texto.',
            'genero.max' => 'O campo gênero não pode ter mais de :max caracteres.',
            'data_nascimento.date' => 'O campo data de nascimento deve ser uma data válida.',

            'cpf.string' => 'O campo CPF deve ser um texto.',
            'cpf.max' => 'O campo CPF não pode ter mais de :max caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'rg.string' => 'O campo RG deve ser um texto.',
            'rg.min' => 'O campo RG deve ter pelo menos :min caracteres.',
            'rg.max' => 'O campo RG não pode ter mais de :max caracteres.',
            'rg.unique' => 'Este RG já está cadastrado.',

            'uf_rg.string' => 'O campo UF do RG deve ser um texto.',
            'uf_rg.max' => 'O campo UF do RG não pode ter mais de :max caracteres.',

            'orgao_emissor_rg.string' => 'O campo órgão emissor do RG deve ser um texto.',
            'orgao_emissor_rg.max' => 'O campo órgão emissor do RG não pode ter mais de :max caracteres.',

            'cep.string' => 'O campo CEP deve ser um texto.',
            'cep.max' => 'O campo CEP não pode ter mais de :max caracteres.',

            'endereco.string' => 'O campo endereço deve ser um texto.',
            'endereco.max' => 'O campo endereço não pode ter mais de :max caracteres.',

            'numero.string' => 'O campo número deve ser um texto.',
            'numero.max' => 'O campo número não pode ter mais de :max caracteres.',

            'complemento.string' => 'O campo complemento deve ser um texto.',
            'complemento.max' => 'O campo complemento não pode ter mais de :max caracteres.',

            'bairro.string' => 'O campo bairro deve ser um texto.',
            'bairro.max' => 'O campo bairro não pode ter mais de :max caracteres.',

            'cidade.string' => 'O campo cidade deve ser um texto.',
            'cidade.max' => 'O campo cidade não pode ter mais de :max caracteres.',

            'uf.string' => 'O campo UF deve ser um texto.',
            'uf.max' => 'O campo UF não pode ter mais de :max caracteres.',

            'telefone.string' => 'O campo telefone deve ser um texto.',
            'telefone.max' => 'O campo telefone não pode ter mais de :max caracteres.',

            'conta_bancaria.string' => 'O campo conta bancária deve ser um texto.',
            'conta_bancaria.max' => 'O campo conta bancária não pode ter mais de :max caracteres.',

            'agencia.string' => 'O campo agência deve ser um texto.',
            'agencia.max' => 'O campo agência não pode ter mais de :max caracteres.',

            'banco_id.uuid' => 'O ID do banco fornecido não é válido.',
            'banco_id.exists' => 'O banco selecionado não existe em nossos registros.',

            'curriculo_lattes_url.string' => 'O campo URL do Currículo Lattes deve ser um texto.',
            'curriculo_lattes_url.max' => 'O campo URL do Currículo Lattes não pode ter mais de :max caracteres.',

            'linkedin_url.url' => 'O campo URL do LinkedIn deve ser uma URL válida.',
            'linkedin_url.max' => 'O campo URL do LinkedIn não pode ter mais de :max caracteres.',

            'github_url.url' => 'O campo URL do GitHub deve ser uma URL válida.',
            'github_url.max' => 'O campo URL do GitHub não pode ter mais de :max caracteres.',

            'website_url.url' => 'O campo URL do Website deve ser uma URL válida.',
            'website_url.max' => 'O campo URL do Website não pode ter mais de :max caracteres.',

            'area_atuacao.array' => 'O campo área de atuação deve ser uma lista.',
            'area_atuacao.max' => 'O campo área de atuação não pode ter mais de :max itens.',

            'tecnologias.array' => 'O campo tecnologias deve ser uma lista.',
            'tecnologias.max' => 'O campo tecnologias não pode ter mais de :max itens.',

            'campos_extras.array' => 'O campo campos extras deve ser um objeto válido.',
        ];
    }
}
