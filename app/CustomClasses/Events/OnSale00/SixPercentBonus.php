<?php


namespace App\CustomClasses\Events\OnSale00;


use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\Sales;

class SixPercentBonus
{
    public static function OnSale00(array $params, &$diff)
    {
        $maxProgram = BillPrograms::orderBy('to', 'desc')->first();

        $billId = $params[0];
        $bill = Bills::where('id', '=', $billId)->first();
        if ($bill->bill_program_id == $maxProgram->id) {
            $diff = 0;
            return;
        }
        $currentAmount = $bill->init_amount;
        $currentAmount += Sales::where([['bill_id', '=', $billId], ['status', '=', Sales::STATUS_COMPLETED]])->sum('amount');
        $diff = $maxProgram->to - $currentAmount;
        if($diff < 0) {
            $diff = 0;
            return;
        }
        $bill->init_amount = $bill->init_amount + $diff;
        $bill->bill_program_id = $maxProgram->id;
        $bill->remaining_amount = 0;
        $bill->save();
    }
}
