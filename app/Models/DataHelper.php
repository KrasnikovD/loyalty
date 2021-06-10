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

        $cardList = Cards::select('id', 'number', 'user_id')->whereIn('user_id', $usersIds)->get();
        $cardsIds = array_column($cardList->toArray(), 'id');
        $billsList = Bills::join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
            ->select('bills.id', 'bills.value', 'bills.card_id', 'bill_types.name')
            ->whereIn('bills.card_id', $cardsIds)->get();
        $billsIds = array_column($billsList->toArray(), 'id');

        $billsProgramsList = BillPrograms::select('id', 'bill_id', 'from', 'to', 'percent')
            ->whereIn('bill_id', $billsIds)->get();
        $billsProgramsMap = [];
        foreach ($billsProgramsList as $billsProgram) {
            if(!isset($billsProgramsMap[$billsProgram['bill_id']])) $billsProgramsMap[$billsProgram['bill_id']] = [];
            $billsProgramsMap[$billsProgram['bill_id']][] = $billsProgram->toArray();
        }

        foreach ($billsList as &$billItem) {
            $billItem->programs = @$billsProgramsMap[$billItem->id];
        }

        $billsMap = [];
        foreach ($billsList as $bill) {
            if(!isset($billsMap[$bill['card_id']])) $billsMap[$bill['card_id']] = [];
            $billsMap[$bill['card_id']][] = $bill->toArray();
        }

        foreach ($cardList as &$cardItem) {
            $cardItem->bills = @$billsMap[$cardItem->id];
        }

        $cardMap = [];
        foreach ($cardList as $card) {
            if(!isset($cardMap[$card['user_id']])) $cardMap[$card['user_id']] = [];
            $cardMap[$card['user_id']][] = $card->toArray();
        }

        foreach ($data as &$item) {
            $item['card_list'] = @$cardMap[$item['id']];
        }
    }

    public static function collectCardsInfo(&$data)
    {
        $cardsIds = array_column($data, 'id');
        $billsList = Bills::join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
            ->select('bills.id', 'bills.value', 'bills.card_id', 'bill_types.name')
            ->whereIn('bills.card_id', $cardsIds)->get();
        $billsIds = array_column($billsList->toArray(), 'id');

        $billsProgramsList = BillPrograms::select('id', 'bill_id', 'from', 'to', 'percent')
            ->whereIn('bill_id', $billsIds)->get();
        $billsProgramsMap = [];
        foreach ($billsProgramsList as $billsProgram) {
            if(!isset($billsProgramsMap[$billsProgram['bill_id']])) $billsProgramsMap[$billsProgram['bill_id']] = [];
            $billsProgramsMap[$billsProgram['bill_id']][] = $billsProgram->toArray();
        }

        foreach ($billsList as &$billItem) {
            $billItem->programs = @$billsProgramsMap[$billItem->id];
        }

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
