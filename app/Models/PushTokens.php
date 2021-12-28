<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushTokens extends Model
{
    use HasFactory;

    protected $table = 'push_queue';
}
