<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataHelper extends Model
{
    use HasFactory;

    public static function collectUsersInfo(&$data)
    {
        $usersIds = array_column($data, 'id');

        $cardList = Cards::select('id', 'number', 'user_id')->whereIn('user_id', $usersIds)->get()->toArray();
        self::collectCardsInfo($cardList);
        /*$cardsIds = array_column($cardList->toArray(), 'id');
        $billsList = Bills::join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
            ->select('bills.id', 'bills.value', 'bills.card_id', 'bill_types.name')
            ->whereIn('bills.card_id', $cardsIds)->get();

        $billsMap = [];
        foreach ($billsList as $bill) {
            if(!isset($billsMap[$bill['card_id']])) $billsMap[$bill['card_id']] = [];
            $billsMap[$bill['card_id']][] = $bill->toArray();
        }

        foreach ($cardList as &$cardItem) {
            $cardItem->bills = @$billsMap[$cardItem->id];
        }*/

        $cardMap = [];
        foreach ($cardList as $card) {
            if(!isset($cardMap[$card['user_id']])) $cardMap[$card['user_id']] = [];
            $cardMap[$card['user_id']][] = $card;
        }

        $userFieldsMap = [];
        $fieldUsers = FieldsUsers::join('fields', 'fields.id', '=', 'fields_users.field_id')
            ->whereIn('user_id', $usersIds)
            ->select('fields_users.user_id', 'fields.name', 'fields_users.value')->get();
        foreach ($fieldUsers as $item) {
            if (!isset($userFieldsMap[$item->user_id])) $userFieldsMap[$item->user_id] = [];
            $userFieldsMap[$item->user_id][] = ['name' => $item->name, 'value' => $item->value];
        }

        foreach ($data as &$item) {
            $item['card_list'] = @$cardMap[$item['id']];
            $item['fields'] = @$userFieldsMap[$item['id']];
        }
    }

    public static function collectCardsInfo(&$data)
    {
        $cardsIds = array_column($data, 'id');
        $billsList = Bills::join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
            ->leftJoin('bill_programs', 'bill_programs.id', '=', 'bills.bill_program_id')
            ->select('bills.id', 'bills.value', 'bills.card_id',
                'bills.remaining_amount', 'bills.bill_program_id',
                'bill_types.name as type_name', 'bill_programs.file', 'bill_programs.percent')
            ->whereIn('bills.card_id', $cardsIds)->get();

        $billsMap = [];
        foreach ($billsList as $bill) {
            if(!isset($billsMap[$bill['card_id']])) $billsMap[$bill['card_id']] = [];
            $billsMap[$bill['card_id']][] = $bill->toArray();
        }

        foreach ($data as &$cardItem) {
            $cardItem['bills'] = @$billsMap[$cardItem['id']];
        }
    }
}
