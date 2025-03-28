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

    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    // print_r(array($name, $email, $message));

    $result = $mg->messages()->send(
        'mg.pray.support',
        [
            'from' => 'Tefilah Support <no-reply@mg.pray.support>',
            'to' => 'Anthony Master <anthony.master@ifbmt.info>',
            'subject' => 'New Contact from pray.support',
            'reply-to' => $email,
            'text' => "Name: ".$name."\nEmail: ".$email."\nMessage: ".$message
        ]
    );

    print_r($result->getMessage());

?>