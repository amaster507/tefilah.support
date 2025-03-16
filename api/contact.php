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
    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        http_response_code(415);
        echo json_encode(array('error' => 'Server only accepts application/json data'));
        exit();
    }

    // include secrets
    require_once 'secrets.php';

    // get JSON data from request body type { name: string, email: string,
    // message: string }
    $data = json_decode(file_get_contents('php://input'), true);

    print_r($data);

?>