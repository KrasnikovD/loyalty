<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cards extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cards';

    const ACTIVE = 0;
    const BLOCKED = 1;

    public static function getCertAmount($cert)
    {
        switch ($cert) {
            case 1:
                return 1000;
            case 2:
                return 2000;
            case 3:
                return 3000;
        }
    }
}
