<?php
function foreignKeyDelete($name)
{
    return "\$table->dropForeign('$name');\r\n             ";
}
