<x-mail::message>

# Cadastro Não Aprovado - {{ config('app.name') }}

Olá {{ $dadosColaborador['name'] }},

Informamos que seu cadastro no sistema {{ config('app.name') }} não foi aprovado pela coordenação.

Caso você acredite que houve algum equívoco ou deseje obter mais informações sobre o motivo da não aprovação, recomendamos que entre em contato diretamente com a coordenação do laboratório.

Se necessário, você pode realizar um novo cadastro no sistema:

<x-mail::button :url="$url">
Realizar Novo Cadastro
</x-mail::button>

Atenciosamente,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Se o botão não funcionar, copie e cole o seguinte link no seu navegador: [{{ $url }}]({{ $url }})
</x-mail::subcopy>

</x-mail::message>
