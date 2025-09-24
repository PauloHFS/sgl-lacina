@component('mail::message')
# Redefinir sua senha

Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.

@component('mail::button', ['url' => $url])
Redefinir senha
@endcomponent

Este link de redefinição de senha expirará em 60 minutos.

Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
