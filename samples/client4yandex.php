<?php
require_once('./lib/nusoap.php');

$wsdlurl = 'https://api-sandbox.direct.yandex.com/v5/campaigns?wsdl'; /// 'https://api-sandbox.direct.yandex.ru/live/v4/wsdl/';
$token = 'YOUR TOKEN';
$locale = 'ru';

// Инициализация NuSOAP-клиента
$client = new nusoap_client($wsdlurl, 'wsdl');

# Параметры NuSOAP-клиента
$client->authtype = 'bearer'; /// 'basic';
$client->decode_utf8 = 0;
$client->soap_defencoding = 'UTF-8';

// Формирование заголовков SOAP-запроса
$client->setCredentials('token', $token, 'bearer'); ///$client->setHeaders("<token>$token</token>\n     <locale>$locale</locale>");

// Выполнение запроса к серверу API Директа
$result = $client->call(/*'GetClientInfo'*/'get', array('SelectionCriteria' => (object) array(), 'FieldNames' => array('Id', 'Name')));

// Вывод запроса и ответа
echo "Запрос:<pre>".htmlspecialchars($client->request, ENT_QUOTES)."</pre>";
echo "Ответ:<pre>".htmlspecialchars($client->response, ENT_QUOTES)."</pre>";


// Вывод отладочной информации 
echo '<hr><pre>'.htmlspecialchars($client->debug_str, ENT_QUOTES).'</pre>';

?>
