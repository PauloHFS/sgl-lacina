<?php

namespace App\Http\Requests;

use App\Enums\StatusAusencia;
use App\Models\Ausencia;
use App\Models\UsuarioProjeto;
use App\Rules\TotalHorasAusencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AusenciasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Se for uma rota de edição (update), o model 'ausencia' estará disponível via route model binding.
        if ($this->route('ausencia')) {
            /** @var Ausencia $ausencia */
            $ausencia = $this->route('ausencia');

            // A autorização passa apenas se o ID do usuário logado for o mesmo que criou a ausência.
            return $ausencia->usuario_id === Auth::id() || Auth::user()->isCoordenador();
        }

        // Para a criação (store), a autorização é sempre permitida.
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Adiciona o ID do usuário logado aos dados da requisição antes da validação.
        $this->merge(['usuario_id' => Auth::id()]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'usuario_id' => ['required', 'uuid', 'exists:users,id'],
            'projeto_id' => [
                'required',
                'uuid',
                'exists:projetos,id',
                // Validação customizada para garantir que o usuário tem um vínculo ativo com o projeto.
                function ($attribute, $value, $fail) {
                    $vinculoAtivo = UsuarioProjeto::where('usuario_id', Auth::id())
                        ->where('projeto_id', $value)
                        ->where('status', 'APROVADO')
                        ->whereNull('data_fim')
                        ->exists();

                    if (! $vinculoAtivo) {
                        $fail('Você não está vinculado a este projeto ou o vínculo não está ativo.');
                    }
                },
            ],
            'titulo' => ['required', 'string', 'max:255'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => [
                'required',
                'date',
                'after_or_equal:data_inicio',
                // Validação customizada para checar sobreposição de datas.
                function ($attribute, $value, $fail) {
                    $data_inicio = $this->input('data_inicio');
                    $data_fim = $value;
                    $usuario_id = $this->input('usuario_id');
                    $projeto_id = $this->input('projeto_id');
                    $ausencia_id = $this->route('ausencia') ? $this->route('ausencia')->id : null;
                    $compensacao_data_inicio = $this->input('compensacao_data_inicio');
                    $compensacao_data_fim = $this->input('compensacao_data_fim');

                    $this->checkForOverlap(
                        $fail, $usuario_id, $ausencia_id, $data_inicio, $data_fim, 'data_inicio', 'data_fim',
                        'O período de ausência se sobrepõe a outra ausência para o mesmo projeto.',
                        $projeto_id
                    );

                    if ($compensacao_data_inicio && $compensacao_data_fim) {
                        $this->checkForOverlap(
                            $fail, $usuario_id, $ausencia_id, $compensacao_data_inicio, $compensacao_data_fim, 'compensacao_data_inicio', 'compensacao_data_fim',
                            'O período de compensação se sobrepõe a outra compensação existente.'
                        );
                        $this->checkForOverlap(
                            $fail, $usuario_id, $ausencia_id, $data_inicio, $data_fim, 'compensacao_data_inicio', 'compensacao_data_fim',
                            'O período de ausência se sobrepõe a um período de compensação existente.'
                        );
                        $this->checkForOverlap(
                            $fail, $usuario_id, $ausencia_id, $compensacao_data_inicio, $compensacao_data_fim, 'data_inicio', 'data_fim',
                            'O período de compensação se sobrepõe a um período de ausência existente.'
                        );
                    }
                },
            ],
            'justificativa' => ['required', 'string'],
            'horas_a_compensar' => ['required', 'integer', 'min:1', new TotalHorasAusencia],
            'compensacao_horarios' => ['required', 'json'],
            'compensacao_data_inicio' => ['required', 'date'],
            'compensacao_data_fim' => ['required', 'date', 'after_or_equal:compensacao_data_inicio'],
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
            '*.required' => 'O campo :attribute é obrigatório.',
            'projeto_id.required' => 'É obrigatório selecionar um projeto.',
            'horas_a_compensar.min' => 'As horas a compensar devem ser pelo menos 1.',
            'compensacao_horarios.required' => 'O plano de compensação é obrigatório.',
        ];
    }

    /**
     * Check for overlapping date ranges for absences.
     */
    private function checkForOverlap($fail, $usuario_id, $ausencia_id, $start, $end, $db_start_col, $db_end_col, $message, $projeto_id = null): void
    {
        $query = Ausencia::where('usuario_id', $usuario_id)
            ->whereIn('status', [StatusAusencia::PENDENTE, StatusAusencia::APROVADO])
            ->when($ausencia_id, fn ($query) => $query->where('id', '!=', $ausencia_id))
            ->when($projeto_id, fn ($query) => $query->where('projeto_id', $projeto_id))
            ->where(function ($query) use ($start, $end, $db_start_col, $db_end_col) {
                $query->where($db_start_col, '<=', $end)
                    ->where($db_end_col, '>=', $start);
            });

        if ($query->exists()) {
            $fail($message);
        }
    }
}
