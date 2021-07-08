<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    const STATUS_PRE_ORDER = 0;
    const STATUS_COMPLETED = 1;

    protected $table = 'sales';
}
