<?php

namespace Gbxtp\Atmc;


use CJSCore;

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;

use Bitrix\Main\Application;
use	Bitrix\Main\HttpApplication;

use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\IO\FileOpenException;
use Bitrix\Main\IO\File;

use Gbxtp\Atmc\Db;


class Main
{
    const MODULE_ID = 'gbxtp.atmc';

    const PATH_TO_ADMIN_CSS_DEFAULT  = '/bitrix/css/'. self::MODULE_ID .'/default/admin.css';
    const PATH_TO_PUBLIC_CSS_DEFAULT = '/bitrix/css/'. self::MODULE_ID .'/default/public.css';

    const PATH_TO_ADMIN_CSS_CUSTOM   = '/bitrix/css/'. self::MODULE_ID .'/custom/admin.css';
    const PATH_TO_PUBLIC_CSS_CUSTOM  = '/bitrix/css/'. self::MODULE_ID .'/custom/public.css';

    const PATH_TO_ADMIN_JS_LOGIN_CUSTOM     = '/bitrix/js/'. self::MODULE_ID .'/login.js';
    const PATH_TO_ADMIN_JS_SETTINGS_CUSTOM     = '/bitrix/js/'. self::MODULE_ID .'/settings.js';

    private static $request = null;


    private static function getPathToFile($p)
    {
        $filePathDefault = $p['FILE_PATH_DEFAULT'];
        $filePathCustom  = $p['FILE_PATH_CUSTOM'];

        $file  = new File(Application::getDocumentRoot() . $filePathCustom);

            $pathFinal = $filePathDefault;

        try
        {
            $pathFinal = empty($file->getSize()) ? $filePathDefault : $filePathCustom;
        }
        catch (FileNotFoundException $exception)
        {
            $pathFinal = $filePathDefault;
        }
        catch (FileOpenException $exception)
        {
            $pathFinal = $filePathDefault;
        }

        try
        {
            $fP = $file->open('r');
        }
        catch (FileOpenException $exception)
        {
            $pathFinal = $filePathDefault;
        }

        return $pathFinal;
    }

    private static function initExt($l)
    {
        foreach ($l as $key => $list)
        {
            CJSCore::RegisterExt($key, $list);
        }

            CJSCore::Init(array_keys($l));
    }

    private static function updateAdminStyle()
    {
        $pathCssAdmin  = self::getPathToFile([
            'FILE_PATH_DEFAULT' => self::PATH_TO_ADMIN_CSS_DEFAULT,
            'FILE_PATH_CUSTOM'  => self::PATH_TO_ADMIN_CSS_CUSTOM,
        ]);

        $l = [
            'gbxtp_atmc_admin_css' => [
                'use' => CJSCore::USE_ADMIN,

                'css' => $pathCssAdmin,
            ],
            'gbxtp_atmc_admin_js_login' => [
                'use' => CJSCore::USE_ADMIN,

                'rel' => ['jquery'],
                'js' => self::PATH_TO_ADMIN_JS_LOGIN_CUSTOM,
            ],
            'gbxtp_atmc_admin_js_settings' => [
                'use' => CJSCore::USE_ADMIN,

                'rel' => ['color_picker'],
                'js' => self::PATH_TO_ADMIN_JS_SETTINGS_CUSTOM,
            ],
        ];

        $lvS = [

        ];


            $request = self::$request;

            $lR = $l;
            $optionValues = [];

        if ($request['mid'] === self::MODULE_ID)
        {
            $lR = array_merge(
                $l,
                $lvS
            );

            $optionValues = Db::getEditableOptionValues();
        }

        self::initExt($lR);

        Asset::getInstance()->addString(
            "<script>var gbxtp_atmc_language_id = '". LANGUAGE_ID ."';</script>",
            true,
            AssetLocation::AFTER_JS
        );

                                                         $optionValuesJson = json_encode($optionValues);

        Asset::getInstance()->addString(
            "<script>var gbxtp_atmc_options_values = $optionValuesJson;</script>",
            true,
            AssetLocation::AFTER_JS
        );
    }

    private static function updatePublicStyle()
    {
        if($_SESSION["SESS_AUTH"]["AUTHORIZED"] !== "Y")
        {
            return;
        }

        $pathCssPublic = self::getPathToFile([
            'FILE_PATH_DEFAULT' => self::PATH_TO_PUBLIC_CSS_DEFAULT,
            'FILE_PATH_CUSTOM'  => self::PATH_TO_PUBLIC_CSS_CUSTOM,
        ]);

        $l = [
            'gbxtp_atmc_public_css' => [
                'use' => CJSCore::USE_PUBLIC,

                'css' => $pathCssPublic,
            ],
        ];

        self::initExt($l);
    }

    function run()
    {

        if(defined("ADMIN_SECTION"))
        {
            self::$request = HttpApplication::getInstance()->getContext()->getRequest();

            self::updateAdminStyle();
        }
        else
        {
            self::updatePublicStyle();
        }
    }
}