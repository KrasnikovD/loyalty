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
    const TYPE_STAT = 'statistics';

    const TYPE_DATE_TRIGGER_DATE = 1;
    const TYPE_DATE_TRIGGER_MONTHDAY = 2;
    const TYPE_DATE_TRIGGER_BIRTHDAY = 3;

    const TYPE_TRIGGER_SEX = 1;
    const TYPE_TRIGGER_FIELD = 2;
}
