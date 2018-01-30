<?php

use sampleAPI\services\Request;
use sampleAPI\services\Response;

require_once('../vendor/autoload.php');

$request = new Request();
$response = $request->handleRequest();

$response->send();