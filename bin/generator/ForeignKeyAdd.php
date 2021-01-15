<?php
function foreignKeyAdd($foreignKey, $thisTable, $listTable)
{
    $foreignKeyName = $foreignKey['name'];
    $columnId = $foreignKey['columnIds'][0];
    $columnName = $thisTable['column'][$columnId]['name'];
    $refTableName = $listTable[$foreignKey['refTableId']]['properties']['name'];
    $refColumnName = $listTable[$foreignKey['refTableId']]['column'][$foreignKey['refColumnIds'][0]]['name'];
    return "\$table->foreign('$columnName', '$foreignKeyName')->references('$refColumnName')->on('$refTableName');\r\n             ";
}
