<?php

function indexDelete($index)
{
    $nameIndex = $index['name'];
    if ($index['type'] == 'INDEX') {
        return "\$table->dropIndex('$nameIndex');";
    } else if ($index['type'] == 'UNIQUE') {
        return "\$table->dropUnique('$nameIndex');";
    }
}
