<?php

namespace App\Console\Commands;

use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\BonusRules;
use App\Models\Cards;
use Illuminate\Console\Command;

class BonusRulesBill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus_bill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        self::delete();
        self::create();
        return 0;
    }

    private function delete()
    {
        $bills = Bills::select('bills.*')
            ->join('bonus_rules', 'bonus_rules.id', '=', 'bills.rule_id')
            ->get();
        foreach ($bills as $bill) {
            if (strtotime($bill->end_dt) < time() /*|| $bill->value == 0*/) {
                $bill->delete();
            }
        }
    }

    private function create()
    {
        foreach(BonusRules::where('enabled', '=', 1)->get() as $rule) {
            print $rule->id."\n";
            $startDt = $rule->start_dt;
            if (!$startDt) {
                $startDt = date('Y') . '-' . $rule->month . '-' . $rule->day;
            }
            if (time() <= strtotime($startDt) || time() >= strtotime($startDt . ' + ' . $rule->duration . ' days')) {
                print "out of date, $startDt {$rule->duration}\n";
                continue;
            }
            $cards = Cards::select('cards.id', 'bills.rule_id')
                ->join('fields_users', 'fields_users.user_id', '=', 'cards.user_id')
                ->join('bills', 'bills.card_id', '=', 'cards.id')
                ->where([['fields_users.value', '=', 1], ['fields_users.field_id', '=', $rule->field_id]])
                ->get();

            $excludeCardList = [];
            foreach ($cards as $card) {
                if ($card->rule_id == $rule->id)
                    $excludeCardList[] = $card->id;
            }
            $excludeCardList = array_unique($excludeCardList);

            $billProgramId = $remainingAmount = null;
            $programs = BillPrograms::orderBy('from', 'asc')->get();
            if (isset($programs[0]) && $programs[0]->from == 0) {
                $billProgramId = $programs[0]->id;
                $remainingAmount = isset($programs[1]) ? $programs[1]->from : $programs[0]->to;
            }
            foreach (array_unique(array_column($cards->toArray(), 'id')) as $cardId) {
                if (in_array($cardId, $excludeCardList))
                    continue;
                print "rule id=".$rule->id."\n";
                print "card id=".$cardId."\n";
                $bill = new Bills;
                $bill->card_id = $cardId;
                $bill->bill_type_id = BillTypes::where('name', BillTypes::TYPE_BONUS)->value('id');
                $bill->bill_program_id = $billProgramId;
                $bill->remaining_amount = $remainingAmount;
                $bill->value = $rule->value;
                $bill->rule_id = $rule->id;
                $bill->end_dt = date('Y-m-d', strtotime($startDt . ' + ' . $rule->duration . ' days'));
                $bill->save();
            }
        }
    }
}
