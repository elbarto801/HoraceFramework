<?php

namespace HoraceFramework;

class Exceptions
{
    const   PACKAGE =   "Horace Errors";
    const   AUTHOR  =   "Emmanuel NOEL";
    const   EDITOR  =   "Horace Productions";
    const   VERSION =   "1.0";

    private static $_ignore_errors              =   array();
    private static $_error_reporting_activated  =   true;
    private static $_root                       =   null;

    public static function errorReporting()
    {
        $errors = func_get_args();

        foreach ($errors as $value)
        {
            if (is_int($value))
            {
                switch ($value)
                {
                    case 0:
                        self::$_error_reporting_activated = false;
                        return;
                    break;

                    default:
                        self::$_error_reporting_activated = true;
                        return;
                    break;
                }
            } elseif (preg_match('#^~(E_ERROR|E_WARNING|E_PARSE|E_NOTICE|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING|E_USER_ERROR|E_USER_WARNING|E_USER_NOTICE|E_STRICT|E_RECOVERABLE_ERROR|E_USER_DEPRECATED|E_ALL)#', $value))
            {
                self::$_ignore_errors[] = substr($value, 1, strlen($value));
            }
        }
    }

    public static function setException()
    {
        if (class_exists('\\HoraceFramework\Core'))
        {
            // On recherche la racine du framework.
            self::$_root = \HoraceFramework\Core::getRoot();
        } else
        {
            self::$_root = $_SERVER['DOCUMENT_ROOT'];
        }
    }
}