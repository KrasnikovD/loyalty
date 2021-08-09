<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TranslationTexts;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.token');
    }

    /**
     * @api {get} /api/translations/rate_store Get Store Rates Texts
     * @apiName GetStoreRatesTexts
     * @apiGroup AdminTranslations
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function get_rate_store()
    {
        $title = TranslationTexts::getByKey(TranslationTexts::KEY_IM_RATE_STORE_TITLE, config('app.locale'));
        $body = TranslationTexts::getByKey(TranslationTexts::KEY_IM_RATE_STORE_BODY, config('app.locale'));
        return response()->json(['errors' => [], 'data' => ['title' => $title, 'body' => $body]]);
    }

    /**
     * @api {patch} /api/translations/rate_store Set Store Rates Texts
     * @apiName SetStoreRatesTexts
     * @apiGroup AdminTranslations
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [title]
     * @apiParam {string} [body]
     */

    public function update_rate_store(Request $request)
    {
        if ($request->title)
            TranslationTexts::setByKey(TranslationTexts::KEY_IM_RATE_STORE_TITLE, config('app.locale'), $request->title);
        if ($request->body)
            TranslationTexts::setByKey(TranslationTexts::KEY_IM_RATE_STORE_BODY, config('app.locale'), $request->body);
        
        $title = TranslationTexts::getByKey(TranslationTexts::KEY_IM_RATE_STORE_TITLE, config('app.locale'));
        $body = TranslationTexts::getByKey(TranslationTexts::KEY_IM_RATE_STORE_BODY, config('app.locale'));
        return response()->json(['errors' => [], 'data' => ['title' => $title, 'body' => $body]]);
    }
}
