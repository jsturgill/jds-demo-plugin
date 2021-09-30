<?php

const ALLOW_LIST = ['init-wp.sh'];
const BASE_SCRIPT_PATH = '/opt/scripts';

function serveResponseAndExit(int $httpResponseCode, array $responseObject): void
{
    header('Content-type: application/json; charset=utf-8');
    http_response_code($httpResponseCode);
    echo json_encode($responseObject);
    exit(); // same as die()
}

$scriptName = $_GET['script'];
$responseObject = [
    'status' => 'success',
    'script' => $scriptName
];

if (!in_array($scriptName, ALLOW_LIST)) {
    $responseObject['status'] = 'failure';
    $responseObject['messages'] = 'script not allowed';
    serveResponseAndExit(403, $responseObject);
}

$scriptPath = BASE_SCRIPT_PATH . "/$scriptName";
$output = null;
$returnCode = null;

exec($scriptPath, $output, $returnCode);

$responseObject['returnCode'] = $returnCode;
$responseObject['output'] = $output;

if ($returnCode !== 0) {
    $responseObject['status'] = 'failure';
    serveResponseAndExit(500, $responseObject);
}

serveResponseAndExit(200, $responseObject);
