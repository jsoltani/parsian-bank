# parsian-bank
connect to bank parsian

# Requirements
js-rsaCrypt Requires PHP >= 7.0

# Installation
## Using Composer
You can install this package using composer. Add this package to your composer.json:

```
"require": {
	"jsoltani/parsian-bank": "dev-master"
}
```

or if you prefer command line, change directory to project root and:

```
php composer.phar require "jsoltani/parsian-bank":"dev-master"
```

# Example Usage
## Example For Pay 
```
\src\jsBankParsian::setParsianPin('scsdsdfbdsthsgfnfgndg'); //set parsian pin
\src\jsBankParsian::setCallBackUrl('http://example.ir/callback'); // set callback url

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

echo "<h2>Error : </h2><span>$Message</span>";
```

## Example For Callback 
```
$Token = $_REQUEST['Token'] ?? '';
$Status = $_REQUEST['status'] ?? -1;
$TerminalNo = $_REQUEST['TerminalNo'] ?? '';
$RRN = $_REQUEST['RRN'] ?? '';
$TspToken = $_REQUEST['TspToken'] ?? '';
$HashCardNumber = $_REQUEST['HashCardNumber'] ?? '';
$Amount = $_REQUEST['Amount'] ?? 0;

$getCallback = \src\jsBankParsian::callback($RRN, $Token, $Status);
$Code    = $getCallback->responseCode ?? -1;
$Message = $getCallback->responseMessage ?? 'Error';

if($Code == 0){
    die(' Payment OK ');
}

echo $Message;
```