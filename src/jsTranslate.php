<?php

namespace src;

/**
 * jsTranslate
 */
class jsTranslate
{
    private static $LANG = "fa";

    /**
     * translation word
     * @param string $word
     * @return string
     * @throws \Exception
     */
    public static function translate($word)
    {
        $langFile = require  __DIR__ . '/languages/'. self::$LANG .'.php';
        if(!isset($langFile[$word])){
            throw new \Exception('translation word not found');
        }
        return $langFile[$word];
    }

    /**
     * set language code
     * @param string $language
     */
    public static function setLanguage($language = 'fa'){
        self::$LANG = $language;
    }

    /**
     * get language code
     * @return string
     */
    public static function getLanguage(){
        return self::$LANG;
    }

}