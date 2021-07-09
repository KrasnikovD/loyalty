<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillTypes extends Model
{
    use HasFactory;

    protected $table = 'bill_types';

    const TYPE_DEFAULT = 'default';
}
