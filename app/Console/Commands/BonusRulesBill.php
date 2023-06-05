<?php

namespace App\Console\Commands;

use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\BonusRules;
use App\Models\Cards;
use App\Models\CommonActions;
use App\Models\Devices;
use App\Notifications\WelcomeNotification;
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
        $billIds = [];
        foreach ($bills as $bill) {
            if (strtotime($bill->end_dt) < time()) {
                $billIds[] = $bill->id;
            }
        }
        $billIds = array_unique($billIds);
        if (!empty($billIds)) {
            $cards = Cards::distinct()
                ->select('cards.*')
                ->join('bills', 'bills.card_id', '=', 'cards.id')
                ->whereIn('bills.id', $billIds)
                ->get();
            $cardsList = [];
            foreach ($cards as $card) {
                $cardsList[$card->id] = $card;
            }
            foreach ($bills as $bill) {
                if (in_array ($bill->id, $billIds)) {
                    CommonActions::cardHistoryLogRemoveBonusByRule($cardsList[$bill->card_id], $bill);
                }
            }
            Bills::whereIn('id', $billIds)->delete();
        }
    }

    private function create()
    {
        foreach(BonusRules::where('enabled', '=', 1)->get() as $rule) {
            $startDt = $rule->start_dt;
            $duration = $rule->duration;
            if ($rule->is_birthday == 0) {
                if (!$startDt) {
                    $startDt = date('Y') . '-' . $rule->month . '-' . $rule->day;
                }
                if (time() <= strtotime($startDt) || time() >= strtotime($startDt . ' + ' . $duration . ' days')) {
                    continue;
                }
            }
            $q = Cards::select('cards.id', 'cards.number', 'cards.is_physical', 'cards.is_main', 'cards.phone', 'bills.rule_id', 'users.birthday', 'users.id as user_id')
                ->join('users', 'users.id', '=', 'cards.user_id')
                ->join('bills', 'bills.card_id', '=', 'cards.id');
            if (is_null($rule->sex))
                $q->join('fields_users', 'fields_users.user_id', '=', 'users.id')
                    ->where([['fields_users.value', '=', 1], ['fields_users.field_id', '=', $rule->field_id]]);
            else
                $q->where('users.sex', '=', $rule->sex);
            $cards = $q->get();

            $excludedCardsList = [];
            foreach ($cards as $card) {
                if ($card->rule_id == $rule->id) {
                    $excludedCardsList[] = $card->id;
                    continue;
                }
                if ($rule->is_birthday == 1) {
                    $month = date('m', strtotime($card->birthday));
                    $day = date('d', strtotime($card->birthday));
                    $userBirthday = date('Y')  . '-' . $month . '-' . $day;
                    $startDt = date('Y-m-d', strtotime($userBirthday . ' - ' . ($duration / 2) . ' days'));
                }
                if (time() <= strtotime($startDt) || time() >= strtotime($startDt . ' + ' . $duration . ' days')) {
                    $excludedCardsList[] = $card->id;
                    continue;
                }
                $card->startDt = $startDt;
            }
            $cardList = [];
            $excludedCardsList = array_unique($excludedCardsList);

            foreach ($cards as $card) {
                if (in_array($card->id, $excludedCardsList))
                    continue;
                $cardList[$card->id] = $card;
            }

            $billProgramId = $remainingAmount = null;
            $programs = BillPrograms::orderBy('from', 'asc')->get();
            if (isset($programs[0]) && $programs[0]->from == 0) {
                $billProgramId = $programs[0]->id;
                $remainingAmount = isset($programs[1]) ? $programs[1]->from : $programs[0]->to;
            }
            foreach ($cardList as $card) {
                $bill = new Bills;
                $bill->card_id = $card->id;
                $bill->bill_type_id = BillTypes::where('name', BillTypes::TYPE_BONUS)->value('id');
                $bill->bill_program_id = $billProgramId;
                $bill->remaining_amount = $remainingAmount;
                $bill->value = $rule->value;
                $bill->rule_id = $rule->id;
                $bill->end_dt = date('Y-m-d', strtotime($card->startDt . ' + ' . $duration . ' days'));
                $bill->rule_name = $rule->name;
                $bill->save();
                CommonActions::cardHistoryLogAddBonusByRule($card, $bill);

                $title = __('messages.im_bill_by_bonus_rule_added_title');
                $body = __('messages.im_bill_by_bonus_rule_added_body', ['end_date' => date('d.m.y', strtotime($bill->end_dt))]);
                $device = Devices::where('user_id', '=', $card->user_id)->first();
                if ($device)
                    $device->notify(new WelcomeNotification($title, $body));
            }
        }
    }
}
