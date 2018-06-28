<?php
include("../bower_components/nusoap/src/nusoap.php");
$client = new nusoap_client('http://www.delbravoweb.com/monitorpruebas/ws/server.php?wsdl','wsdl');
$err = $client->getError();
if ($err) {
 echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
$client->debug();
$param = array(
	'usuario' => 'admin',
	'password' => 'r0117c',
	'cuentas' => json_encode(array(' 96733',' 96301')),
	'correos' => 'abisaicruz@delbravo.com',
	'correos_cliente' => 'false'
	);
$result = $client->call('enviarcuentas', $param);
echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2>';
echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo $result['Codigo']." ".$result['Mensaje'];
if ($client->fault) {
 echo '<h2>Fault</h2><pre>';
 print_r($result);
 echo '</pre>';
} else {
 // Check for errors
 $err = $client->getError();
 if ($err) {
  // Display the error
  echo '<h2>Error</h2><pre>' . $err . '</pre>';
 } else {
  // Display the result
  echo '<h2>Result</h2><pre>';
  print($result);
  echo '</pre>';
 }
}

echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
?>