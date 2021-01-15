<?php

function indexAdd($index, $column)
{
    echo "INDEX TYPEEE " . $index['type'];
    if ($index['type'] == 'INDEX') {
        $columnIndex = "";
        $nameIndex = $index['name'];
        foreach ($index['column'] as $keyThisColumn => $thisColumn) {
            $columnIndex .= "'" . $column[$thisColumn['id']]['name'] . "', ";
        }
        $columnIndex = substr($columnIndex, 0, strlen($columnIndex) - 2);
        return "\$table->index([$columnIndex],'$nameIndex');";
    } else if ($index['type'] == 'UNIQUE') {
        $columnIndex = "";
        $nameIndex = $index['name'];
        foreach ($index['column'] as $keyThisColumn => $thisColumn) {
            $columnIndex .= "'" . $column[$thisColumn['id']]['name'] . "', ";
        }
        $columnIndex = substr($columnIndex, 0, strlen($columnIndex) - 2);
        return "\$table->unique([$columnIndex],'$nameIndex');";
    }
}
