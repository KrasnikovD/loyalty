<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\TranslationTexts;
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
        // \App\Models\User::factory(10)->create();
       /* Categories::insert([
            'parent_id' => 0,
            'name' => 'default'
        ]);*/
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
    }
}
