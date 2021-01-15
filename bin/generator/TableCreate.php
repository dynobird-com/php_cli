<?php

function tableCreate($table)
{
    $loader = new \Twig\Loader\FilesystemLoader('/mnt/e/dynobird/cli/bin/');
    $twig = new \Twig\Environment($loader);
    $tableTemplate = $twig->load('table.txt');
    $foreignKeyTemplate = $twig->load('foreign-template.txt');

    $tableName = $table['properties']['name'];

    $columnTemplate = "";
    foreach ($table['column'] as $key => $column) {
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
        $columnTemplate .= ";\r\n             ";
    }

    $primaryColumn = "";
    // primary check
    $count = 0;
    foreach ($table['column'] as $key => $column) {
        if ($column['primary'] == true && $column['dataType'] != 'id') {
            $columnName = $column['name'];
            if ($count == 0) {
                $primaryColumn .= "'$columnName'";
            } else {
                $primaryColumn .= " ,'$columnName'";
            }
        }
        $count++;
    }

    if ($primaryColumn != "") {
        $columnTemplate .= "\$table->primary([$primaryColumn]);\r\n             ";
    }

    // $createTable =
    //     "Schema::create('$tableName', function (Blueprint \$table) {
    //             $columnTemplate
    //     });";

    $createTable = $tableTemplate->render([
        'tableName' => $tableName,
        'column' => $columnTemplate
    ]);
    echo "Create table script ---------\r\n";
    echo $createTable;
    echo "\r\n";


    return $createTable . "\r\n";
    // $myfile = fopen($migrationName . ".php", "w");
    // fwrite(
    //     $myfile,
    //     $template->render([
    //         'migrationName' => ucfirst($history['name']),
    //         'up' => $up
    //     ])
    // );
    // echo $tableName;
}