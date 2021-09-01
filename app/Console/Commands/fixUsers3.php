<?php

namespace App\Console\Commands;

use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\Cards;
use Illuminate\Console\Command;

class fixUsers3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix3 {f}';

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
        $unExisted = [];
        if ($handle) {
            while (($buffer = fgets($handle)) !== false) {
                list($cardNumber, $currentAmount) = explode(" ", $buffer);
                if (strlen($cardNumber) > 8) continue;
                $cardNumber = str_repeat('0', 8 - strlen($cardNumber)) . $cardNumber;

                $currentAmount = trim($currentAmount);
                $card = Cards::where('number', $cardNumber)->first();
                if (!$card) {
                    $unExisted[] = $cardNumber;
                } else {
                    print $cardNumber . "\n";
                    $bill = Bills::where('card_id', $card->id)->first();
                    $bill->init_amount = $currentAmount;
                    $bill->save();
                    print "\n***********\n";
                }
            }
            fclose($handle);
        }
        print_r($unExisted);
        return 0;
    }
}
