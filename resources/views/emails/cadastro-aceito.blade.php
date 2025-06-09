<x-mail::message>

# 🎉 Bem-vindo(a) ao {{ config('app.name') }}!

Olá **{{ $colaborador->name }}**,

@if (isset($observacao) && !empty($observacao))
Atenciosamente informamos que seu cadastro foi **aprovado com pendências**! O coordenador deixou a seguinte mensagem para você:
@else
É com satisfação que informamos que seu cadastro foi **aprovado com sucesso**!
@endif

@if(isset($observacao) && !empty($observacao))
<x-mail::panel>
**💬 Mensagem do Coordenador:**

{{ $observacao }}
</x-mail::panel>
@endif

## Próximos Passos

Agora você pode:

- ✅ Acessar sua conta no sistema
- 🔍 Visualizar os projetos disponíveis
- 📝 Solicitar vínculo aos projetos de seu interesse
- 📊 Acompanhar o status de suas solicitações

<x-mail::button :url="$url">
🚀 Acessar Minha Conta
</x-mail::button>

---

**Dúvidas?** Entre em contato com a coordenação do laboratório ou consulte a documentação do sistema.

**Laboratório de Computação Inteligente Aplicada (LaCInA)**  
*Universidade Federal de Campina Grande*

<x-mail::subcopy>
Se o botão não funcionar, copie e cole o seguinte link no seu navegador: {{ $url }}
</x-mail::subcopy>

</x-mail::message>