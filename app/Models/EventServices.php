<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventServices extends Model
{
    use HasFactory;

    protected $table = 'event_services';

    public static function onLogin(array $params)
    {
        foreach (self::where([['expiration', '>=', date('Y-m-d')], ['trigger', '=', 'onLogin']])->get() as $eventService) {
            $className = $eventService->class;
            if (class_exists($className)) {
                $method = $eventService->method;
                if (method_exists($className, $method)) {
                    $className::$method($params);
                }
            }
        }
    }
}
