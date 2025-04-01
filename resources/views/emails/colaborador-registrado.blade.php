<x-mail::message>

  # Colaborador Registrado

  Olá Docente, um novo colaborador foi registrado no sistema e está aguardando a sua aprovação.

  ## Dados do Colaborador
  <x-mail::panel>

    **Nome:** {{ $colaborador->name }}

    **E-mail:** {{ $colaborador->email }}

  </x-mail::panel>

  <x-mail::button :url="$url">
    Visualizar Colaborador
  </x-mail::button>

  <x-mail::subcopy>
    Se o botão não funcionar, copie e cole o seguinte link no seu navegador: [{{ $url }}]({{ $url }})
  </x-mail::subcopy>

</x-mail::message>