<?php
/*
 * Get data from the ./data.csv file
 */
$errors = array();

function getCSVData() {
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

function getDynCSVData() {
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

function checkAnniversaires($data) {
    $bdToday = array();
    $todayMonth = date('m');
    $todayDay = date('d');
    foreach ($data as $key => $value) {
        /* Testing if month + day match today */
        if ((substr($value['DateISO'], 5, 2) == $todayMonth) && (substr($value['DateISO'], -2) == $todayDay)) {
            $bdToday[] = $value;
        }
    }
    return $bdToday;
}

/* This is where the mails are sent.
 * @todo: use a mail library, e.g. http://swiftmailer.org/
 */

function announceAnniversary($recipients, $happyGuys) {
    $to = 'nicolas.borboen@epfl.ch';
    $subject = '[PHANNIV] Today\'s anniversary';
    $message = "hello, did you know, today is \n";
    foreach ($happyGuys as $index => $value) {
        $message .= '-> ' . $value['Prénom'] . ' ' . $value['Nom'] . "\n";
    }
    $message .= "'s birthday !";
    $headers = 'From: nicolas.borboen@epfl.ch' . "\r\n" .
            'Reply-To: no-reply@epfl.ch' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);
}

/*
 * If someone has his birthday, email all the other persons
 * @Todo, @bug: if two people get their birthdays the same day,
  both should received the mail for the other one...
 */

function getRecipients($all, $birthDayGuys) {
    $recipients = $all;
    foreach ($birthDayGuys as $key => $value) {
        $toRemove = array_search($value['email'], array_column($all, 'email'));
        if (is_int($toRemove)) {
            unset($recipients[$toRemove]);
        }
    }
    return array_column($recipients, 'email');
}

function addDataInCSV($val) {
    // get previous content from CSV
    $handle = fopen("data.csv", "a+");
    fputcsv($handle, $val); // here you can change delimiter/enclosure
}

function debug($val) {
    print "<pre>";
    print_r($_POST);
    print "</pre>";
}

function checkdata($data, &$errors) {
    $date = $data['dateISO'];
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
        return true;
    } else {
        $errors['date'] = "Baaaad date.";
    }
}

function checkText($fieldname, $data, &$errors){
  $firstname = filter_var_array($data, $fieldname, FILTER_SANITIZE_SPECIAL_CHARS);
  if(!empty($firstname)){
    if($fieldname == "text") {
      echo "ok!";
    }
    return true;
  }else{
    //echo "test else";
    $errors[$fieldname] = "You can't leave it empty";
  }
}

/* In case we use phanniv.php in Command Line Interface */
if (php_sapi_name() == 'cli') {
    print_r(getDynCSVData());
    //announceAnniversary(getRecipients($everyOne, checkAnniversaires($everyOne)), checkAnniversaires($everyOne));
    //print_r(checkAnniversaires($everyOne));
} else {
    /* For any other use case but CLI */
    include "browser.php";
    $browser = new Browser();
    echo $browser->getBrowser();


    if ($_POST) {
        debug($_POST);
        checkdata($_POST, $errors);
        checkText("firstname", $_POST, $errors);
        checkText("lastname", $_POST, $errors);
        //checkFirstName($_POST, $errors);
        addDataInCSV($_POST);
    }
    ?>

    <!doctype html>
    <html>
        <head>
            <meta charset="UTF-8">
            <title>PHANNIV</title>
            <!-- Latest compiled and minified CSS -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

            <!-- Optional theme -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

            <!-- Latest compiled and minified JavaScript -->
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        </head>
        <body>
            <form method="post" action="" class= "form-group">
                <label> Prénom : <input class="form-control" type="text" name="firstname" /></label><br/>
                <?php if (isset($errors['firstname'])) { ?>
                                <div class="alert alert-danger" role="alert"><?= $errors['firstname'] ?></div>
                            <?php } ?>
                <label> Nom : <input class="form-control" type="text" name="lastname" /></label><br/>
                <?php if (isset($errors['lastname'])) { ?>
                                <div class="alert alert-danger" role="alert"><?= $errors['lastname'] ?></div>
                            <?php } ?>
                <label>Date de naissance : <input class="form-control" type="text" name="dateISO" /></label><br/>
    <?php if (isset($errors['date'])) { ?>
                    <div class="alert alert-danger" role="alert"><?= $errors['date'] ?></div>
                <?php } ?>
                <label> Mail : <input class="form-control" type="mail" name="email"/></label><br/>
                <input class="btn btn-default" type="submit"/>
            </form>
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
