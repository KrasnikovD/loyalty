<?php
namespace App\Exports;

use App\Models\ClientAnswers;
use App\Models\Questions;
use Maatwebsite\Excel\Concerns\FromArray;

class QuestionExport implements FromArray
{
    private $newsId;

    public function __construct($newsId)
    {
        $this->newsId = $newsId;
    }

    public function array(): array
    {
        $answersData = ClientAnswers::select('questions.text as question_text', 'users.id as user_id', 'users.first_name', 'users.second_name', 'cards.number as card_number', 'client_answers.value as answer', 'client_answers.question_id', 'answer_option_id', 'questions.type')
            ->join('questions', 'questions.id', '=', 'client_answers.question_id')
            ->join('cards', 'cards.user_id', '=', 'client_answers.client_id')
            ->join('users', 'users.id', '=', 'client_answers.client_id')
            ->whereNull('questions.deleted_at')
            ->where('questions.news_id', '=', $this->newsId)
            ->get();

        $map1 = [];
        foreach ($answersData as $datum) {
            $map1[$datum['user_id'] . '_' . $datum['question_id']] = $datum->toArray();
        }

        $values = [];
        foreach ($answersData as $datum) {
            $userId = $datum['user_id'];
            $questionId = $datum['question_id'];
            if (!isset($values[$userId . '_' . $questionId]))
                $values[$userId . '_' . $questionId] = [];
            $values[$userId . '_' . $questionId][] = $datum->answer;
        }

        foreach ($map1 as &$item) {
            $rawValue = $values[$item['user_id'] . '_'. $item['question_id']];
            if ($item['type'] == Questions::TYPE_BOOLEAN) {
                $item['answer'] = $rawValue[0] == 1 ? 'Да' : 'Нет';
            }
            if ($item['type'] == Questions::TYPE_OPTIONS) {
                $item['answer'] = implode(',', $rawValue);
            }
        }
        foreach ($map1 as &$item) {
            unset($item['type'], $item['answer_option_id'], $item['user_id'], $item['question_id']);
        }
        $map1 = array_values($map1);
        return $map1;
    }
}
