<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

class Users extends Model implements Authenticatable
{
    use HasFactory;

    const TYPE_ADMIN = 0;
    const TYPE_USER = 1;

    protected $table = 'users';

    public function getAuthIdentifier()
    {}

    public function getAuthIdentifierName()
    {}

    public function getAuthPassword()
    {}

    public function getRememberToken()
    {}

    public function getRememberTokenName()
    {}

    public function setRememberToken($value)
    {}
}
