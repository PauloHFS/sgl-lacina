@component('mail::message')
# Verifique seu endereço de e-mail

Por favor, clique no botão abaixo para verificar seu endereço de e-mail.

@component('mail::button', ['url' => $url])
Verificar endereço de e-mail
@endcomponent

Se você não criou uma conta, nenhuma ação adicional é necessária.

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
