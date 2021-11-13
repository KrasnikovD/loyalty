<?php


namespace App\CustomClasses\Events\OnLogin;


use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\Cards;
use App\Models\CommonActions;
use App\Models\Sales;

class SixPercentBonus
{
    public static function onLogin(array $params)
    {
        $userId = $params['user_id'];
        $billTypeId = BillTypes::where('name', BillTypes::TYPE_DEFAULT)->first()->id;
        $maxProgram = BillPrograms::orderBy('to', 'desc')->first();

        foreach(Cards::select('cards.id', 'bills.id as bill_id', 'bills.bill_program_id', 'bills.init_amount')->join('bills', 'bills.card_id', '=', 'cards.id')
                    ->where([['user_id', $userId], ['bill_type_id', '=', $billTypeId]])->get() as $cardInfo) {
            if ($cardInfo->bill_program_id == $maxProgram->id)
                continue;
            $currentAmount = $cardInfo->init_amount;
            $currentAmount += Sales::where([['bill_id', '=', $cardInfo->bill_id], ['status', '=', Sales::STATUS_COMPLETED]])->sum('amount');
            $diff = $maxProgram->to - $currentAmount;
            if($diff < 0)
                continue;
            $bill = Bills::where('id', $cardInfo->bill_id)->first();
            $bill->init_amount = $bill->init_amount + $diff;
            $bill->bill_program_id = $maxProgram->id;
            $bill->remaining_amount = 0;
            $bill->save();

            CommonActions::cardHistoryLogEditOrCreate(Cards::where('id', $cardInfo->id)->first(), false);
        }
    }
}
