<x-mail::message>

  # Cadastro Aceito no {{ config('app.name') }}

  Olá {{ $colaborador->name }}, 
  seu cadastro foi aceito com sucesso no sistema {{ config('app.name') }}. Agora você pode acessar sua conta e solicitar um vinculo a algum projeto.

  <x-mail::button :url="$url">
    Acessar Conta
  </x-mail::button>

  <x-mail::subcopy>
    Se o botão não funcionar, copie e cole o seguinte link no seu navegador: [{{ $url }}]({{ $url }})
  </x-mail::subcopy>

</x-mail::message>