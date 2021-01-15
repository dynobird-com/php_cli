<?php

function columnRename($oldColumnName, $newColumnName)
{
    return "\$table->renameColumn('$oldColumnName', '$newColumnName');\r\n";
}
