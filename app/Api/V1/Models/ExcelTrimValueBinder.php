<?php
namespace App\Api\V1\Models;

use PHPExcel_Cell;
use PHPExcel_Cell_DataType;
use PHPExcel_Cell_IValueBinder;
use PHPExcel_Cell_DefaultValueBinder;

class ExcelTrimValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
    public function bindValue(PHPExcel_Cell $cell, $value = null)
    {
        if (is_string($value))
        {
            $value = trim(preg_replace('/\s+/', ' ', $value));
            $cell->setValueExplicit($value);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }
}