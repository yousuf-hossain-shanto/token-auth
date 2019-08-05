<?php

namespace YHShanto\Token;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use YHShanto\Token\Models\Consumer;
use YHShanto\Token\Models\Token;

trait HasTokens
{
    public $inputKey = 'access_token';
    public $consumerKey = 'Consumer-Key';


    public function tokens()

    {

        return $this->morphMany(Token::class, 'user');

    }

    public function generateToken(Consumer $consumer)

    {

        $secret_key = $consumer->secret_key;

        $token = JWT::encode($this->getTokenPayload(), $secret_key);

        return $this->tokens()->create([
            'consumer_id' => $consumer->id,
            'token' => $token,
            'expires_in' => Carbon::now()->addHours(5)
        ]);

    }

    public function getTokenPayload()

    {

        /** @var \App\User $this */
        return [
            "iss" => config('app.url'),
            "aud" => config('app.url'),
            "iat" => time(),
            "data" => $this->getAttributes()
        ];

    }
}
