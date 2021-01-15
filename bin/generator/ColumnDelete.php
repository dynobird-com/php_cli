<?php

function columnDelete($columnName)
{
    return "\$table->dropColumn('$columnName');\r\n";
}
