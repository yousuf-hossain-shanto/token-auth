<?php

namespace YHShanto\Token\Models;

use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['expires_in'];
    protected $hidden = ['user_type', 'user_id', 'consumer_id', 'id', 'updated_at'];
//    protected $appends = ['user'];

    public function user()

    {

        return $this->morphTo();

    }

    public function consumer()

    {

        return $this->belongsTo(Consumer::class, 'consumer_id', 'id');

    }

    /**
     * @param Consumer $consumer
     * @return bool
     */
    public function isValid(Consumer $consumer)

    {

        if ($consumer->id != $this->consumer->id) return false;
        if ($this->revoked != 0) return false;
        if ($this->expires_in->isPast()) return false;

        $key = $consumer->secret_key;

        try {
            $decoded = JWT::decode($this->token, $key, array('HS256'));
            return $decoded->data;
        } catch (\Exception $exception) {
            return false;
        }

    }
}
