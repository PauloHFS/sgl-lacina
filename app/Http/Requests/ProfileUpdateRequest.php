<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ValidCpf;
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
            'foto_url' => 'nullable|image|max:5120', // Max 5MB
            // Dados pessoais
            'genero' => 'required|string|max:50',
            'data_nascimento' => 'required|date',
            // Documentos
            'cpf' => ['required', 'string', 'max:14', new ValidCpf(), Rule::unique(User::class, 'cpf')->ignore(Auth::user()->id)],
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
            'uf' => 'required|string|max:2',
            // Dados de contato
            'telefone' => 'required|string|max:20',
            // Dados bancários
            'conta_bancaria' => 'required|string|max:20',
            'agencia' => 'required|string|max:10',
            'banco_id' => 'required|uuid|exists:bancos,id',
            // Dados profissionais
            'curriculo_lattes_url' => 'required|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'area_atuacao' => 'nullable|string|max:255',
            'tecnologias' => 'nullable|string|max:255',

            'campos_extras' => 'nullable|json',
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
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O campo nome deve ser um texto.',
            'name.max' => 'O campo nome não pode ter mais de :max caracteres.',

            'email.required' => 'O campo e-mail é obrigatório.',
            'email.string' => 'O campo e-mail deve ser um texto.',
            'email.lowercase' => 'O campo e-mail deve estar em letras minúsculas.',
            'email.email' => 'Por favor, insira um endereço de e-mail válido.',
            'email.max' => 'O campo e-mail não pode ter mais de :max caracteres.',
            'email.unique' => 'Este e-mail já está cadastrado.',

            'foto_url.image' => 'O arquivo enviado para foto deve ser uma imagem (jpeg, png, bmp, gif, svg, ou webp).',
            'foto_url.max' => 'A foto não pode ser maior que :max kilobytes.',

            'genero.required' => 'O campo gênero é obrigatório.',
            'genero.string' => 'O campo gênero deve ser um texto.',
            'genero.max' => 'O campo gênero não pode ter mais de :max caracteres.',

            'data_nascimento.required' => 'O campo data de nascimento é obrigatório.',
            'data_nascimento.date' => 'O campo data de nascimento deve ser uma data válida.',

            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.string' => 'O campo CPF deve ser um texto.',
            'cpf.max' => 'O campo CPF não pode ter mais de :max caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'rg.required' => 'O campo RG é obrigatório.',
            'rg.string' => 'O campo RG deve ser um texto.',
            'rg.min' => 'O campo RG deve ter pelo menos :min caracteres.',
            'rg.max' => 'O campo RG não pode ter mais de :max caracteres.',
            'rg.unique' => 'Este RG já está cadastrado.',

            'uf_rg.required' => 'O campo UF do RG é obrigatório.',
            'uf_rg.string' => 'O campo UF do RG deve ser um texto.',
            'uf_rg.max' => 'O campo UF do RG não pode ter mais de :max caracteres.',

            'orgao_emissor_rg.required' => 'O campo órgão emissor do RG é obrigatório.',
            'orgao_emissor_rg.string' => 'O campo órgão emissor do RG deve ser um texto.',
            'orgao_emissor_rg.max' => 'O campo órgão emissor do RG não pode ter mais de :max caracteres.',

            'cep.required' => 'O campo CEP é obrigatório.',
            'cep.string' => 'O campo CEP deve ser um texto.',
            'cep.max' => 'O campo CEP não pode ter mais de :max caracteres.',

            'endereco.required' => 'O campo endereço é obrigatório.',
            'endereco.string' => 'O campo endereço deve ser um texto.',
            'endereco.max' => 'O campo endereço não pode ter mais de :max caracteres.',

            'numero.required' => 'O campo número é obrigatório.',
            'numero.string' => 'O campo número deve ser um texto.',
            'numero.max' => 'O campo número não pode ter mais de :max caracteres.',

            'complemento.string' => 'O campo complemento deve ser um texto.',
            'complemento.max' => 'O campo complemento não pode ter mais de :max caracteres.',

            'bairro.required' => 'O campo bairro é obrigatório.',
            'bairro.string' => 'O campo bairro deve ser um texto.',
            'bairro.max' => 'O campo bairro não pode ter mais de :max caracteres.',

            'cidade.required' => 'O campo cidade é obrigatório.',
            'cidade.string' => 'O campo cidade deve ser um texto.',
            'cidade.max' => 'O campo cidade não pode ter mais de :max caracteres.',

            'uf.required' => 'O campo UF é obrigatório.',
            'uf.string' => 'O campo UF deve ser um texto.',
            'uf.max' => 'O campo UF não pode ter mais de :max caracteres.',

            'telefone.required' => 'O campo telefone é obrigatório.',
            'telefone.string' => 'O campo telefone deve ser um texto.',
            'telefone.max' => 'O campo telefone não pode ter mais de :max caracteres.',

            'conta_bancaria.required' => 'O campo conta bancária é obrigatório.',
            'conta_bancaria.string' => 'O campo conta bancária deve ser um texto.',
            'conta_bancaria.max' => 'O campo conta bancária não pode ter mais de :max caracteres.',

            'agencia.required' => 'O campo agência é obrigatório.',
            'agencia.string' => 'O campo agência deve ser um texto.',
            'agencia.max' => 'O campo agência não pode ter mais de :max caracteres.',

            'banco_id.required' => 'O campo banco é obrigatório.',
            'banco_id.uuid' => 'O ID do banco fornecido não é válido.',
            'banco_id.exists' => 'O banco selecionado não existe em nossos registros.',

            'curriculo_lattes_url.required' => 'O campo URL do Currículo Lattes é obrigatório.',
            'curriculo_lattes_url.string' => 'O campo URL do Currículo Lattes deve ser um texto.',
            'curriculo_lattes_url.max' => 'O campo URL do Currículo Lattes não pode ter mais de :max caracteres.',

            'linkedin_url.url' => 'O campo URL do LinkedIn deve ser uma URL válida.',
            'linkedin_url.max' => 'O campo URL do LinkedIn não pode ter mais de :max caracteres.',

            'github_url.url' => 'O campo URL do GitHub deve ser uma URL válida.',
            'github_url.max' => 'O campo URL do GitHub não pode ter mais de :max caracteres.',

            'website_url.url' => 'O campo URL do Website deve ser uma URL válida.',
            'website_url.max' => 'O campo URL do Website não pode ter mais de :max caracteres.',

            'area_atuacao.string' => 'O campo área de atuação deve ser um texto.',
            'area_atuacao.max' => 'O campo área de atuação não pode ter mais de :max caracteres.',

            'tecnologias.string' => 'O campo tecnologias deve ser um texto.',
            'tecnologias.max' => 'O campo tecnologias não pode ter mais de :max caracteres.',

            'campos_extras.json' => 'O campo campos extras deve estar em formato JSON válido.',
        ];
    }
}
