<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using

// \src\jsBankParsian::setParsianPin('scsdsdfbdsthsgfnfgndg');
// \src\jsBankParsian::setCallBackUrl('http://example.ir/callback');

$OrderId = time() . rand(000,999); // factor number
$Amount  = 1000; // amount to pay
$getPay  = \src\jsBankParsian::pay($OrderId, $Amount);
$getPay  = json_decode($getPay);
$Code    = $getPay->responseCode ?? -1;
$Message = $getPay->responseMessage ?? 'Error';

if($Code == 0){
    $Token = $getPay->responseItems->Token ?? '';
    if(!empty($Token)){
        header('LOCATION: https://pec.shaparak.ir/NewIPG/?Token=' . $Token);
        exit;
    }
}

die("<h2>Error : </h2><span>$Message</span>");