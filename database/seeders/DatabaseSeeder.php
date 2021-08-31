<?php

namespace Database\Seeders;

use App\Models\BillPrograms;
use App\Models\BillTypes;
use App\Models\Categories;
use App\Models\TranslationTexts;
use App\Models\Users;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
       //  \App\Models\User::factory(10)->create();
        BillTypes::insert([
            'name' => 'default',
        ]);
        Users::insert([
            'first_name' => 'admin',
            'second_name' => 'admin',
            'phone' => '+111111111111',
            'password' => '56c88ccbd5ed243738b643b5ca8446a9',
            'token' => sha1(microtime() . 'salt' . time()),
            'type' => Users::TYPE_ADMIN,
        ]);
        Categories::insert([
            'parent_id' => 0,
            'name' => 'default'
        ]);
       $translations = [
           [
               'text' => 'Rate the store',
               'key' => 'im_rate_store_title',
               'locale' => 'en'
           ],
           [
               'text' => 'Rate the store',
               'key' => 'im_rate_store_title',
               'locale' => 'ru'
           ],
           [
               'text' => 'Rate the store',
               'key' => 'im_rate_store_body',
               'locale' => 'en'
           ],
           [
               'text' => 'Rate the store',
               'key' => 'im_rate_store_body',
               'locale' => 'ru'
           ],
       ];
       TranslationTexts::insert($translations);
       $billPrograms = [
           [
               'from' => 0,
               'to' => 10000,
               'percent' => 2,
               'created_at' => date('Y-m-d H:i:s'),
               'updated_at' => date('Y-m-d H:i:s'),
           ],
           [
               'from' => 10001,
               'to' => 50000,
               'percent' => 4,
               'created_at' => date('Y-m-d H:i:s'),
               'updated_at' => date('Y-m-d H:i:s'),
           ],
           [
               'from' => 50001,
               'to' => 100000,
               'percent' => 6,
               'created_at' => date('Y-m-d H:i:s'),
               'updated_at' => date('Y-m-d H:i:s'),
           ]
       ];
        BillPrograms::insert($billPrograms);
    }
}
