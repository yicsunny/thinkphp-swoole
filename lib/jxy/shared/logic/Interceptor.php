<?php

namespace jxy\shared\logic;


use jxy\shared\exception\ErrorException;

class Interceptor
{
    public static function ensureNull($result, $errno, $args = [])
    {
        if (!is_null($result)) throw new ErrorException($errno, $args);

        return TRUE;
    }

    public static function ensureNotNull($result, $errno, $args = [])
    {
        if (is_null($result)) throw new ErrorException($errno, $args);

        return TRUE;
    }

    public static function ensureNotEmpty($result, $errno, $args = [])
    {
        if (empty($result)) throw new ErrorException($errno, $args);

        return TRUE;
    }

    public static function ensureEmpty($result, $errno, $args = [])
    {
        if (!empty($result)) throw new ErrorException($errno, $args);

        return TRUE;
    }

    public static function ensureNotFalse($result, $errno, $args = [])
    {
        if ($result === FALSE) throw new ErrorException($errno, $args);

        return TRUE;
    }

    public static function ensureFalse($result, $errno, $args = [])
    {
        if ($result !== FALSE) throw new ErrorException($errno, $args);

        return TRUE;
    }

    public static function ensurePhoneNumber($result, $errno, $args = [])
    {
        function check_phone($result, $pattern = "/1[345789]{1}\d{9}$/"){
            if(preg_match($pattern, $result) == false) return FALSE;

            return true;
        }

        if (check_phone($result)) throw new ErrorException($errno, $args);

        return TRUE;
    }
}