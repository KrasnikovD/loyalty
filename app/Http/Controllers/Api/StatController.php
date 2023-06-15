<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnswerOptions;
use App\Models\Baskets;
use App\Models\Bills;
use App\Models\CardHistory;
use App\Models\Cards;
use App\Models\ClientAnswers;
use App\Models\DataHelper;
use App\Models\Questions;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StatController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.token');
    }

    /**
     * @api {get} /api/statistic/sales Average Check
     * @apiName AverageCheck
     * @apiGroup AdminStat
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} user_id
     * @apiParam {integer} outlet_id
     * @apiParam {string} [date_from]
     * @apiParam {string} [date_to]
     */

    public function sales(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $q = Sales::select(DB::raw('cast(sales.created_at as date) as date, sum(sales.amount) as total_amount, count(*) as count, sum(bonus_history.added) as total_added, sum(bonus_history.debited) as total_debited'))
                ->leftJoin('bonus_history', 'bonus_history.sale_id', '=', 'sales.id')
                ->groupBy(DB::raw('cast(sales.created_at as date)'));
            if ($request->user_id)
                $q->where('user_id', $request->user_id);
            if ($request->outlet_id)
                $q->where('outlet_id', $request->outlet_id);
            if ($request->date_from && $request->date_to) {
                $from = date("Y-m-d", strtotime($request->date_from));
                $to = date("Y-m-d", strtotime($request->date_to));
                $q->where(DB::raw('cast(sales.created_at as date)'), '>=', $from);
                $q->where(DB::raw('cast(sales.created_at as date)'), '<=', $to);
            }
            $data = $q->get();
            foreach ($data as &$item) {
                $item->average_amount = $item->total_amount / $item->count;
                $item->total_debited = intval($item->total_debited);
            }
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/statistic/product_rates Product Rates
     * @apiName ProductRates
     * @apiGroup AdminStat
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} [limit]
     * @apiParam {string} [date_from]
     * @apiParam {string} [date_to]
     */

    public function product_rates(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make($request->all(), ['limit' => 'integer']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $q = Baskets::select(DB::raw('count(*) as count, baskets.product_id, products.name'))
                ->join('products', 'products.id', '=', 'baskets.product_id')
                ->groupBy('baskets.product_id')
                ->orderBy('count', 'desc');
            if ($request->date_from && $request->date_to) {
                $from = date("Y-m-d", strtotime($request->date_from));
                $to = date("Y-m-d", strtotime($request->date_to));
                $q->where(DB::raw('cast(baskets.created_at as date)'), '>=', $from);
                $q->where(DB::raw('cast(baskets.created_at as date)'), '<=', $to);
            }
            if ($request->limit)
                $q->limit($request->limit);
            $data = $q->get();
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/statistic/bonus_bills_summary/:id Bonus Bills Summary
     * @apiName BonusBillsSummary
     * @apiGroup AdminStat
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function bonus_bills_summary($id)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:bonus_rules,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $cardsCount = Cards::distinct()
                ->join('bills', 'bills.card_id', '=', 'cards.id')
                ->where('bills.rule_id', '=', $id)
                ->count();

            $cards = Cards::select('bills.id', 'card_history.data')
                ->join('bills', 'bills.card_id', '=', 'cards.id')
                ->join('card_history', 'card_history.card_id', '=', 'cards.id')
                ->where([['bills.rule_id', '=', $id], ['card_history.type', '=', CardHistory::BONUS_BY_RULE_ADDED]])
                ->get();
            $addedAmount = 0;
            foreach ($cards as $card) {
                $historyData = json_decode($card->data);
                if ($historyData->rule_id == $id) {
                    $addedAmount += $historyData->value;
                }
            }
            $bills = Bills::select('bonus_history.debited')
                ->join('bonus_history', 'bonus_history.bill_id', '=', 'bills.id')
                ->where('bills.rule_id', '=', $id)->get();
            $debitedCount = count($bills->toArray());
            $debitedAmount = array_sum(array_column($bills->toArray(), 'debited'));
            $data = [
                'card_count' => $cardsCount,
                'total_added_amount' => $addedAmount,
                'debit_count' => $debitedCount,
                'total_debited_amount' => $debitedAmount
            ];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/statistic/question_summary/:id Question Summary
     * @apiName QuestionSummary
     * @apiGroup AdminStat
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function question_summary($id)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:news,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $table = [];
            $questionsData = Questions::where('news_id', '=', $id)->get();
            $optionsData = AnswerOptions::join('questions', 'questions.id', '=', 'answer_options.question_id')
                ->where([['questions.type', '=', 4], ['questions.news_id', '=', $id]])
                ->whereNull('questions.deleted_at')
                ->select('answer_options.text', 'answer_options.question_id')
                ->get();
            $options = [];
            foreach ($optionsData as $datum) {
                if (!isset($options[$datum['question_id']]))
                    $options[$datum['question_id']] = [];
                //$options[$datum['question_id']][] = $datum->toArray();
                $options[$datum['question_id']][] = $datum->text;
            }

            $answersData = ClientAnswers::select('cards.user_id', 'users.first_name', 'users.second_name', 'cards.number', 'client_answers.value', 'client_answers.question_id', 'answer_option_id', 'client_answers.created_at')
                ->join('questions', 'questions.id', '=', 'client_answers.question_id')
                ->join('cards', 'cards.user_id', '=', 'client_answers.client_id')
                ->join('users', 'users.id', '=', 'client_answers.client_id')
                ->whereNull('questions.deleted_at')
                ->where('questions.news_id', '=', $id)
                ->get();

            $clientIds = array_unique(array_column($answersData->toArray(), 'user_id'));
            $answersMap = [];
            $answersMap2 = [];
            foreach ($answersData as $datum) {
                if (!isset($answersMap[$datum['question_id'] . "_" . $datum['user_id']]))
                    $answersMap[$datum['question_id'] . "_" . $datum['user_id']] = [];
                $answersMap[$datum['question_id'] . "_" . $datum['user_id']][] = $datum->toArray();

                if (!isset($answersMap2[$datum['question_id']]))
                    $answersMap2[$datum['question_id']] = [];
                $answersMap2[$datum['question_id']][] = $datum->toArray();
            }

            foreach ($questionsData as $datum) {
                $answers = [];
                $summary = null;
                $positiveCount = 0;
                if(!empty($clientIds)) {
                    foreach ($clientIds as $clientId) {
                        $value = implode(array_column($answersMap[$datum->id . "_" . $clientId], 'value'), ', ');
                        if ($datum->type == Questions::TYPE_BOOLEAN) {
                            if ($value == 1) {
                                $value = 'Да';
                                $positiveCount ++;
                            } else
                                $value = 'Нет';
                        }
                        $mapItem = $answersMap[$datum->id . "_" . $clientId][0];
                        $answers[$datum->id][] = [
                            'client_name' => trim($mapItem['first_name'] . " " . $mapItem['second_name']),
                            'client_card_number' => $mapItem['number'],
                            'value' => $value,
                            'date' => date("Y-m-d H:i:s", strtotime($mapItem['created_at'])),
                        ];
                    }

                    if ($datum->type == Questions::TYPE_BOOLEAN) {
                        $summary = [
                            'Да' => round(($positiveCount / count($clientIds)) * 100, 2),
                            'Нет' => round(((count($clientIds) - $positiveCount) / count($clientIds)) * 100, 2)
                        ];
                    }
                    if ($datum->type == Questions::TYPE_OPTIONS) {

                        $answers2 = [];
                        foreach ($answersMap2[$datum->id] as $item) {
                            if (!isset($answers2[$item['value']]))
                                $answers2[$item['value']] = [];
                            $answers2[$item['value']][] = $item;
                        }

                        $values = array_unique(array_column($answersMap2[$datum->id], 'value'));
                        $summary = [];

                        foreach ($values as $value) {
                            $summary[$value] = round((count($answers2[$value]) / count($answersMap2[$datum->id])) * 100, 2);
                        }
                    }
                }

                $table[] = [
                    'question_name' => $datum->text,
                    'options' => $datum->type == Questions::TYPE_BOOLEAN ? ['Да', 'Нет'] : @$options[$datum->id],
                    'answers' => @$answers[$datum->id],
                    'summary' => $summary
                ];
            }

            $clients = ClientAnswers::join('questions', 'questions.id', '=', 'client_answers.question_id')
                ->where('questions.news_id', '=', $id)
                ->groupBy('client_id')->get();
            $clientsCount = count($clients->toArray());

            $data = [
                'clients_count' => $clientsCount,
                'table' => $table,
            ];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {post} /api/statistic/sales_migrations Sales Migrations Statistic
     * @apiName SalesMigrations
     * @apiGroup AdminStat
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} date_begin_1
     * @apiParam {string} date_end_1
     * @apiParam {string} date_begin_2
     * @apiParam {string} date_end_2
     * @apiParam {integer[]} outlet_ids
     * @apiParam {integer=0,1} [only_losses]
     */

    public function sales_migrations(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make($request->all(), [
            'date_begin_1' => 'required',
            'date_end_1' => 'required',
            'date_begin_2' => 'required',
            'date_end_2' => 'required',
            'outlet_ids' => 'required|array',
            'outlet_ids.*' => 'exists:outlets,id',
            'only_losses' => 'nullable|in:0,1'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $dateBegin1 = date("Y-m-d", strtotime($request->date_begin_1));
            $dateBegin2 = date("Y-m-d", strtotime($request->date_begin_2));
            $dateEnd1 = date("Y-m-d", strtotime($request->date_end_1));
            $dateEnd2 = date("Y-m-d", strtotime($request->date_end_2));
            $data = DataHelper::collectSalesMigrationsInfo($dateBegin1, $dateBegin2, $dateEnd1, $dateEnd2, $request->outlet_ids, $request->only_losses);
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }
}
