<?php

namespace App\Helpers;

class Helper
{

    /**
    * checks if an array is multidimentional
    *
    * @param array $array
    *
    * @return bool
     */
    public static function isMultiArray($array)
    {
        foreach ($array as $item) {
            if (is_array($item)) {
                return true;
            }
        }
        return false;
    }
    /**
    * checks if command in terminal is migration
    *
    * @return bool
     */
    public static function isMigrationCommand()
    {
        $command = \Request::server('argv', null);
        if (is_array($command)) {
            $command = implode(' ', $command);
        }
        return str_contains($command, 'migrate');
    }
    /**
    * checks if command in terminal is optimize
    *
    * @return bool
     */
   
}
