@component('mail::message')
# Olá, {{ $ausencia->user->name }}!

Sua solicitação de ausência para o período de **{{ $ausencia->data_inicio->format('d/m/Y') }}** a **{{ $ausencia->data_fim->format('d/m/Y') }}** foi **rejeitada**.

**Motivo da rejeição:** {{ $ausencia->motivo_rejeicao ?? 'Não especificado' }}

Para mais detalhes ou para submeter uma nova solicitação, acesse o sistema.

@component('mail::button', ['url' => route('dashboard')])
Ver no Sistema
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
