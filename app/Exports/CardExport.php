<?php
namespace App\Exports;

use App\Models\Cards;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;

class CardExport implements FromArray
{
    private $programId;
    private $dtStart;
    private $dtEnd;

    public function __construct($programId, $dtStart, $dtEnd)
    {
        $this->programId = $programId;
        $this->dtStart = $dtStart;
        $this->dtEnd = $dtEnd;
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
        if ($this->dtStart && $this->dtEnd) {
            $this->dtStart = date('Y-m-d', strtotime($this->dtStart));
            $this->dtEnd = date('Y-m-d', strtotime($this->dtEnd));
            $q->join('sales', 'sales.card_id', '=', 'cards.id')
                ->where('sales.created_at', '>=', $this->dtStart)
                ->where('sales.created_at', '<=', $this->dtEnd);
        }
        return $q->get()->toArray();
    }
}
