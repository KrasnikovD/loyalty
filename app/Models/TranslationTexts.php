<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslationTexts extends Model
{
    use HasFactory;

    protected $table = 'translation_texts';

    const KEY_IM_RATE_STORE_TITLE = 'im_rate_store_title';
    const KEY_IM_RATE_STORE_BODY = 'im_rate_store_body';

    public static function getByKey($key, $locale)
    {
        return self::where([['key', '=', $key], ['locale', '=', $locale]])->first()->text;
    }

    public static function setByKey($key, $locale, $value)
    {
        self::where([['key', '=', $key], ['locale', '=', $locale]])->update(['text' => $value]);
    }
}
