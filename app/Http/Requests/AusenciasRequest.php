<?php

namespace App\Http\Requests;

use App\Enums\StatusAusencia;
use App\Models\Ausencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AusenciasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['usuario_id' => Auth::id()]);

        $compensacao_horarios = json_decode($this->input('compensacao_horarios'), true);
        $compensacao_data_inicio = null;
        $compensacao_data_fim = null;

        if (! empty($compensacao_horarios) && is_array($compensacao_horarios)) {
            $datas = array_column($compensacao_horarios, 'data');
            if (! empty($datas)) {
                sort($datas);
                $compensacao_data_inicio = $datas[0];
                $compensacao_data_fim = end($datas);
            }
        }

        $this->merge([
            'compensacao_data_inicio' => $compensacao_data_inicio,
            'compensacao_data_fim' => $compensacao_data_fim,
        ]);
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
            'projeto_id' => ['required', 'uuid', 'exists:projetos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => [
                'required',
                'date',
                'after_or_equal:data_inicio',
                function ($attribute, $value, $fail) {
                    $data_inicio = $this->input('data_inicio');
                    $data_fim = $value;
                    $usuario_id = $this->input('usuario_id');
                    $projeto_id = $this->input('projeto_id');
                    $ausencia_id = $this->route('ausencia') ? $this->route('ausencia')->id : null;
                    $compensacao_data_inicio = $this->input('compensacao_data_inicio');
                    $compensacao_data_fim = $this->input('compensacao_data_fim');

                    // 1. Ausência vs. Ausência (mesmo projeto)
                    $this->checkForOverlap(
                        $fail, $usuario_id, $ausencia_id, $data_inicio, $data_fim, 'data_inicio', 'data_fim',
                        'O período de ausência se sobrepõe a outra ausência para o mesmo projeto.',
                        $projeto_id
                    );

                    if ($compensacao_data_inicio && $compensacao_data_fim) {
                        // 2. Compensação vs. Compensação (qualquer projeto)
                        $this->checkForOverlap(
                            $fail, $usuario_id, $ausencia_id, $compensacao_data_inicio, $compensacao_data_fim, 'compensacao_data_inicio', 'compensacao_data_fim',
                            'O período de compensação se sobrepõe a outra compensação existente.'
                        );

                        // 3. Ausência vs. Compensação (qualquer projeto)
                        $this->checkForOverlap(
                            $fail, $usuario_id, $ausencia_id, $data_inicio, $data_fim, 'compensacao_data_inicio', 'compensacao_data_fim',
                            'O período de ausência se sobrepõe a um período de compensação existente.'
                        );

                        // 4. Compensação vs. Ausência (qualquer projeto)
                        $this->checkForOverlap(
                            $fail, $usuario_id, $ausencia_id, $compensacao_data_inicio, $compensacao_data_fim, 'data_inicio', 'data_fim',
                            'O período de compensação se sobrepõe a um período de ausência existente.'
                        );
                    }
                },
            ],
            'justificativa' => ['required', 'string'],
            'horas_a_compensar' => ['required', 'integer', 'min:1'],
            'compensacao_horarios' => ['required', 'json'],
            'compensacao_data_inicio' => ['nullable', 'date'],
            'compensacao_data_fim' => ['nullable', 'date', 'after_or_equal:compensacao_data_inicio'],
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
     *
     * @param  \Closure  $fail
     * @param  string  $usuario_id
     * @param  int|null  $ausencia_id
     * @param  string  $start
     * @param  string  $end
     * @param  string  $db_start_col
     * @param  string  $db_end_col
     * @param  string  $message
     * @param  string|null  $projeto_id
     * @return void
     */
    private function checkForOverlap($fail, $usuario_id, $ausencia_id, $start, $end, $db_start_col, $db_end_col, $message, $projeto_id = null)
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
