<?php

function getCSVData()
{
    $csvData = array_map('str_getcsv', file('data.csv'));
    $csvHeader = $csvData[0];
    $data = array();
    array_shift($csvData);
    foreach ($csvData as $k => $v) {
        $data[] = array(
            $csvHeader[0] => $v[0],
            $csvHeader[1] => $v[1],
            $csvHeader[2] => $v[2],
            $csvHeader[3] => $v[3], );
    }

    return $data;
}

function getTodayISO8601()
{
    return date('Y-m-d');
}

/*
 * Retourne la liste des personnes qui ont un anniversaire aujourd'hui
 */
function checkAnniversaires($data)
{
    $bdToday = array();
    $todayMonth = date('m');
    $todayDay = date('d');
    foreach ($data as $key => $value) {
        if ((substr($value['DateISO'], 5, 2) == $todayMonth)
            && (substr($value['DateISO'], -2) == $todayDay)) {
            $bdToday[] = $value;
        }
    }
    return $bdToday;
}

function announceAnniversary($recipients, $happyGuys)
{
    $to = 'nicolas.borboen@epfl.ch';
    $subject = '[PHANNIV] Today\'s anniversary';
    $message = "hello, did you know, today is \n";
    foreach ($happyGuys as $index => $value) {
        $message .= '-> '.$value['Prénom'].' '.$value['Nom']."\n";
    }
    $message .= "'s birthday !";
    $headers = 'From: nicolas.borboen@epfl.ch'."\r\n".
    'Reply-To: no-reply@epfl.ch'."\r\n".
    'X-Mailer: PHP/'.phpversion();

    mail($to, $subject, $message, $headers);
}

function getRecipients($all, $birthDayGuys)
{
    $recipients = $all;
    foreach ($birthDayGuys as $key => $value) {
        //$test = array_search($value["email"],$birthDayGuys);
        $toRemove = array_search($value['email'], array_column($all, 'email'));
        if (is_int($toRemove)) {
            unset($recipients[$toRemove]);
        }
    }

    return array_column($recipients, 'email');
}

if (php_sapi_name() == 'cli') {
    $everyOne = getCSVData();
    announceAnniversary(getRecipients($everyOne, checkAnniversaires($everyOne)), checkAnniversaires($everyOne));
    print_r(checkAnniversaires($everyOne));
} else {
    ?>

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
    print_r(checkAnniversaires(getCSVData())); ?>
        </pre>
    </body>
</html>
<?php
} ?>
