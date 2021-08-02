<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import_products {f} {c?}';

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
        $file = $this->argument('f');
        $IsParseCategory = $this->argument('c');
        $sourcesData = json_decode(file_get_contents($file));
        if ($IsParseCategory) {
            $categories = array_values(array_unique(array_column($sourcesData, 'category')));
            
        }

        return 0;
    }
}
