<?php
namespace App\Exports;

use App\Models\Cards;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;

class CardExport implements FromArray
{
    private $programId;

    public function __construct(array $programId)
    {
        $this->programId = $programId;
    }

    public function array(): array
    {
        $q = Cards::select(DB::raw("concat(users.first_name, ' ', users.second_name) as name"), 'cards.number', 'users.phone', 'bill_programs.percent')
            ->leftJoin('users', 'users.id', '=', 'cards.user_id')
            ->leftJoin('bills', 'cards.id', '=', 'bills.card_id')
            ->leftJoin('bill_programs', 'bill_programs.id', '=', 'bills.bill_program_id');
        if ($this->programId) {
            $q->whereIn('bill_programs.id', $this->programId);
        }
        $result = $q->get()->toArray();
        return $result;
    }
}
