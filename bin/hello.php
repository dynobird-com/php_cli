<?php
$loader = new \Twig\Loader\FilesystemLoader('/mnt/e/dynobird/cli/bin/');
$twig = new \Twig\Environment($loader);
print __DIR__ . "\n";
print "yuuu";
// $argv contains command line arguments
print "From packed application: " . $argv[1] . "\n";


$client = new \GuzzleHttp\Client();
$response = $client->get('https://app-testung.dynobird.com/api/v1/integration/access?keyword=%%&tokenId=b020f5398a28baface776112c21edf75f6ce9984b11352b050f4dab636a0d77176f25ee3bfd6574093973adee15502d271a4');

// print $response->getStatusCode();
// print $response->getBody();






$data = $response->getBody();



$json = json_decode($data, true);
// print $json['message'];

$histories = $json['payload']['history'];


// print json_encode($tags);


$template = $twig->load('template.txt');
$tableTemplate = $twig->load('table.txt');
$foreignKeyTemplate = $twig->load('foreign-template.txt');


foreach ($histories as $key => $history) {

    $date = date_create($history['createdAt']);
    $formatedData = date_format($date, "Y_m_d_His");
    echo $formatedData;

    $migrationName = $formatedData . '_' . $history['name'];

    $up = "";
    foreach ($history['design']['table'] as $key => $table) {
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

        echo $createTable;
        echo "\r\n";


        $up .= $createTable . "\r\n";
        // $myfile = fopen($migrationName . ".php", "w");
        // fwrite(
        //     $myfile,
        //     $template->render([
        //         'migrationName' => ucfirst($history['name']),
        //         'up' => $up
        //     ])
        // );
        echo $tableName;
    }


    // Schema::table('posts', function (Blueprint $table) {    
    //     $table->foreign('user_id')->references('id')->on('users');
    // });


    // foreign key
    foreach ($history['design']['table'] as $keyTable => $table) {
        $tableName = $table['properties']['name'];
        $foreignKeyScript = "";
        foreach ($table['foreignKey'] as $keyColumn => $foreignKey) {
            $columnId = $foreignKey['columnIds'][0];
            echo "\r\n-------------- $columnId";
            $columnName = $table['column'][$columnId]['name'];
            $refTableName = $history['design']['table'][$foreignKey['refTableId']]['properties']['name'];
            $refColumnName = $history['design']['table'][$foreignKey['refTableId']]['column'][$foreignKey['refColumnIds'][0]]['name'];
            $foreignKeyScript .= "\$table->foreign('$columnName')->references('$refColumnName')->on('$refTableName');\r\n             ";
        }
        if ($foreignKeyScript != "") {
            $fkTableScript = $foreignKeyTemplate->render([
                'tableName' => $tableName,
                'foreignKey' => $foreignKeyScript
            ]);

            $up .= $fkTableScript . "\r\n         ";
        }
    }

    $foreignKeyTemplate;
    echo "\r\nforeignKeyScriptforeignKeyScriptforeignKeyScriptforeignKeyScript\r\n";
    echo $foreignKeyScript;
    // echo $template->render(['tableName' => $formatedData . '_' . $history['name']]);
    // echo $createTable;
    echo "\r\n";
    // echo '<br>';
    // foreach ($val as $value) {
    //     echo 'VALUE IS: ' . $value;
    //     echo '<br>';
    // }
    // echo '<br>';

    $myfile = fopen($migrationName . ".php", "w");
    fwrite(
        $myfile,
        $template->render([
            'migrationName' => ucfirst($history['name']),
            'up' => $up
        ])
    );
}


// function createTable($tableName, $columns)
// {
// //    $createTable= "Schema::create('password_resets', function (Blueprint $table) {
// //         $table->string('email')->index();
// //         $table->string('token');
// //         $table->timestamp('created_at')->nullable();
// //     });";
// }


// DYNO CONFIG JSON
// echo "_________________";
// $cwd = getcwd();
// $string = file_get_contents($cwd . "/dyno.json");
// $json_a = json_decode($string, true);
// echo $json_a['framework'];
