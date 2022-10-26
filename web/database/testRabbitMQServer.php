#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
$database_details = parse_ini_file("database.ini");
$db = new mysqli($database_details['dbIP'],$database_details['dbUser'],$database_details['dbPassword'],$database_details['dbName']);

function doLogin($username,$password)
{
    // lookup username in databas
    // check password
    return true;
    //return false if not valid
}

function getFriends($accountID)
{
  if ($db->errno != 0)
  {
    echo "failed to connect to database: ". $db->error . PHP_EOL;
    exit(0);
  }
  echo "successfully connected to database".PHP_EOL;

  $query = "SELECT friends FROM accounts WHERE accID=" . $accountID . ";";

  $response = $db->query($query);
  if ($db->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$db->error.PHP_EOL;
    exit(0);
  }
  echo $response;
  return $response;
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
    case "get_friends":
      return getFriends($request['accountID']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
$array = [
  "type" => "get_friends",
  "userID" => "1",
];
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

