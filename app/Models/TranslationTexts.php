<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslationTexts extends Model
{
    use HasFactory;

    protected $table = 'translation_texts';

    const IM_RATE_STORE_TITLE = 'im_rate_store_title';
    const IM_RATE_STORE_BODY = 'im_rate_store_body';
    const AUTH_ENTER_CODE_POPUP_TEXT = 'auth_enter_code_popup_text';
    const AUTH_ENTER_CODE_PLACEHOLDER_TEXT = 'auth_enter_code_placeholder_text';
    const IM_BONUS_NOTIFICATION_TITLE = 'im_bonus_notification_title';
    const IM_BONUS_NOTIFICATION_BODY = 'im_bonus_notification_body';

    public static function getByKey($key, $locale)
    {
        return self::where([['key', '=', $key], ['locale', '=', $locale]])->first()->text;
    }

    public static function setByKey($key, $locale, $value)
    {
        self::where([['key', '=', $key], ['locale', '=', $locale]])->update(['text' => $value]);
    }
}
