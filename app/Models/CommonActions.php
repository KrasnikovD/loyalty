<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

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

    public static function sendSms($phone, $message)
    {
       /* if (strpos($phone, '071') !== false) {
            return Http::post('https://api.c-eda.ru/public/v1/send_sms',
                [
                    'phone' => $phone,
                    'message' => $message,
                    'key' => "baeb7c755d0aedc018bf52475374c0a8804e3565"
                ]);
        }
        return Http::post('https://sms.ru/sms/send',
            [
                'to' => $phone,
                'msg' => $message,
                'api_id' => "515f19a5-c2c7-fb84-3968-027ff9ad7eaa",
                'json' => 1
            ]);*/
        if (strpos($phone, '071') !== false) {
            $url = 'https://api.c-eda.ru/public/v1/send_sms';
            $params = [
                'phone' => $phone,
                'message' => $message,
                'key' => "baeb7c755d0aedc018bf52475374c0a8804e3565"
            ];
        } else {
            $url = 'https://sms.ru/sms/send';
            $params = [
                'to' => $phone,
                'msg' => $message,
                'api_id' => "515f19a5-c2c7-fb84-3968-027ff9ad7eaa",
                'json' => 1
            ];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
