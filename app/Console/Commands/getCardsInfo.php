<?php

namespace App\Console\Commands;

use App\Models\Cards;
use Illuminate\Console\Command;

class getCardsInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'card_info {f}';

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

        $nonExists = [];
        foreach (str_getcsv(file_get_contents($pathToFile), "\n") as &$row) {
            $row = str_getcsv($row, ";");
            if ($card = Cards::where('number', $row[0])->first()) {
                print "number: ".$row[0]." phone: ".$card->phone." fio: ".$card->old_holder_name."\n";
            } else {
                $nonExists[] = $row[0];
            }

        }
        return 0;
    }
}
