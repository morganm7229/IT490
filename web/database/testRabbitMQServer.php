#!/usr/bin/php

<?php
function getFriends($accountID)
{
 $db = new mysqli('localhost','database','doogis123','steamTag');
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

 return mysqli_fetch_row($response)[0];
}

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($username,$password)
{
    $mydb = new mysqli('192.168.191.236','rabbit','eDzHu9pK','users');
    // db pass eDzHu9pK
    if ($mydb->errno != 0)
    {
            echo "failed to connect to database: ". $mydb->error . PHP_EOL;
            exit(0);
    }
    
    echo "successfully connected to database".PHP_EOL;
    
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password';";
    
    $response = $mydb->query($query);
    if ($mydb->errno != 0)
    {
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            exit(0);
    }
    
    $numrows = mysqli_num_rows($response);
    
    if($numrows != 0)
    {
    echo "Auth";
        return "Auth";
    }else{
      return 0;
    }
    // lookup username in databas
    // check password
    echo "doLogin()";
    return true;
    //return false if not valid
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
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>
