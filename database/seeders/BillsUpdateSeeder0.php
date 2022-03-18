<?php

namespace Database\Seeders;

use App\Models\BillTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillsUpdateSeeder0 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bill_types')->insert(['name' => BillTypes::TYPE_BONUS]);
    }
}
