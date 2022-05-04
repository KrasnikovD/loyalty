<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baskets;
use App\Models\Bills;
use App\Models\CardHistory;
use App\Models\Cards;
use App\Models\ClientAnswers;
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
            $clients = ClientAnswers::join('questions', 'questions.id', '=', 'client_answers.question_id')
                ->where('questions.news_id', '=', $id)
                ->groupBy('client_id')->get();
            $clientsCount = count($clients->toArray());
            $answerPercents = ClientAnswers::select(DB::raw('count(*) as count'), 'answer_options.text as option_text', 'questions.text as question_text', 'client_answers.answer_option_id')
                ->join('questions', 'questions.id', '=', 'client_answers.question_id')
                ->join('answer_options', 'answer_options.id', '=', 'client_answers.answer_option_id')
                ->whereNotNull('answer_option_id')
                ->where('questions.news_id', '=', $id)
                ->groupBy('client_answers.answer_option_id')
                ->get()->toArray();

            if (count($answerPercents)) {
                $total = array_sum(array_column($answerPercents, 'count'));
                foreach ($answerPercents as &$answer) {
                    $answer['percent'] = round($answer['count'] / $total, 2) * 100;
                }
            }

            $answers = ClientAnswers::select('cards.user_id', 'questions.news_id', 'cards.number', 'client_answers.value', 'questions.text')
                ->join('questions', 'questions.id', '=', 'client_answers.question_id')
                ->join('cards', 'cards.user_id', '=', 'client_answers.client_id')
                ->where('questions.news_id', '=', $id)
                ->whereIn('questions.type', [1,2])
                ->get();
            $data = [
                'clients_count' => $clientsCount,
                'answer_percents' => $answerPercents,
                'answers' => $answers,
            ];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }
}
