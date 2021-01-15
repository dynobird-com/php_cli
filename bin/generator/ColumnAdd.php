<?php

function addColumn($column)
{

    $columnTemplate = "";
    $columnName = $column['name'];
    $columnType = $column['dataType'];
    echo "type is " . $columnType;
    $tmp = explode('(', $columnType);
    $length = null;
    $cleanColumnType = $tmp[0];
    if (array_key_exists(1, $tmp)) {
        echo "NULLLLLLLL " . $columnName;
        $length = str_replace([')', ' '], '', $tmp[1]);
        echo $length;
    }
    // echo 'tmp one => '.$tmp[1];
    if ($length == null) {
        $columnTemplate .= "\$table->$cleanColumnType('$columnName')";
    } else {
        $columnTemplate .= "\$table->$cleanColumnType('$columnName', $length)";
    }

    if ($column['unique'] == true) {
        $columnTemplate .= '->unique()';
    }

    if ($column['notNull'] == false) {
        $columnTemplate .= '->nullable()';
    }

    if ($column['default'] != null) {
        $defaultValue = $column['default'];
        // isNull
        if ($defaultValue == null) {
        }
        // is number
        // is regular expression
        $columnTemplate .= "->default('$defaultValue')";
    }

    if ($column['comment'] != '' && $column['comment'] != null) {
        $commentValue = $column['comment'];
        $columnTemplate .= "->comment('$commentValue')";
    }

    // closer
    $columnTemplate .= ";\r\n";
    return $columnTemplate;
}
