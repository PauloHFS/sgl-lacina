<x-mail::message>

# 🧪 {{ $assunto }}

Olá! Este é um email de teste do Sistema de Gestão do LaCInA (SGL LaCInA).

{{ $conteudo }}

---

**Informações do Sistema:**
- **Ambiente:** {{ ucfirst($environment) }}
- **Mailer:** {{ ucfirst($mailer) }}
- **Data/Hora:** {{ $timestamp }}
- **Sistema:** {{ config('app.name') }}

---

**Status do Teste:** ✅ **SUCESSO**

Se você recebeu este email, significa que o sistema de envio de emails está funcionando corretamente em produção.

<x-mail::subcopy>
Este é um email automático de teste do sistema. Não é necessário responder.
</x-mail::subcopy>

</x-mail::message>
