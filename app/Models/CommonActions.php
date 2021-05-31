<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonActions extends Model
{
    use HasFactory;

    public static function intersection($sections, $from, $to)
    {
        $sets = [];
        foreach ($sections as $item) {
            $sets[] = [];
            for ($i = $item[0]; $i <= $item[1]; $i ++) {
                $sets[count($sets)-1][] = $i;
            }
        }
        $targetSet = [];
        for ($i = $from; $i <= $to; $i ++) {
            $targetSet[] = $i;
        }
        foreach ($sets as $set) {
            foreach ($set as $i) {
                if(in_array($i, $targetSet))
                    return true;
            }
        }
        return false;
    }
}
