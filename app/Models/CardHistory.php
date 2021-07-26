<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardHistory extends Model
{
    use HasFactory;

    protected $table = 'card_history';

    const CREATED = 'created';
    const EDITED = 'edited';
    const DELETED = 'deleted';
    const BINDED = 'binded';
    const SALE = 'sale';
    const CHANGE_STATUS = 'change_status';
}
