<?php
/*
 * Get data from the ./data.csv file
*/
function getCSVData()
{
    $csvData = array_map('str_getcsv', file('data.csv'));
    $csvHeader = array_shift($csvData);
    $data = array();
    foreach ($csvData as $k => $v) {
        $data[] = array(
            $csvHeader[0] => $v[0],
            $csvHeader[1] => $v[1],
            $csvHeader[2] => $v[2],
            $csvHeader[3] => $v[3]);
    }
    return $data;
}

/* Same as getCSVData() but works if number of header change */
function getDynCSVData()
{
    $csvData = array_map('str_getcsv', file('data.csv'));
    $csvHeader = array_shift($csvData);
    $retData = array();
    foreach ($csvData as $k => $v) {
        foreach ($csvHeader as $kh => $kv) {
            $retData[$k][$kv] = $v[$kh];
        }
    }
    return $retData;
}

/*
 * Retourne la liste des personnes qui ont leur anniversaire aujourd'hui
 */
function checkAnniversaires($data)
{
    $bdToday = array();
    $todayMonth = date('m');
    $todayDay = date('d');
    foreach ($data as $key => $value) {
        /* Testing if month + day match today */
        if ((substr($value['DateISO'], 5, 2) == $todayMonth)
            && (substr($value['DateISO'], -2) == $todayDay)) {
            $bdToday[] = $value;
        }
    }
    return $bdToday;
}

/* This is where the mails are sent.
 * @todo: use a mail library, e.g. http://swiftmailer.org/
*/
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

/*
* If someone has his birthday, email all the other persons
* @Todo, @bug: if two people get their birthdays the same day,
               both should received the mail for the other one...
*/
function getRecipients($all, $birthDayGuys)
{
    $recipients = $all;
    foreach ($birthDayGuys as $key => $value) {
        $toRemove = array_search($value['email'], array_column($all, 'email'));
        if (is_int($toRemove)) {
            unset($recipients[$toRemove]);
        }
    }
    return array_column($recipients, 'email');
}

/* In case we use phanniv.php in Command Line Interface */
if (php_sapi_name() == 'cli') {
    print_r(getDynCSVData());
    //announceAnniversary(getRecipients($everyOne, checkAnniversaires($everyOne)), checkAnniversaires($everyOne));
    //print_r(checkAnniversaires($everyOne));
} else {
    /* For any other use case but CLI */
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
            print_r(getDynCSVData());
            ?>
        </pre>
    </body>
</html>

<?php
}
?>
