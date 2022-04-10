<?php
namespace App\Exports;

use App\Models\ClientAnswers;
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
        $q = ClientAnswers::select('cards.user_id', 'questions.news_id', 'cards.number', 'client_answers.value', 'questions.text')
            ->join('questions', 'questions.id', '=', 'client_answers.question_id')
            ->join('cards', 'cards.user_id', '=', 'client_answers.client_id')
            ->where('questions.news_id', '=', $this->newsId)
            ->whereIn('questions.type', [1,2]);
        return $q->get()->toArray();
    }
}
