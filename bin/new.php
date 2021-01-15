<?php
include "./generator/TableCreate.php";
include "./generator/TableRename.php";
include "./generator/ColumnAdd.php";
include "./lib/General.php";
include "./generator/ColumnAtributeChange.php";
include "./generator/ColumnRename.php";
include "./generator/ForeignKeyAdd.php";
include "./generator/ForeignKeyDelete.php";
include "./generator/IndexAdd.php";
include "./generator/IndexRename.php";
include "./generator/IndexDelete.php";
include "./generator/TableDelete.php";
include "./generator/ColumnDelete.php";

$loader = new \Twig\Loader\FilesystemLoader('/mnt/e/dynobird/cli/bin/');
$twig = new \Twig\Environment($loader);
$template = $twig->load('template.txt');
$foreignKeyTemplate = $twig->load('foreign-template.txt');
$indexTemplate = $twig->load('index-template.txt');

print __DIR__ . "\n";
print "yuuu";
// $argv contains command line arguments
print "From packed application: " . $argv[1] . "\n";


$client = new \GuzzleHttp\Client();
$response = $client->get('https://app-testung.dynobird.com/api/v1/integration/access?keyword=%%&tokenId=c6013d42705038eace64673a9a19c641a5f3ced407dbc9623e33770128effec49d7f87f6775d21c8e48e6508368478fb13fb');

// print $response->getStatusCode();
// print $response->getBody();






$data = $response->getBody();



$json = json_decode($data, true);
print $json['message'];

$histories = $json['payload']['history'];


// print json_encode($tags);




foreach ($histories as $keyHistory => $history) {

    $date = date_create($history['createdAt']);
    $formatedData = date_format($date, "Y_m_d_His");

    $migrationName = $formatedData . '_' . uncamelCase($history['name']);

    echo "History name " . $history['name'] . " \r\n";
    $up = "";
    foreach ($history['design']['table'] as $keyTable => $thisTable) {
        $tableName = $thisTable['properties']['name'];



        echo "\r\nHistory key---- " . $keyHistory . "\r\n";

        if ($keyHistory === 0) {
            echo "Create all table \r\n";
            $up .= tableCreate($thisTable);
            continue;
        }
        // compare table name exist or not
        if (!array_key_exists($keyTable, $histories[$keyHistory - 1]['design']['table'])) {
            // echo "New Table $tableName\r\n";
            // action create new table
            $up .= tableCreate($thisTable);
            continue;
        }


        // echo $histories[$keyHistory - 1]['design']['table'][$keyTable]['properties']['name'] . "\r\n";
        $thisTable = $thisTable;
        $oldTable = $histories[$keyHistory - 1]['design']['table'][$keyTable];

        echo "old name " . $oldTable['properties']['name'] . "\r\n";
        echo "new name " . $thisTable['properties']['name'] . "\r\n";

        // check is table name change
        if ($thisTable['properties']['name'] != $oldTable['properties']['name']) {
            // echo "Name wa change \r\n";
            $up .= tableRename($oldTable['properties']['name'], $thisTable['properties']['name']);
        }


        $columnChangeScript = "";
        foreach ($thisTable['column'] as $keyColumn => $thisColumn) {
            $columnName = $thisColumn['name'];
            $columnType = $thisColumn['dataType'];
            // echo "colum name " . $columnType;
            // check column is exist
            if (!array_key_exists($keyColumn, $oldTable['column'])) {
                echo "  $columnName create *\r\n";
                $columnChangeScript .= addColumn($thisColumn);
                continue;
            }

            $oldColumn = $oldTable['column'][$keyColumn];

            if ($oldColumn['name'] != $thisColumn['name']) {
                // name changed
                $oldColumnName = $oldColumn['name'];
                $newColumnName = $thisColumn['name'];
                $columnChangeScript .= columnRename($oldColumnName, $newColumnName);
            }


            if (
                ($oldColumn['notNull'] != $thisColumn['notNull']) ||
                ($oldColumn['unique'] != $thisColumn['unique']) ||
                ($oldColumn['default'] != $thisColumn['default']) ||
                ($oldColumn['comment'] != $thisColumn['comment']) ||
                ($oldColumn['dataType'] != $thisColumn['dataType'])
            ) {
                $columnChangeScript .= columnAtributeChange($thisColumn);
            }
        }

        if ($columnChangeScript != '') {
            $up .= "Schema::table('$tableName', function (Blueprint \$table) {
                $columnChangeScript
            });\r\n";
        }
    }


    foreach ($history['design']['table'] as $keyTable => $thisTable) {
        $tableName = $thisTable['properties']['name'];
        $foreignKeyScript = "";
        $listTable = $history['design']['table'];

        // create all foreign key
        if ($keyHistory === 0) {
            echo "\r\nmasuk sanaaaa 9234\r\n";
            foreach ($thisTable['foreignKey'] as $keyColumn => $foreignKey) {
                $foreignKeyScript .= foreignKeyAdd($foreignKey, $thisTable, $listTable);
            }
            if ($foreignKeyScript != "") {
                $fkTableScript = $foreignKeyTemplate->render([
                    'tableName' => $tableName,
                    'foreignKey' => $foreignKeyScript
                ]);
                $up .= $fkTableScript . "\r\n         ";
            }
            continue;
        }


        // compare table name exist or not
        if (!array_key_exists($keyTable, $histories[$keyHistory - 1]['design']['table'])) {
            echo "Add 342d $" . $history['name'] . "  " . $thisTable['properties']['name'];
            $foreignKeyScript = "";
            foreach ($thisTable['foreignKey'] as $keyColumn => $foreignKey) {
                $foreignKeyScript .= foreignKeyAdd($foreignKey, $thisTable, $listTable);
            }
            if ($foreignKeyScript != "") {
                $fkTableScript = $foreignKeyTemplate->render([
                    'tableName' => $tableName,
                    'foreignKey' => $foreignKeyScript
                ]);
                $up .= $fkTableScript . "\r\n         ";
            }
            continue;
        }


        $oldTable = $histories[$keyHistory - 1]['design']['table'][$keyTable];
        // check changed foreign key

        $foreignKeyScript = "";
        foreach ($thisTable['foreignKey'] as $keyThisForeignKey => $thisForeignKey) {
            echo "\r\nmasuk siniii 9234\r\n";

            // add new foreign key
            if (!array_key_exists($keyThisForeignKey, $oldTable['foreignKey'])) {
                $foreignKeyScript .= foreignKeyAdd($thisForeignKey, $thisTable, $listTable);
                continue;
            }


            $oldForeignKey = $oldTable['foreignKey'][$keyThisForeignKey];
            if (
                ($oldForeignKey['name'] != $thisForeignKey['name']) ||
                ($oldForeignKey['columnIds'][0] != $thisForeignKey['columnIds'][0]) ||
                ($oldForeignKey['refTableId'] != $thisForeignKey['refTableId']) ||
                ($oldForeignKey['refColumnIds'][0] != $thisForeignKey['refColumnIds'][0]) ||
                ($oldForeignKey['onDelete'] != $thisForeignKey['onDelete']) ||
                ($oldForeignKey['onUpdate'] != $thisForeignKey['onUpdate'])
            ) {
                $foreignKeyScript .= foreignKeyDelete($oldForeignKey['name']);
                $foreignKeyScript .= foreignKeyAdd($thisForeignKey, $thisTable, $listTable);
            }
        }
        if ($foreignKeyScript != "") {
            echo "$foreignKeyScript yo scrip\r\n";
            $fkTableScript = $foreignKeyTemplate->render([
                'tableName' => $tableName,
                'foreignKey' => $foreignKeyScript
            ]);
            $up .= $fkTableScript . "\r\n         ";
        }
    }


    foreach ($history['design']['table'] as $keyTable => $thisTable) {
        $tableName = $thisTable['properties']['name'];
        $indexScript = "";
        $listTable = $history['design']['table'];

        echo "INIIIIIIIIIIII " . $keyHistory;
        // create all index
        if ($keyHistory == 0) {
            echo "INDEXXXXXXXXXXXXXXX";
            foreach ($thisTable['index'] as $keyIndex => $thisIndex) {
                $indexScript .= indexAdd($thisIndex, $thisTable['column']) . "\r\n";;
            }
            if ($indexScript != "") {
                $fullIndexScript = $indexTemplate->render([
                    'tableName' => $tableName,
                    'index' => $indexScript
                ]);
                $up .= $fullIndexScript . "\r\n         ";
            }
            continue;
        }

        // compare table name exist or not
        if (!array_key_exists($keyTable, $histories[$keyHistory - 1]['design']['table'])) {
            $indexScript = "";
            foreach ($thisTable['index'] as $keyIndex => $thisIndex) {
                $indexScript .= indexAdd($thisIndex, $thisTable['column']) . "\r\n";;
            }
            if ($indexScript != "") {
                $fullIndexScript = $indexTemplate->render([
                    'tableName' => $tableName,
                    'index' => $indexScript
                ]);
                $up .= $fullIndexScript . "\r\n         ";
            }
            continue;
        }


        $oldTable = $histories[$keyHistory - 1]['design']['table'][$keyTable];

        $indexScript = "";
        foreach ($thisTable['index'] as $thisKeyIndex => $thisIndex) {
            echo "\r\nmasuk siniii 9234\r\n";

            // add new index
            if (!array_key_exists($thisKeyIndex, $oldTable['index'])) {
                $indexScript .= indexAdd($thisIndex, $thisTable['column']) . "\r\n";
                continue;
            }


            $oldIndex = $oldTable['index'][$thisKeyIndex];


            $isIndexColumnChanged = false;
            // detect additional column
            foreach ($thisTable['index'][$thisKeyIndex]['column'] as $keyIndexColumn => $indexColumn) {
                if (!array_key_exists($keyIndexColumn, $oldTable['index'][$thisKeyIndex]['column'])) {
                    $isIndexColumnChanged = true;
                    break;
                }
            }

            // detect deleted column
            foreach ($oldTable['index'][$thisKeyIndex]['column'] as $keyIndexColumn => $indexColumn) {
                if (!array_key_exists($keyIndexColumn, $thisTable['index'][$thisKeyIndex]['column'])) {
                    $isIndexColumnChanged = true;
                    break;
                }
            }


            if (
                ($oldIndex['name'] != $thisIndex['name']) &&
                ($oldIndex['type'] == $thisIndex['type']) &&
                ($oldIndex['comment'] == $thisIndex['comment']) &&
                $isIndexColumnChanged === false
            ) {
                $indexScript .=  indexRename($oldIndex['name'], $thisIndex['name']) . "\r\n";;
            } else if (
                ($oldIndex['type'] != $thisIndex['type']) ||
                ($oldIndex['comment'] != $thisIndex['comment']) ||
                $isIndexColumnChanged === true
            ) {
                $indexScript .= indexDelete($oldIndex) . "\r\n";;
                $indexScript .= indexAdd($thisIndex, $thisTable['column']) . "\r\n";;
            }
        }

        if ($indexScript != "") {
            $fullIndexScript = $indexTemplate->render([
                'tableName' => $tableName,
                'index' => $indexScript
            ]);
            $up .= $fullIndexScript . "\r\n         ";
        }
    }


    if ($keyHistory !== 0) {
        $dropScript = "";
        $oldHistory = $histories[$keyHistory - 1];
        // Delete
        foreach ($oldHistory['design']['table'] as $keyOldTable => $oldTable) {
            /**
             * DELETE TABLE
             */
            if (!array_key_exists($keyOldTable, $histories[$keyHistory]['design']['table'])) {
                // check relation dependency
                $dropScript .= dropForeignKeyByTable($oldHistory['design']['table'], $oldTable);
                $dropScript .= tableDelete($oldTable['properties']['name']) . "\r\n";
                continue;
            }

            $thisTable = $histories[$keyHistory]['design']['table'][$keyOldTable];

            /**
             * DELETE INDEX
             */
            $dropIndexScript = "";
            foreach ($oldTable['index'] as $keyOldIndex => $oldIndex) {
                if (!array_key_exists($keyOldIndex, $thisTable['index'])) {
                    $dropIndexScript .= indexDelete($oldIndex) . "\r\n";;
                }
            }

            if ($dropIndexScript != "") {
                $fullDropIndexScript = $indexTemplate->render([
                    'tableName' => $oldTable['properties']['name'],
                    'index' => $dropIndexScript
                ]);
                $dropScript .= $fullDropIndexScript . "\r\n";
            }

            /**
             * DELETE COLUMN
             */
            $dropColumnScript = "";
            foreach ($oldTable['column'] as $keyOldColumn => $oldColumn) {
                if (!array_key_exists($keyOldColumn, $thisTable['column'])) {
                    $dropScript .= dropForeignKeyByColumn($oldHistory['design']['table'], $oldColumn);
                    $dropColumnScript .= columnDelete($oldColumn['name']);
                }
            }
            if ($dropColumnScript != "") {
                $fullDropColumnScript = $indexTemplate->render([
                    'tableName' => $oldTable['properties']['name'],
                    'index' => $dropColumnScript
                ]);
                $dropScript .= $fullDropColumnScript . "\r\n";
            }



            /**
             * DROP FOREIGN KEY
             */
            $dropForeignKeyScript = "";
            foreach ($oldTable['foreignKey'] as $keyOldForeignKey => $oldForeignKey) {
                // if table not deleted and column not deleted tapi foreign key deleted
                if (
                    array_key_exists($oldForeignKey['refTableId'], $histories[$keyHistory]['design']['table']) &&
                    array_key_exists($oldForeignKey['refColumnIds'][0], $histories[$keyHistory]['design']['table'][$oldForeignKey['refTableId']]) &&
                    array_key_exists($oldForeignKey['columnIds'][0], $thisTable['column'])
                ) {
                    continue;
                }

                if (!array_key_exists($keyOldForeignKey, $histories[$keyHistory]['design']['table'][$keyOldTable]['foreignKey'])) {
                    $dropForeignKeyScript .= foreignKeyDelete($oldForeignKey['name']);
                }
            }

            if ($dropForeignKeyScript != "") {
                $fullDropForeignKeyScript = $indexTemplate->render([
                    'tableName' => $oldTable['properties']['name'],
                    'index' => $dropForeignKeyScript
                ]);
                $dropScript .= $fullDropForeignKeyScript . "\r\n";
            }
        }

        $up .= $dropScript;
    }


    $myfile = fopen("/mnt/e/dynobird/experimental/example-app/database/migrations/" . $migrationName . ".php", "w");
    fwrite(
        $myfile,
        $template->render([
            'migrationName' => camelCase($history['name']),
            'up' => $up
        ])
    );
}


function indexColumnChanged($oldIndex, $thisIndex)
{
    return false;
}


function dropForeignKeyByColumn($listOldTable, $deletedColumn)
{
    $loader = new \Twig\Loader\FilesystemLoader('/mnt/e/dynobird/cli/bin/');
    $twig = new \Twig\Environment($loader);
    $foreignKeyTemplate = $twig->load('foreign-template.txt');

    $scriptDelete = "";
    foreach ($listOldTable as $keyOldTableKey => $oldTable) {
        $scriptDeletedFKTable = "";
        foreach ($oldTable['foreignKey'] as $keyOldForeignKey => $oldForeignKey) {
            if ($oldForeignKey['refColumnIds'][0] === $deletedColumn['id']) {
                $scriptDeletedFKTable .= foreignKeyDelete($oldForeignKey['name']);
                continue;
            }

            if ($oldForeignKey['columnIds'][0] === $deletedColumn['id']) {
                $scriptDeletedFKTable .= foreignKeyDelete($oldForeignKey['name']);
                continue;
            }
        }

        if ($scriptDeletedFKTable != "") {
            $scriptDelete .= $foreignKeyTemplate->render([
                'tableName' => $oldTable['properties']['name'],
                'foreignKey' => $scriptDeletedFKTable
            ]) . "\r\n";
        }
    }

    return $scriptDelete;
}


function dropForeignKeyByTable($listOldTable, $deletedTable)
{
    echo "(((((((((((((((((((((((((((((((((((((((( drop fk by table \r\n";
    $loader = new \Twig\Loader\FilesystemLoader('/mnt/e/dynobird/cli/bin/');
    $twig = new \Twig\Environment($loader);
    $foreignKeyTemplate = $twig->load('foreign-template.txt');

    $scriptDelete = "";
    foreach ($listOldTable as $keyOldTableKey => $oldTable) {
        $scriptDeletedFKTable = "";
        foreach ($oldTable['foreignKey'] as $keyOldForeignKey => $oldForeignKey) {
            if ($oldForeignKey['refTableId'] === $deletedTable['id']) {
                echo $oldForeignKey['name'] . "+++++++++++++++++++++++++++++++++++++++++++++++++++++++oldForeignKey[oldForeignKey[]]\r\n";
                $scriptDeletedFKTable .= foreignKeyDelete($oldForeignKey['name']);
            }
        }

        if ($scriptDeletedFKTable != "") {
            echo $oldForeignKey['name'] . "--------------------------------------------------------------------------old foreign key build BUILD\r\n";
            $scriptDelete .= $foreignKeyTemplate->render([
                'tableName' => $oldTable['properties']['name'],
                'foreignKey' => $scriptDeletedFKTable
            ]) . "\r\n";
        }
    }
    return $scriptDelete;
}
