<?php

declare(strict_types=1);

/*
This is the sample success page.

You must decode the response and get status - 200, in the following pattern (as data is encrypted)

and then you can do anything you desire, example - display the user success message or insert into your merchant domain's database

*/

if ($_GET) {
    $encoded = json_encode($_GET);
    // var_dump($encoded);
    // echo '<br/><br/>';

    $decoded = json_decode(base64_decode($encoded), true);
    // var_dump($decoded);
    // echo '<br/><br/>';

    if (200 == $decoded['status']) {
        // do anything here, i.e. display to merchant domain pages or insert data into merchant domain's  database
        echo 'Status => '.$decoded['status'].'<br/>';
        echo 'Transaction ID => '.$decoded['transaction_id'].'<br/>';
        echo 'Merchant => '.$decoded['merchant'].'<br/>';
        // echo "Currency => " . $decoded["currency"] . "<br/>";
        // echo "Amount => " . $decoded["amount"] . "<br/>";
        // echo "Fee => " . $decoded["fee"] . "<br/>";
        // echo "Total => " . $decoded["total"] . "<br/>";
    }
}
