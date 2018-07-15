<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using

use \src\jsBankParsian as jsParsianBank;

$Token = $_REQUEST['Token'] ?? '';
$Status = $_REQUEST['status'] ?? -1;
$TerminalNo = $_REQUEST['TerminalNo'] ?? '';
$RRN = $_REQUEST['RRN'] ?? '';
$TspToken = $_REQUEST['TspToken'] ?? '';
$HashCardNumber = $_REQUEST['HashCardNumber'] ?? '';
$Amount = $_REQUEST['Amount'] ?? 0;

$getCallback = jsParsianBank::callback($RRN, $Token, $Status);
$Code    = $getCallback->responseCode ?? -1;
$Message = $getCallback->responseMessage ?? 'Error';

if($Code == 0){
    die(' Payment OK ');
}

die($Message);