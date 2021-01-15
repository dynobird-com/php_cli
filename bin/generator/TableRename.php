<?php

function tableRename($oldTableName, $newTableName)
{
    return "Schema::rename('$oldTableName', '$newTableName');\r\n";
}