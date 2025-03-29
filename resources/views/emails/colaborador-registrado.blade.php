<x-mail::message>

  # Colaborador Registrado

  Olá Docente, um novo colaborador foi registrado no sistema e está aguardando a sua aprovação.

  ## Dados do Colaborador

  - **Nome:** {{ $colaborador->name }}
  - **E-mail:** {{ $colaborador->email }}

  Obrigado,<br>
  {{ config('app.name') }}

</x-mail::message>