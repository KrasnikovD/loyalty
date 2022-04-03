<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BonusRules extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'bonus_rules';

    const TYPE_BONUS = 'bonus';
    const TYPE_QUESTION = 'question';
}
