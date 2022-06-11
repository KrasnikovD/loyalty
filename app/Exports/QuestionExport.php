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
        $answersData = ClientAnswers::select('cards.number as card_number', 'users.second_name', 'users.first_name', 'questions.text as question_text', 'client_answers.value as answer', 'users.id as user_id', 'client_answers.question_id', 'answer_option_id', 'questions.type', 'client_answers.created_at')
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
        foreach ($answersData as &$datum) {
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
            $item['created_at'] = date("Y-m-d H:i:s", strtotime($item['created_at']));
            unset($item['type'], $item['answer_option_id'], $item['user_id'], $item['question_id']);
        }
        return array_merge([["№ карты","Фамилия","Имя","Вопрос","Ответ", "Дата"]], array_values($map1));
    }
}
