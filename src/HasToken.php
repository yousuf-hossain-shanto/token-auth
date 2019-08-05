<?php

namespace YHShanto\Token;

use Firebase\JWT\JWT;

trait HasTokens
{

    public function generateToken()

    {

        $secret_key = sha1(config('app.key'));
        $payload = $this->getTokenPayload();
        $token = JWT::encode($payload, $secret_key);

        return [
            'token' => $token,
            'expires_in' => $payload['exp']
        ];

    }

    public function getTokenPayload()

    {

        /** @var \App\User $this */
        $ti = time();
        return [
            "iss" => config('app.url'),
            "aud" => config('app.url'),
            "iat" => $ti,
            "exp" => ($ti+3600),
            "data" => $this->getAttributes()
        ];

    }
}
