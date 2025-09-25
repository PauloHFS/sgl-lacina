<x-mail::message>
# Nova Solicitação de Ausência

Uma nova solicitação de ausência foi criada por **{{ $ausencia->usuario->name }}** no projeto **{{ $ausencia->projeto->nome }}**.

**Titulo:** {{ $ausencia->titulo }}

**Período:** {{ $ausencia->data_inicio->format('d/m/Y') }} - {{ $ausencia->data_fim->format('d/m/Y') }}

<x-mail::button :url="route('ausencias.show', ['ausencia' => $ausencia->id])">
Ver solicitação
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
