<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class CommonActions extends Model
{
    use HasFactory;

    const EARTH_RADIUS = 6372795;

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
        return json_decode($response);
    }

    public static function geocode($lon, $lat)
    {
        $context = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        $result = @json_decode(file_get_contents("https://geocode-maps.yandex.ru/1.x/?format=json&apikey=a664588b-adda-4fc4-adac-e497efe25be4&geocode=$lon,$lat", false, stream_context_create($context)));
        $rootObject = @$result->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData;
        if ($rootObject) {
            $cityName = $streetName = $houseName = null;
            $addressText = $rootObject->text;
            foreach ($rootObject->Address->Components as $component) {
                if ($component->kind == 'locality') $cityName = $component->name;
                if ($component->kind == 'street') $streetName = $component->name;
                if ($component->kind == 'house') $houseName = $component->name;
            }
            return [
                'city' => $cityName,
                'street' => $streetName,
                'house' => $houseName,
                'address' => $addressText,
            ];
        }
        return null;
    }

    public static function calculateTheDistance ($fA, $lA, $fB, $lB)
    {
        $lat1 = $fA * M_PI / 180;
        $lat2 = $fB * M_PI / 180;
        $long1 = $lA * M_PI / 180;
        $long2 = $lB * M_PI / 180;

        $cl1 = cos($lat1);
        $cl2 = cos($lat2);
        $sl1 = sin($lat1);
        $sl2 = sin($lat2);
        $delta = $long2 - $long1;
        $cDelta = cos($delta);
        $sDelta = sin($delta);

        $y = sqrt(pow($cl2 * $sDelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cDelta, 2));
        $x = $sl1 * $sl2 + $cl1 * $cl2 * $cDelta;

        $ad = atan2($y, $x);
        return $ad * self::EARTH_RADIUS;
    }
}
