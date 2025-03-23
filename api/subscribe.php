<?php

    // allows direct access to this file
    define('AllowedDirectAccess', true);

    // requires POST method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(array('error' => 'Only POST requests are allowed'));
        exit();
    }

    // requires JSON
    if ($_SERVER['CONTENT_TYPE'] !== 'application/x-www-form-urlencoded') {
        http_response_code(415);
        echo json_encode(array('error' => 'Server only accepts application/x-www-form-urlencoded data'));
        exit();
    }

    // include secrets
    require_once 'secrets.php';

    require '../vendor/autoload.php';
    use Mailgun\Mailgun;

    $mg = Mailgun::create($mailgunApiKey);

    $member_address = $_POST['address'];

    // add member_address to $mailingList
    try {
        $result = $mg->mailingList()->member()->create(
            $mailingList,
            $member_address,
            "Subscriber",
        );
        http_response_code(200);
        echo "Member added to list!";
    } catch(Exception $e) {
        if ($e->getResponseCode() == 400) {
            // member is already on list!
            http_response_code(200);
            echo "Member is already on list!";
        } else {
            // Error has occurred
            http_response_code(200);
            // print the error message
            echo $e->getMessage();
        }
    }
?>