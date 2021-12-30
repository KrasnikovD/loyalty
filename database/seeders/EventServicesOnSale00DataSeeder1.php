<?php

namespace Database\Seeders;

use App\Models\EventServices;
use Illuminate\Database\Seeder;

class EventServicesOnSale00DataSeeder1 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EventServices::insert(
            [
                'trigger' => 'OnSale00',
                'class' => 'App\CustomClasses\Events\OnSale00\SixPercentBonus',
                'method' => 'OnSale00',
                'since' => '2022-01-15',
                'expiration' => '2022-01-31'
            ]
        );
    }
}
