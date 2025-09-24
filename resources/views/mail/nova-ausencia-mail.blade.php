<x-mail::message>
# Nova Solicitação de Ausência

Uma nova solicitação de ausência foi criada por **{{ $ausencia->user->name }}** no projeto **{{ $ausencia->projeto->nome }}**.

**Período:** {{ $ausencia->data_inicio->format('d/m/Y') }} - {{ $ausencia->data_fim->format('d/m/Y') }}
**Observação:** {{ $ausencia->observacao }}

<x-mail::button :url="route('ausencias.index', ['ausencia' => $ausencia->id])">
Ver solicitação
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
