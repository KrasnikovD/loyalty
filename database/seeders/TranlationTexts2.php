<?php

namespace Database\Seeders;

use App\Models\TranslationTexts;
use Illuminate\Database\Seeder;

class TranlationTexts2 extends Seeder
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
                'text' => '{debited} bonuses were written off for your purchase',
                'key' => 'im_bonus_notification_body',
                'locale' => 'en'
            ],
            [
                'text' => 'За вашу покупку списано {debited} бонусов',
                'key' => 'im_bonus_notification_body',
                'locale' => 'ru'
            ],
            [
                'text' => 'Write-off of bonuses',
                'key' => 'im_bonus_notification_title',
                'locale' => 'en'
            ],
            [
                'text' => 'Списание бонусов',
                'key' => 'im_bonus_notification_title',
                'locale' => 'ru'
            ],
        ];
        TranslationTexts::insert($translations);
    }
}
