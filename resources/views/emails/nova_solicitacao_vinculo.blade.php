<x-mail::message>
@if($usuarioProjeto->trocar)
# Solicitação de Troca de Projeto

Olá, coordenador(a)!

O usuário **{{ $usuarioProjeto->usuario->name }}** solicitou uma troca de projeto para **{{ $usuarioProjeto->projeto->nome }}**.

**Detalhes da Solicitação de Troca:**

- **Nome do Solicitante:** {{ $usuarioProjeto->usuario->name }}
- **E-mail:** {{ $usuarioProjeto->usuario->email }}
- **Função Desejada:** {{ $usuarioProjeto->funcao }}
- **Tipo de Vínculo:** {{ $usuarioProjeto->tipo_vinculo }}
- **Carga Horária:** {{ $usuarioProjeto->carga_horaria }}h/semana
- **Data de Início Solicitada:** {{ \Carbon\Carbon::parse($usuarioProjeto->data_inicio)->format('d/m/Y') }}
- **Data/Hora da Solicitação:** {{ $usuarioProjeto->created_at->format('d/m/Y H:i') }}

<x-mail::button :url="route('colaboradores.show', $usuarioProjeto->usuario_id)">
Avaliar Troca de Projeto
</x-mail::button>

<x-mail::subcopy>
Se o botão não funcionar, acesse: {{ route('colaboradores.show', $usuarioProjeto->usuario_id) }}
</x-mail::subcopy>

@else
# Nova Solicitação de Vínculo ao Projeto

Olá, coordenador(a)!

O usuário **{{ $usuarioProjeto->usuario->name }}** solicitou vínculo ao projeto **{{ $usuarioProjeto->projeto->nome }}**.

**Detalhes da Solicitação:**

- **Nome do Solicitante:** {{ $usuarioProjeto->usuario->name }}
- **E-mail:** {{ $usuarioProjeto->usuario->email }}
- **Função:** {{ $usuarioProjeto->funcao }}
- **Tipo de Vínculo:** {{ $usuarioProjeto->tipo_vinculo }}
- **Carga Horária:** {{ $usuarioProjeto->carga_horaria }}h/mês
- **Data de Início:** {{ \Carbon\Carbon::parse($usuarioProjeto->data_inicio)->format('d/m/Y') }}
- **Data/Hora da Solicitação:** {{ $usuarioProjeto->created_at->format('d/m/Y H:i') }}

<x-mail::button :url="route('colaboradores.show', $usuarioProjeto->usuario_id)">
Avaliar Solicitação
</x-mail::button>

<x-mail::subcopy>
Se o botão não funcionar, acesse: {{ route('colaboradores.show', $usuarioProjeto->usuario_id) }}
</x-mail::subcopy>
@endif
</x-mail::message>