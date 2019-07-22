<?php

namespace YHShanto\Token;

use Firebase\JWT\JWT;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use YHShanto\Token\Events\TokenGenerated;
use YHShanto\Token\Events\TokenValidated;
use YHShanto\Token\Models\Consumer;
use YHShanto\Token\Models\Token;

class Driver implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The name of the query string item from the request containing the API token.
     *
     * @var string
     */
    protected $inputKey;

    /**
     * The name of the token "column" in persistent storage.
     *
     * @var string
     */
    protected $consumerKey;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider $provider
     * @param  \Illuminate\Http\Request $request
     * @param  string $inputKey
     * @param  string $consumerKey
     * @return void
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        $inputKey = 'access_token',
        $consumerKey = 'Consumer-Key')
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = $inputKey;
        $this->consumerKey = $consumerKey;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            $data = $this->validate();
            if (isset($data, $data->id)) {
                $user = $this->provider->retrieveById($data->id);
            }
        }

        return $this->user = $user;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        $raw_token = $this->request->bearerToken();

        return Token::where('token', $raw_token)->first();
    }

    /**
     * Get the consumer for the current request.
     *
     * @return string
     */
    public function getConsumerForRequest()
    {
        $publishable_key = $this->request->query($this->consumerKey);

        if (empty($publishable_key)) {
            $publishable_key = $this->request->input($this->consumerKey);
        }

        if (empty($publishable_key)) {
            $publishable_key = $this->request->header($this->consumerKey, '');
        }

        if (empty($publishable_key)) return false;

        $consumer = Consumer::where('publishable_key', $publishable_key)->first();
        if ($consumer) return $consumer;
        return false;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $consumer = $this->getConsumerForRequest();
        /** @var Token $token */
        $token = $this->getTokenForRequest();

        if (!$consumer || !$token) return false;

        $res = $token->isValid($consumer);

        if ($res) {
            event(new TokenValidated($consumer, $token));
            return $res;
        }

        return false;

    }

    public function attempt(array $credentials = [])
    {
        $consumer = $this->getConsumerForRequest();
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user && $this->provider->validateCredentials($user, $credentials)) {
            /** @var HasTokens $user */
            $res = $user->generateToken($consumer);
            if ($res) {
                event(new TokenGenerated($consumer, $res));
                return $res;
            }
            return $res;
        }

        return false;

    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
