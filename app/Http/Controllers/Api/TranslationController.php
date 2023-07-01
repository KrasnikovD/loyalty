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
        $title = TranslationTexts::getByKey(TranslationTexts::IM_RATE_STORE_TITLE, config('app.locale'));
        $body = TranslationTexts::getByKey(TranslationTexts::IM_RATE_STORE_BODY, config('app.locale'));
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
            TranslationTexts::setByKey(TranslationTexts::IM_RATE_STORE_TITLE, config('app.locale'), $request->title);
        if ($request->body)
            TranslationTexts::setByKey(TranslationTexts::IM_RATE_STORE_BODY, config('app.locale'), $request->body);

        $title = TranslationTexts::getByKey(TranslationTexts::IM_RATE_STORE_TITLE, config('app.locale'));
        $body = TranslationTexts::getByKey(TranslationTexts::IM_RATE_STORE_BODY, config('app.locale'));
        return response()->json(['errors' => [], 'data' => ['title' => $title, 'body' => $body]]);
    }

    /**
     * @api {get} /api/translations/auth_code_texts Get Auth Code Texts
     * @apiName GetAuthCodeTexts
     * @apiGroup AdminTranslations
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function get_auth_code_texts()
    {
        $popupText = TranslationTexts::getByKey(TranslationTexts::AUTH_ENTER_CODE_POPUP_TEXT, config('app.locale'));
        $placeholderText = TranslationTexts::getByKey(TranslationTexts::AUTH_ENTER_CODE_PLACEHOLDER_TEXT, config('app.locale'));
        return response()->json(['errors' => [], 'data' => ['popup_text' => $popupText, 'placeholder_text' => $placeholderText]]);
    }

    /**
     * @api {patch} /api/translations/auth_code_texts Set Auth Code Texts
     * @apiName SetAuthCodeTexts
     * @apiGroup AdminTranslations
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [popup_text]
     * @apiParam {string} [placeholder_text]
     */

    public function update_auth_code_texts(Request $request)
    {
        if ($request->popup_text)
            TranslationTexts::setByKey(TranslationTexts::AUTH_ENTER_CODE_POPUP_TEXT, config('app.locale'), $request->popup_text);
        if ($request->placeholder_text)
            TranslationTexts::setByKey(TranslationTexts::AUTH_ENTER_CODE_PLACEHOLDER_TEXT, config('app.locale'), $request->placeholder_text);

        $popupText = TranslationTexts::getByKey(TranslationTexts::AUTH_ENTER_CODE_POPUP_TEXT, config('app.locale'));
        $placeholderText = TranslationTexts::getByKey(TranslationTexts::AUTH_ENTER_CODE_PLACEHOLDER_TEXT, config('app.locale'));
        return response()->json(['errors' => [], 'data' => ['popup_text' => $popupText, 'placeholder_text' => $placeholderText]]);
    }
}
