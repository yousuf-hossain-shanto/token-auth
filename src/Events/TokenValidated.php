<?php
namespace YHShanto\Token\Events;

use YHShanto\Token\Models\Consumer;
use YHShanto\Token\Models\Token;

class TokenValidated
{
    protected $consumer;
    protected $token;

    public function __construct(Consumer $consumer, Token $token)

    {

        $this->consumer = $consumer;
        $this->token = $token;

    }
}
