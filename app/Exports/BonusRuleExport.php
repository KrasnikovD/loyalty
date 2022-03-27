<?php
namespace App\Exports;

use App\Models\Bills;
use App\Models\CardHistory;
use App\Models\Cards;
use Maatwebsite\Excel\Concerns\FromArray;

class BonusRuleExport implements FromArray
{
    private $ruleId;

    public function __construct($ruleId)
    {
        $this->ruleId = $ruleId;
    }

    public function array(): array
    {
        $bills = Bills::select('cards.id as card_id', 'bills.id as bill_id')
            ->join('cards', 'cards.id', '=', 'bills.card_id')
            ->where('rule_id', '=', $this->ruleId)->get();
        $cardIds = array_column($bills->toArray(), 'card_id');
        $billsIds = array_column($bills->toArray(), 'bill_id');
        $historyData = Cards::select('card_history.id', 'cards.number', 'card_history.type', 'card_history.data', 'card_history.created_at')
            ->join('card_history', 'card_history.card_id', '=', 'cards.id')
            ->whereIn('card_history.type', [CardHistory::BONUS_BY_RULE_ADDED, CardHistory::SALE])
            ->whereIn('cards.id', $cardIds)
            ->orderBy('card_history.created_at', 'desc')
            ->get();

        $report = [];
        foreach ($historyData->toArray() as $entry) {
            $entryData = json_decode($entry['data']);
            if (in_array($entryData->bill_id, $billsIds)) {
                $amount = $entry['type'] == CardHistory::SALE ? "-{$entryData->debited}" : "+{$entryData->value}";
                $report[] = [
                    'number' => $entry['number'],
                    'type' => $entry['type'] == CardHistory::SALE ? 'debited' : 'added',
                    'date' => date('Y-m-d', strtotime($entry['created_at'])),
                    'amount' => $amount
                ];
            }
        }
        return $report;
    }
}
