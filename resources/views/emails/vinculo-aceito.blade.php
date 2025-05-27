<x-mail::message>

  # Vinculo Aceito no seguinte projeto: {{ $projeto->nome }}

  Olá {{ $colaborador->name }}, 
  seu vínculo foi aceito com sucesso no projeto {{ $projeto->nome }}.

  <x-mail::button :url="$url">
    Acessar Projeto
  </x-mail::button>

  <x-mail::subcopy>
    Se o botão não funcionar, copie e cole o seguinte link no seu navegador: [{{ $url }}]({{ $url }})
  </x-mail::subcopy>

</x-mail::message>