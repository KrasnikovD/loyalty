<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questions extends Model
{
    protected $table = 'questions';

    use HasFactory;
    use SoftDeletes;

    const TYPE_NUMERIC = 1;
    const TYPE_STRING = 2;
    const TYPE_BOOLEAN = 3;
    const TYPE_OPTIONS = 4;
}
