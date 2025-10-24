<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use App\Mail\Auth\VerifyEmail as VerifyEmailMailable;
use App\Mail\Auth\ResetPassword as ResetPasswordMailable;

class AuthMailableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        VerifyEmailNotification::toMailUsing(function ($notifiable, $url) {
            return (new VerifyEmailMailable($url))->to($notifiable->email);
        });

        ResetPasswordNotification::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new ResetPasswordMailable($url))->to($notifiable->email);
        });
    }
}
