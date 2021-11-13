<?php

namespace App\Console\Commands;

use App\CustomClasses\Events\OnLogin\SixPercentBonus;
use App\Models\Users;
use Illuminate\Console\Command;

class SetSixPercent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set_six_percent';

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
        foreach (Users::whereNotNull('code')->get() as $item) {
           SixPercentBonus::onLogin(['user_id' => $item->id]);
        }

        return Command::SUCCESS;
    }
}
