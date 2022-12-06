<?php
require_once('./lib/nusoap.php');

$wsdlurl = 'https://api-sandbox.direct.yandex.com/v5/campaigns?wsdl'; /// 'https://api-sandbox.direct.yandex.ru/live/v4/wsdl/';
$token = 'YOUR TOKEN';
$locale = 'en';

// Initialization of NuSOAP-client
$client = new nusoap_client($wsdlurl, 'wsdl');

# Parameters of NuSOAP-client
$client->authtype = 'bearer'; /// 'basic';
$client->decode_utf8 = 0;
$client->soap_defencoding = 'UTF-8';

// Adding headers for SOAP-request
$client->setCredentials('token', $token, 'bearer'); ///$client->setHeaders("<token>$token</token>\n     <locale>$locale</locale>");

// Call request to server (API Yandex Direct)
$result = $client->call(/*'GetClientInfo'*/'get', array('SelectionCriteria' => (object) array(), 'FieldNames' => array('Id', 'Name')));

// Output of request and response
echo "Request (to server):<pre>".htmlspecialchars($client->request, ENT_QUOTES)."</pre>";
echo "Response (from server):<pre>".htmlspecialchars($client->response, ENT_QUOTES)."</pre>";


// Output of debug info
echo '<hr><pre>'.htmlspecialchars($client->debug_str, ENT_QUOTES).'</pre>';

?>
