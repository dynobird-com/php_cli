<?php

function indexRename($oldIndexName, $newIndexName)
{
    return "\$table->renameIndex('$oldIndexName', '$newIndexName');";
}
