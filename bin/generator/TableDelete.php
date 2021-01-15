<?php

function tableDelete($tableName)
{
    return "Schema::dropIfExists('$tableName');";
}
