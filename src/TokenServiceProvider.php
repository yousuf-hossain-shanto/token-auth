<?php

namespace YHShanto\Token;

use Illuminate\Auth\CreatesUserProviders;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class TokenServiceProvider extends ServiceProvider
{
    use CreatesUserProviders;

    public function register()
    {
        Auth::extend('yh_token', function ($app, $name, $config) {
            return new Driver($this->createUserProvider($config['provider'] ?? null), app('request'));
        });
    }
}
