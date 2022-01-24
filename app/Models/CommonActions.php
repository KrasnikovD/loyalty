<?php

namespace App\Models;

use App\Notifications\WelcomeNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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

    public static function sendSms(array $phones, $message)
    {
        return [];
        $ruPnones = [];
        $phenixPhones = [];
        $responseList = [];
        foreach ($phones as $phone) {
            $phone = str_replace(array(' ', '(', ')', '-', '+'), "", $phone);
            if (strpos($phone, '071') === 0 || strpos($phone, '71') === 0 || strpos($phone, '38071') === 0)
                $phenixPhones[] = $phone;
            else $ruPnones[] = $phone;
        }
        if (!empty($phenixPhones)) {
            $params = [
                'phone' => $phenixPhones,
                'message' => $message,
                'key' => "baeb7c755d0aedc018bf52475374c0a8804e3565"
            ];
            $params = json_encode($params);
            $responseList[] = self::curlExec('https://api.c-eda.ru/public/v1/send_sms', $params, true);
        }
        if (!empty($ruPnones)) {
            $phonesStringify = implode(',', $ruPnones);
            $params = [
                'to' => $phonesStringify,
                'msg' => $message,
                'api_id' => "72866002-CB09-F8D8-C28E-2B76945CABA4",
                'json' => 1
            ];
            $params = http_build_query($params);
            $responseList[] = self::curlExec('https://sms.ru/sms/send', $params);
        }
        return $responseList;
    }

    public static function call($phone)
    {
        $params = [
            'phone' => $phone,
            'api_id' => "72866002-CB09-F8D8-C28E-2B76945CABA4",
        ];
        $params = http_build_query($params);
        return CommonActions::curlExec('https://sms.ru/code/call', $params);
    }

    private static function curlExec($url, $params, $json = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($json) curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
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

    public static function calculateDistance ($fA, $lA, $fB, $lB)
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

    public static function randomString($n, $onlyNumbers = false)
    {
        $characters = $onlyNumbers ? '0123456789' : '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        for ($i = 0; $i < $n; $i++)
            $str .= $characters[rand(0, strlen($characters)-1)];
        return $str;
    }

    public static function cardHistoryLogSale($sale, $historyEntry = null)
    {
        $logSale = new CardHistory;
        $data = [
            'sale_id' => $sale->id,
            'amount' => $sale->amount,
            'amount_now' => $sale->amount_now,
            'outlet_id' => $sale->outlet_id,
            'bill_id' => $sale->bill_id,
            'debited' => $sale->debited,
        ];
        if ($historyEntry) {
            $data['bill_program_id'] = $historyEntry->bill_program_id;
            $data['accumulated'] = $historyEntry->accumulated;
            $data['added'] = $historyEntry->added;
            $data['debited'] = $historyEntry->debited;
        }
        $logSale->card_id = $sale->card_id;
        $logSale->type = CardHistory::SALE;
        $logSale->data = json_encode($data);
        $logSale->save();
    }

    public static function cardHistoryLogDelete($cardId)
    {
        $logSale = new CardHistory;
        $logSale->card_id = $cardId;
        $logSale->type = CardHistory::DELETED;
        $logSale->author_id = Auth::user()->id;
        $logSale->save();
    }

    public static function cardHistoryLogEditOrCreate($card, $isCreate, $userId = null)
    {
        $logSale = new CardHistory;
        $logSale->card_id = $card->id;
        $logSale->type = $isCreate ? CardHistory::CREATED : CardHistory::EDITED;
        $data = [
            'number' => $card->number,
            'is_physical' => $card->is_physical,
            'is_main' => $card->is_main,
            'phone' => $card->phone,
        ];
        $logSale->data = json_encode($data);
        $logSale->author_id = $userId ?: @Auth::user()->id;
        $logSale->save();
    }

    public static function cardHistoryLogBind($card, $userId = null)
    {
        $logSale = new CardHistory;
        $logSale->card_id = $card->id;
        $logSale->type = CardHistory::BINDED;
        $data = [
            'number' => $card->number,
            'is_physical' => $card->is_physical,
            'is_main' => $card->is_main,
            'phone' => $card->phone,
        ];
        $logSale->data = json_encode($data);
        $logSale->author_id = $userId ?: Auth::user()->id;
        $logSale->save();
    }

    public static function getBirthdayStockInfo($userId, $saleId, $products)
    {
        if ($saleId) {
            foreach (Baskets::where('sale_id', $saleId)->get() as $item) {
                if ($item->coupon_id) return false;
            }
        }
        if ($products) {
            foreach ($products as $product) {
                if (isset($product['coupon_id'])) return false;
            }
        }
        $user = Users::where('id', $userId)->first();
        if ($user && $user->birthday) {
            $count = config('settings.sale_birthday_stock_day_count');
            $fromDate = date('2000-m-d', strtotime(date('Y-m-d') . " - $count days"));
            $toDate = date('2000-m-d', strtotime(date('Y-m-d') . " + $count days"));
            $birthDay = date('2000-m-d', strtotime($user->birthday));
            if ((strtotime($fromDate) <= strtotime($birthDay)) &&
                (strtotime($toDate) >= strtotime($birthDay))) {
                return config('settings.sale_birthday_stock_value');
            }
        }
        return false;
    }

    public static function sendSalePush($userId, $added, $debited, $outletId)
    {
        $device = Devices::where('user_id', '=', $userId)->first();
        $outletName = Outlet::where('id', $outletId)->first()->name;
        if ($device) {
            $title = TranslationTexts::getByKey(TranslationTexts::KEY_IM_RATE_STORE_TITLE, config('app.locale'));
            $body = TranslationTexts::getByKey(TranslationTexts::KEY_IM_RATE_STORE_BODY, config('app.locale'));
            $device->notify(new WelcomeNotification($title, $body,
                json_encode(['outlet_id' => $outletId, 'outlet_name' => $outletName])));
        }
    }
}
