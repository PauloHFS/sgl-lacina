<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyReportRequest extends FormRequest
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
            'data' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'projeto_id' => [
                'required',
                'uuid',
                'exists:projetos,id',
            ],
            'horas_trabalhadas' => [
                'nullable',
                'integer',
                'min:1',
                'max:9',
            ],
            'o_que_fez_ontem' => [
                'required',
                'string',
            ],
            'o_que_vai_fazer_hoje' => [
                'required',
                'string',
            ],
            'observacoes' => [
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
            'data' => 'Data',
            'projeto_id' => 'Projeto',
            'horas_trabalhadas' => 'Horas trabalhadas',
            'o_que_fez_ontem' => 'O que fez ontem',
            'o_que_vai_fazer_hoje' => 'O que vai fazer hoje',
            'observacoes' => 'Observações',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'data.unique' => 'Você já possui um daily report para esta data.',
            'data.before_or_equal' => 'A data do daily report não pode ser no futuro.',
            'projeto_id.exists' => 'O projeto selecionado não existe.',
            'horas_trabalhadas.max' => 'As horas trabalhadas não podem exceder 9 horas.',
        ];
    }
}
