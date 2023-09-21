<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DataHelper extends Model
{
    use HasFactory;

    public static function collectUsersInfo(&$data, $isClient = true)
    {
        $usersIds = array_column($data, 'id');

        $cardList = Cards::select('cards.id', 'cards.number', 'cards.user_id', 'users.birthday')
            ->leftJoin('users', 'users.id', '=', 'cards.user_id')
            ->whereIn('user_id', $usersIds)->get()->toArray();
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
        $q = FieldsUsers::join('fields', 'fields.id', '=', 'fields_users.field_id')
            ->whereIn('user_id', $usersIds);
        if ($isClient) $q->where('is_user_editable', '=', 1);
        $q->select('fields_users.user_id', 'fields.id', 'fields.name', 'fields_users.value');
        $fieldUsers = $q->get();
        foreach ($fieldUsers as $item) {
            if (!isset($userFieldsMap[$item->user_id])) $userFieldsMap[$item->user_id] = [];
            $userFieldsMap[$item->user_id][] = [
                'name' => $item->name,
                'field_id' => $item->id,
                'value' => $item->value
            ];
        }

        foreach ($data as &$item) {
            $item['card_list'] = @$cardMap[$item['id']];
            $item['fields'] = @$userFieldsMap[$item['id']];
        }
    }

    public static function collectUserStatInfo(&$data)
    {
        $ids = array_column($data, 'id');
        $map = [];
        foreach (Baskets::select(DB::raw('count(*) as count, sales.user_id, baskets.product_id, products.name'))
                     ->join('products', 'products.id', '=', 'baskets.product_id')
                     ->join('sales', 'sales.id', '=', 'baskets.sale_id')
                     ->whereIn('sales.user_id', $ids)
                     ->groupBy('sales.user_id', 'baskets.product_id')
                     ->orderBy('sales.user_id')->orderBy('count', 'desc')->get()->toArray() as $item) {
            if (!isset($map[$item['user_id']])) $map[$item['user_id']] = $item;
        }
        foreach ($data as &$item) {
            $item['top_product'] = @$map[$item['id']];
        }
    }

    public static function collectOutletStatInfo(&$data)
    {
        $ids = array_column($data, 'id');
        $map = [];
        foreach (Baskets::select(DB::raw('count(*) as count, sales.outlet_id, baskets.product_id, products.name'))
                     ->join('products', 'products.id', '=', 'baskets.product_id')
                     ->join('sales', 'sales.id', '=', 'baskets.sale_id')
                     ->whereIn('sales.outlet_id', $ids)
                     ->groupBy('sales.outlet_id', 'baskets.product_id')
                     ->orderBy('sales.outlet_id')->orderBy('count', 'desc')->get()->toArray() as $item) {
            if (!isset($map[$item['outlet_id']])) $map[$item['outlet_id']] = $item;
        }
        foreach ($data as &$item) {
            $item['top_product'] = @$map[$item['id']];
        }
    }

    public static function collectCardsInfo(&$data)
    {
        $cardsIds = array_column($data, 'id');
        $billsList = Bills::join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
            ->leftJoin('bill_programs', 'bill_programs.id', '=', 'bills.bill_program_id')
            ->select('bills.id', 'bills.value', 'bills.card_id',
                'bills.remaining_amount', 'bills.bill_program_id', 'bills.end_dt', 'bills.rule_id', 'bills.rule_name',
                'bill_types.name as type_name', 'bill_programs.file', 'bill_programs.percent')
            ->orderBy('bills.end_dt', 'asc')
            ->whereIn('bills.card_id', $cardsIds)->get();

        $billsMap = [];
        foreach ($billsList as $bill) {
            if ($bill->value == 0 && $bill->type_name == BillTypes::TYPE_BONUS)
                continue;
            if(!isset($billsMap[$bill['card_id']])) $billsMap[$bill['card_id']] = [];
            $bill->real_value = $bill->value;
            $bill->value = floor($bill->value);
            $billsMap[$bill['card_id']][] = $bill->toArray();
        }

        foreach ($data as &$cardItem) {
            $cardItem['bills'] = @$billsMap[$cardItem['id']];
        }
    }

    public static function collectQuestionsInfo(&$data, $userId = null)
    {
        $answers = ClientAnswers::where('client_id', $userId)->get();
        $questionIds = array_column($answers->toArray(),'question_id');

        $newsIds = array_column($data, 'id');
        $questions = Questions::whereIn('news_id', $newsIds)
            ->whereNotIn('id', $questionIds)
            ->get();
        $questionIds = array_column($questions->toArray(), 'id');
        $answerOptions = AnswerOptions::whereIn('question_id', $questionIds)->get();
        $answersMap = [];
        foreach ($answerOptions as $answerOption) {
            if (!isset($answersMap[$answerOption->question_id]))
                $answersMap[$answerOption->question_id] = [];
            $answersMap[$answerOption->question_id][] = $answerOption;
        }

        foreach ($questions as &$question) {
            $question['answers_options'] = @$answersMap[$question->id];
        }
        $questionMap = [];
        foreach ($questions as $question) {
            if (!isset($questionMap[$question->news_id]))
                $questionMap[$question->news_id] = [];
            $questionMap[$question->news_id][] = $question;
        }
        foreach ($data as &$newsItem) {
            $newsItem['questions'] = @$questionMap[$newsItem['id']];
        }
    }

    public static function collectUsersBySales1($dateFrom, $outletIds = null) {
        $q = Sales::select(DB::raw("outlets.name as outlet_name, outlets.id as outlet_id, users.id as user_id, concat(users.first_name, ' ', users.second_name) as name, users.phone, max(sales.dt) as date"))
            ->join('outlets', 'outlets.id', '=', 'sales.outlet_id')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->where(DB::raw('cast(sales.dt as date)'), '>=', $dateFrom);
        if ($outletIds) {
            $q->whereIn('outlets.id', $outletIds);
            $q->groupBy(DB::raw('outlets.id, users.id'));
        } else {
            $q->groupBy('users.id');
        }
        $raw = $q->get();
        $users = [];
        foreach ($raw as $item) {
            if (!isset($users[$item['user_id']])) {
                $users[$item['user_id']] = [
                    'user_id' => $item['user_id'],
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                ];
            }
        }
        $outlets = [];
        foreach ($raw as $item) {
            if (!isset($outlets[$item['user_id']])) {
                $outlets[$item['user_id']] = [];
            }
            $outlets[$item['user_id']][] = [
                'date' => $item['date'],
                'outlet_id' => $outletIds ? $item['outlet_id'] : null,
                'outlet_name' => $outletIds ? $item['outlet_name'] : null,
            ];
        }
        foreach ($users as &$user) {
            $user['outlets'] = $outlets[$user['user_id']];
        }

        return array_values($users);
    }

    public static function collectUsersBySales2($min, $max, $outletIds) {
        $q =  Sales::select(DB::raw("outlets.name as outlet_name, outlets.id as outlet_id, users.id as user_id, concat(users.first_name, ' ', users.second_name) as name, users.phone, count(*) as count"))
            ->join('outlets', 'outlets.id', '=', 'sales.outlet_id')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereIn('outlets.id', $outletIds)
            ->groupBy(DB::raw('outlets.id, users.id'));
        $raw = DB::table( DB::raw("({$q->toSql()}) as sub") )
            ->mergeBindings($q->getQuery())
            ->where([['count', '>=', $min], ['count', '<=', $max]])
            ->get();
        $users = [];
        foreach ($raw as $item) {
            $item = (array)$item;
            if (!isset($users[$item['user_id']])) {
                $users[$item['user_id']] = [
                    'user_id' => $item['user_id'],
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                ];
            }
        }
        $outlets = [];
        foreach ($raw as $item) {
            $item = (array)$item;
            if (!isset($outlets[$item['user_id']])) {
                $outlets[$item['user_id']] = [];
            }
            $outlets[$item['user_id']][] = [
                'count' => $item['count'],
                'outlet_name' => $item['outlet_name']
            ];
        }
        foreach ($users as &$user) {
            $total = 0;
            foreach ($outlets[$user['user_id']] as $outlet) {
                $total += $outlet['count'];
            }
            $user['outlets'] = $outlets[$user['user_id']];
            $user['total'] = $total;
        }

        return array_values($users);
    }

    public static function collectUsersBySales3($dateBegin, $dateEnd, $min, $max, $outletIds = null) {
        $q =  Sales::select(DB::raw("outlets.name as outlet_name, outlets.id as outlet_id, users.id as user_id, concat(users.first_name, ' ', users.second_name) as name, users.phone, sum(sales.amount) as sum, count(*) as count"))
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->join('outlets', 'outlets.id', '=', 'sales.outlet_id')
            ->where(DB::raw('cast(sales.dt as date)'), '>=', $dateBegin)
            ->where(DB::raw('cast(sales.dt as date)'), '<=', $dateEnd);
        if ($outletIds) {
            $q->whereIn('outlets.id', $outletIds)
                ->groupBy(DB::raw('outlets.id, users.id'));
        } else {
            $q->groupBy(DB::raw('users.id'));
        }

        $raw = DB::table( DB::raw("({$q->toSql()}) as sub") )
            ->mergeBindings($q->getQuery())
            ->where([['sum', '>=', $min], ['sum', '<=', $max]])
            ->get();
        $users = [];
        foreach ($raw as $item) {
            $item = (array)$item;
            if (!isset($users[$item['user_id']])) {
                $users[$item['user_id']] = [
                    'user_id' => $item['user_id'],
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                ];
            }
        }
        $outlets = [];
        foreach ($raw as $item) {
            $item = (array)$item;
            if (!isset($outlets[$item['user_id']])) {
                $outlets[$item['user_id']] = [];
            }
            $outlets[$item['user_id']][] = [
                'sum' => $item['sum'],
                'count' => $item['count'],
                'outlet_name' => $outletIds ? $item['outlet_name'] : null
            ];
        }
        foreach ($users as &$user) {
            $user['outlets'] = $outlets[$user['user_id']];
        }

        return array_values($users);
    }

    public static function collectUsersBySales4($dateBegin, $dateEnd, $min, $max, $outletIds = null) {
        $q =  Sales::select(DB::raw("outlets.name as outlet_name, outlets.id as outlet_id, users.id as user_id, concat(users.first_name, ' ', users.second_name) as name, users.phone, (sum(sales.amount) / count(*)) as sum, count(*) as count"))
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->join('outlets', 'outlets.id', '=', 'sales.outlet_id')
            ->where(DB::raw('cast(sales.dt as date)'), '>=', $dateBegin)
            ->where(DB::raw('cast(sales.dt as date)'), '<=', $dateEnd);
        if ($outletIds) {
            $q->whereIn('outlets.id', $outletIds)
                ->groupBy(DB::raw('outlets.id, users.id'));
        } else {
            $q->groupBy(DB::raw('users.id'));
        }

        $raw = DB::table( DB::raw("({$q->toSql()}) as sub") )
            ->mergeBindings($q->getQuery())
            ->where([['sum', '>=', $min], ['sum', '<=', $max]])
            ->get();
        $users = [];
        foreach ($raw as $item) {
            $item = (array)$item;
            if (!isset($users[$item['user_id']])) {
                $users[$item['user_id']] = [
                    'user_id' => $item['user_id'],
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                ];
            }
        }
        $outlets = [];
        foreach ($raw as $item) {
            $item = (array)$item;
            if (!isset($outlets[$item['user_id']])) {
                $outlets[$item['user_id']] = [];
            }
            $outlets[$item['user_id']][] = [
                'sum' => $item['sum'],
                'count' => $item['count'],
                'outlet_name' => $outletIds ? $item['outlet_name'] : null
            ];
        }
        foreach ($users as &$user) {
            $user['outlets'] = $outlets[$user['user_id']];
        }

        return array_values($users);
    }

    public static function collectUsersBySales5($dateBegin, $dateEnd, $isNovelty, $isStock, $categoryId = null) {
        $q = Sales::select(DB::raw("products.id as product_id, products.code as product_code, products.name as product_name, users.id as user_id, concat(users.first_name, ' ', users.second_name) as name, users.phone, count(*) as count, sum(baskets.count) as sale_count"))
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->join('baskets', 'sales.id', '=', 'baskets.sale_id')
            ->join('products', 'products.id', '=', 'baskets.product_id')
            ->where(DB::raw('cast(sales.dt as date)'), '>=', $dateBegin)
            ->where(DB::raw('cast(sales.dt as date)'), '<=', $dateEnd);
        if ($isNovelty) {
            $q->where('products.is_novelty', '=', 1);
        }
        if ($isStock) {
            $q->where('products.is_stock', '=', 1);
        }
        if ($categoryId) {
            $q->where('products.category_id', '=', $categoryId);
        }
        $raw = $q->groupBy(DB::raw('products.id, users.id'))
            ->orderBy('users.id')
            ->get();

        $users = [];
        foreach ($raw as $item) {
            if (!isset($users[$item['user_id']])) {
                $users[$item['user_id']] = [
                    'user_id' => $item['user_id'],
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                ];
            }
        }

        $products = [];
        $totalCounts = [];
        foreach ($raw as $item) {
            if (!isset($products[$item['user_id']])) {
                $products[$item['user_id']] = [];
                $totalCounts[$item['user_id']] = 0;
            }
            $products[$item['user_id']][] = [
                'id' => $item['product_id'],
                'code' => $item['product_code'],
                'name' => $item['product_name'],
                'sale_count' => $item['sale_count'],
            ];
            $totalCounts[$item['user_id']] += $item['sale_count'];
        }
        foreach ($users as &$user) {
            $user['products'] = $products[$user['user_id']];
            $user['total'] =  $totalCounts[$user['user_id']];
        }
        return array_values($users);
    }

    public static function collectSalesMigrationsInfo($dateBegin1, $dateBegin2, $dateEnd1, $dateEnd2, $outletIds, $onlyLosses = false, $onlyGone = false) {
        $rawData1 = Sales::select(DB::raw("outlets.name as outlet_name, outlets.id as outlet_id, users.id as user_id, concat(users.first_name, ' ', users.second_name) as name, users.phone, count(*) as count"))
            ->join('outlets', 'outlets.id', '=', 'sales.outlet_id')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->where(DB::raw('cast(sales.dt as date)'), '>=', $dateBegin1)
            ->where(DB::raw('cast(sales.dt as date)'), '<=', $dateEnd1)
            ->whereIn('outlets.id', $outletIds)
            ->groupBy(DB::raw('outlets.id, users.id'))
            ->orderBy('users.id')
            ->get();

        $rawData2 = Sales::select(DB::raw("outlets.name as outlet_name, outlets.id as outlet_id, users.id as user_id, concat(users.first_name, ' ', users.second_name) as name, users.phone, count(*) as count"))
            ->join('outlets', 'outlets.id', '=', 'sales.outlet_id')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->where(DB::raw('cast(sales.dt as date)'), '>=', $dateBegin2)
            ->where(DB::raw('cast(sales.dt as date)'), '<=', $dateEnd2)
            ->whereIn('outlets.id', $outletIds)
            ->groupBy(DB::raw('outlets.id, users.id'))
            ->orderBy('users.id')
            ->get();

        $map1 = [];
        foreach ($rawData1 as $row) {
            $index = $row['user_id'] . '_' . $row['outlet_id'] . '_1';
            $map1[$index] = $row['count'];
        }

        $map2 = [];
        foreach ($rawData2 as $row) {
            $index = $row['user_id'] . '_' . $row['outlet_id'] . '_2';
            $map2[$index] = $row['count'];
        }

        $outlets = [];
        foreach ($outletIds as $outletId) {
            foreach ($rawData1 as $row) {
                if ($row['outlet_id'] == $outletId) {
                    $outlets[$outletId] = [
                        'id' => $row['outlet_id'],
                        'name' => $row['outlet_name']
                    ];
                }
            }
            foreach ($rawData2 as $row) {
                if ($row['outlet_id'] == $outletId) {
                    $outlets[$outletId] = [
                        'id' => $row['outlet_id'],
                        'name' => $row['outlet_name']
                    ];
                }
            }
        }

        $usersIds = [];
        foreach ($rawData1 as $row) {
            $usersIds[] = $row['user_id'];
        }
        foreach ($rawData2 as $row) {
            $usersIds[] = $row['user_id'];
        }
        $usersIds = array_unique($usersIds);
        $users = [];
        foreach ($usersIds as $userId) {
            foreach ($rawData1 as $row) {
                if ($row['user_id'] == $userId) {
                    $users[$userId] = [
                        'id' => $row['user_id'],
                        'name' => $row['name'],
                        'phone' => $row['phone'],
                        'outlets' => $outlets
                    ];
                }
            }
            foreach ($rawData2 as $row) {
                if ($row['user_id'] == $userId) {
                    $users[$userId] = [
                        'id' => $row['user_id'],
                        'name' => $row['name'],
                        'phone' => $row['phone'],
                        'outlets' => $outlets
                    ];
                }
            }
        }

        foreach ($users as $userIndex => $user) {
            foreach ($user['outlets'] as $outletIndex => $outlet) {
                $period1Count = intval(@$map1[$user['id'] . '_' . $outlet['id'] . '_1']);
                $period2Count = intval(@$map2[$user['id'] . '_' . $outlet['id'] . '_2']);
                $users[$userIndex]['outlets'][$outletIndex]['period_1_count'] = $period1Count;
                $users[$userIndex]['outlets'][$outletIndex]['period_2_count'] = $period2Count;
                $users[$userIndex]['outlets'][$outletIndex]['diff'] = $period2Count - $period1Count;
            }
        }

        if ($onlyLosses) {
            $usersForDelete = [];
            foreach ($users as $userIndex => $user) {
                $outletsForDelete = [];
                foreach ($user['outlets'] as $outletIndex => $outlet) {
                    if ($outlet['diff'] >= 0) {
                        $outletsForDelete[] = $outletIndex;
                    }
                }
                foreach ($outletsForDelete as $outletIndex) {
                    unset($users[$userIndex]['outlets'][$outletIndex]);
                }
                if (empty($users[$userIndex]['outlets'])) {
                    $usersForDelete[] = $userIndex;
                }
            }
            foreach ($usersForDelete as $userIndex) {
                unset($users[$userIndex]);
            }
        }

        if (!$onlyLosses && $onlyGone) {
            $usersForDelete = [];
            foreach ($users as $userIndex => $user) {
                $outletsForDelete = [];
                foreach ($user['outlets'] as $outletIndex => $outlet) {
                    if (!($outlet['period_2_count'] == 0 && $outlet['period_1_count'] != 0)) {
                        $outletsForDelete[] = $outletIndex;
                    }
                }
                foreach ($outletsForDelete as $outletIndex) {
                    unset($users[$userIndex]['outlets'][$outletIndex]);
                }
                if (empty($users[$userIndex]['outlets'])) {
                    $usersForDelete[] = $userIndex;
                }
            }
            foreach ($usersForDelete as $userIndex) {
                unset($users[$userIndex]);
            }
        }

        foreach ($users as $userIndex => $user) {
            $users[$userIndex]['outlets'] = array_values($users[$userIndex]['outlets']);
        }
        $users = array_values($users);
        return $users;
    }

}
