<?php

function getCSVData() {
    $csvData = array_map('str_getcsv', file('data.csv'));
    $csvHeader = $csvData[0];
    $data = array();
    array_shift($csvData);
    foreach($csvData as $k => $v) {
        
        $data[] = array($csvHeader[0] => $v[0], 
            $csvHeader[1] => $v[1],
            $csvHeader[2] => $v[2], 
            $csvHeader[3] => $v[3]);
         
    }
    return $data;
}

function getTodayISO8601() {
    return date("Y-m-d");
}

/*
 * Retourne la liste des personnes qui ont un anniversaire aujourd'hui
 */
function checkAnniversaires($todayDate, $data) {
    //return $todayDate;
    $bdToday = array();
    foreach ($data as $key => $value) {
        print $value[2]."\n";
        if ($value[2] == $todayDate){
            $bdToday[] = $value;
        }
    }
    return $bdToday;
}
 if (php_sapi_name() == 'cli') {
     print_r(getCSVData());
     print getTodayISO8601();
     
 } else { ?>

<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PHANNIV</title>
    </head>
    <body>
        <pre>
            <?php
            print_r(getCSVData());
            print getTodayISO8601();
            print_r(checkAnniversaires(getTodayISO8601(), getCSVData()));
            
            ?>
        </pre>
    </body>
</html>
<?php } ?>


