<?php

namespace App\Console\Commands;

use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\Cards;
use App\Models\CommonActions;
use Illuminate\Console\Command;

class fixUsers2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix2 {f}';

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
        $pathToFile = $this->argument('f');
        if (!file_exists($pathToFile))
            die("Invalid arguments");

        $billPrograms = BillPrograms::orderBy('to', 'desc')->get();

        $handle = @fopen($pathToFile, "r");
        if ($handle) {
            while (($buffer = fgets($handle)) !== false) {
                list($cardNumber, $currentAmount) = explode("\t", $buffer);
                $cardNumber = str_repeat('0', 8 - strlen($cardNumber)) . $cardNumber;
                $currentAmount = trim($currentAmount);
                $card = Cards::where('number', $cardNumber)->first();
                if (!$card) {
                    print $cardNumber . "\n";
                } else {
                    print $cardNumber . "\n";
                    self::updateBill($card->id, $currentAmount, $billPrograms);
                    print "\n***********\n";
                }
            }
            fclose($handle);
        }
        return 0;
    }

    private static function updateBill($cardId, $currentAmount, $billPrograms)
    {
        $bill = Bills::where('card_id', $cardId)->first();
        $remainingAmount = null;
        if ($billPrograms) {
            $program = null;
            $maxProgram = $billPrograms[0];
            if ($currentAmount >= $maxProgram->to)
                $program = $maxProgram;
            foreach ($billPrograms as $row) {
                if ($currentAmount >= $row->from && $currentAmount <= $row->to) {
                    $program = $row;
                    break;
                }
            }
            $currentFrom = 0;
            $currentTo = 0;
            if ($program) {
                $currentFrom = $program->from;
                $currentTo = $program->to;
            }
            $nextFrom = BillPrograms::where('from', '>', $currentFrom)->min('from');
            if (!$nextFrom) $nextFrom = $currentTo + 1;
            $remainingAmount = ($currentAmount > $maxProgram->to) ? 0 : $nextFrom - $currentAmount;
            print $currentAmount.' '.$program->percent.' '.$remainingAmount."\n";
            $bill->bill_program_id = $program->id;
            $bill->remaining_amount = $remainingAmount;
            $bill->save();
        }
    }
}
