<?php

namespace App\Console\Commands;

use App\Models\BonusRules;
use Illuminate\Console\Command;

class BonusRulesBill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus_bill';

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
        foreach(BonusRules::all() as $rule) {
            $startDt = $rule->start_dt;
            if (!$startDt) {
                $startDt = date('Y') . '-' . $rule->month . '-' . $rule->day;
            }
            print date('Y-m-d', strtotime($startDt))."\n";
        }
        return 0;
    }
}
