<?php

namespace Database\Seeders;

use App\Models\TranslationTexts;
use Illuminate\Database\Seeder;

class TranlationTexts1 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $translations = [
            [
                'text' => 'You will now be called, enter the last 4 digits of the number',
                'key' => 'auth_enter_code_popup_text',
                'locale' => 'en'
            ],
            [
                'text' => 'Сейчас вам будет осуществлен звонок, введите последние 4 цифры номера',
                'key' => 'auth_enter_code_popup_text',
                'locale' => 'ru'
            ],
            [
                'text' => 'Enter 4 digits',
                'key' => 'auth_enter_code_placeholder_text',
                'locale' => 'en'
            ],
            [
                'text' => 'Ведите 4 цифры',
                'key' => 'auth_enter_code_placeholder_text',
                'locale' => 'ru'
            ],
        ];
        TranslationTexts::insert($translations);
    }
}
