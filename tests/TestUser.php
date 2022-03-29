<?php

namespace EonVisualMedia\LaravelKlaviyo\Test;

use EonVisualMedia\LaravelKlaviyo\Contracts\KlaviyoIdentity;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TestUser extends Authenticatable implements KlaviyoIdentity
{
    protected $fillable = [
        'id', 'name', 'email', 'password',
    ];

    public function getKlaviyoIdentity(): array
    {
        return [
            '$email' => $this->email,
        ];
    }
}
