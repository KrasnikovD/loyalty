<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    const STATUS_PRE_ORDER = 0;
    const STATUS_COMPLETED = 4;
    const STATUS_CANCELED_BY_OUTLET = 5;
    const STATUS_CANCELED_BY_CLIENT = 6;
    const STATUS_CANCELED_BY_ADMIN = 7;

    protected $table = 'sales';
}
