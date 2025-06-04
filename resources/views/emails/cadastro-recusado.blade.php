<x-mail::message>

# Status do Cadastro - {{ config('app.name') }}

Prezado(a) {{ $dadosColaborador['name'] }},

Agradecemos seu interesse em participar das atividades do **Laboratório de Computação Inteligente Aplicada (LaCInA)** da UFCG.

Após análise cuidadosa da coordenação, informamos que seu cadastro não pôde ser aprovado neste momento.

@if($observacao ?? false)
## Feedback da Coordenação

{{ $observacao }}

@endif
## Próximos Passos

Para esclarecer dúvidas sobre os critérios de seleção ou obter orientações para uma futura candidatura, recomendamos que entre em contato com a coordenação do laboratório.

Você pode realizar um novo cadastro a qualquer momento, seguindo as diretrizes atualizadas:

<x-mail::button :url="$url">
Realizar Novo Cadastro
</x-mail::button>

<x-mail::panel>
💡 **Dica:** Certifique-se de preencher todos os campos obrigatórios e fornecer informações completas sobre sua experiência acadêmica e profissional.
</x-mail::panel>

Cordialmente,<br>
**Coordenação do LaCInA**<br>
{{ config('app.name') }}

<x-mail::subcopy>
Se o botão "Realizar Novo Cadastro" não funcionar, acesse diretamente: {{ $url }}
</x-mail::subcopy>

</x-mail::message>
