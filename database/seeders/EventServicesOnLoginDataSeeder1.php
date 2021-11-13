<?php

namespace Database\Seeders;

use App\Models\EventServices;
use Illuminate\Database\Seeder;

class EventServicesOnLoginDataSeeder1 extends Seeder
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
                'trigger' => 'onLogin',
                'class' => 'App\CustomClasses\Events\OnLogin\SixPercentBonus',
                'method' => 'onLogin',
                'expiration' => '2021-12-31'
            ]
        );
    }
}
