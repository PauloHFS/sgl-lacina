<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'usuario_id' => [
                'required',
                'uuid',
                'exists:users,id',
            ],
            'projeto_id' => [
                'required',
                'uuid',
                'exists:projetos,id',
            ],
            'titulo' => [
                'required',
                'string',
                'max:255',
            ],
            'data_inicio' => [
                'required',
                'date',
            ],
            'data_fim' => [
                'nullable',
                'date',
                'after_or_equal:data_inicio',
            ],
            'justificativa' => [
                'required',
                'string',
            ],
            'horas_a_compensar' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'compensacao_data_inicio' => [
                'nullable',
                'date',
            ],
            'compensacao_data_fim' => [
                'nullable',
                'date',
                'after_or_equal:compensacao_data_inicio',
            ],
            'compensacao_horarios' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'colaborador_id' => 'Colaborador',
            'projeto_id' => 'Projeto',
            'titulo' => 'Título',
            'data_inicio' => 'Data de início',
            'data_fim' => 'Data de fim',
            'justificativa' => 'Justificativa',
            'horas_a_compensar' => 'Horas a compensar',
            'compensacao_data_inicio' => 'Data início da compensação',
            'compensacao_data_fim' => 'Data fim da compensação',
            'compensacao_horarios' => 'Horários de compensação',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'colaborador_id.required' => 'O colaborador é obrigatório.',
            'colaborador_id.exists' => 'O colaborador selecionado não existe.',
            'projeto_id.required' => 'O projeto é obrigatório.',
            'projeto_id.exists' => 'O projeto selecionado não existe.',
            'titulo.required' => 'O título é obrigatório.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.before_or_equal' => 'A data de início não pode ser no futuro.',
            'data_fim.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
            'justificativa.required' => 'A justificativa é obrigatória.',
            'horas_a_compensar.integer' => 'As horas a compensar devem ser um número inteiro.',
            'compensacao_data_fim.after_or_equal' => 'A data fim da compensação deve ser igual ou posterior à data início da compensação.',
        ];
    }
}
