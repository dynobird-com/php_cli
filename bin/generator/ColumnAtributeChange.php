<?php

function columnAtributeChange($thisColumn)
{
    $columnName = $thisColumn['name'];
    $columnType = $thisColumn['dataType'];
    $columnTemplate = "";
    $tmp = explode('(', $columnType);
    $length = null;
    $cleanColumnType = $tmp[0];
    if (array_key_exists(1, $tmp)) {
        echo "NULLLLLLLL " . $columnName;
        $length = str_replace([')', ' '], '', $tmp[1]);
        echo $length;
    }

    if ($length == null) {
        $columnTemplate .= "\$table->$cleanColumnType('$columnName')";
    } else {
        $columnTemplate .= "\$table->$cleanColumnType('$columnName', $length)";
    }

    if ($thisColumn['unique'] == true) {
        $columnTemplate .= '->unique()';
    }

    if ($thisColumn['notNull'] == false) {
        $columnTemplate .= '->nullable()';
    }

    if ($thisColumn['default'] != null) {
        $defaultValue = $thisColumn['default'];
        // isNull
        if ($defaultValue == null) {
        }
        // is number
        // is regular expression
        $columnTemplate .= "->default('$defaultValue')";
    }

    if ($thisColumn['comment'] != '' && $thisColumn['comment'] != null) {
        $commentValue = $thisColumn['comment'];
        $columnTemplate .= "->comment('$commentValue')";
    }
    $columnTemplate .= "->change();\r\n";

    return $columnTemplate;
}
