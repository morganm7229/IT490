#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = " jksadhgjkhasdlghjlasdkhgkj,asdhjgklasdhjgklashdjklghasdjklgh";
$request['lobby_id'] = 6099;
$request['status'] = 0;
$request['win'] = 1;
$request['points'] = 0;
$request['username'] = "Steve";
$request['password'] = "newTest";
$request['email'] = "steve@steve.steve";
$request['message'] = $msg;
$request['user_id'] = 3;
$request['steam_id'] = 570;
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

