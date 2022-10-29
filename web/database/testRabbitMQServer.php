#!/usr/bin/php

<?php
$dbVars = parse_ini_file('database.ini');
$dbIP = $dbVars['dbIP'];
$dbUser = $dbVars['dbUser'];
$dbPassword = $dbVars['dbPassword'];
$dbName = $dbVars['dbName'];
echo $dbName;
$db = new mysqli($dbIP,$dbUser,$dbPassword,$dbName);
if ($db->errno != 0)
{
  echo "failed to connect to database: ". $db->error . PHP_EOL;
  exit(0);
}
echo "successfully connected to database".PHP_EOL;

function getFriends($accountID)
{
  global $db;
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

function getUserData($accountID)
{
  global $db;
  $query = "SELECT accid, name, lifetimePoints, gamesWon, publicProfile, publicFriends, publicAchievements, highestScore, gamesPlayed FROM accounts WHERE accID=" . $accountID . ";";

  $response = $db->query($query);
  if ($db->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$db->error.PHP_EOL;
    exit(0);
  }

  return mysqli_fetch_row($response);
}

function getUsername($accountID)
{
  global $db;
  $query = "SELECT name FROM accounts WHERE accID=" . $accountID . ";";

  $response = $db->query($query);
  if ($db->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$db->error.PHP_EOL;
    exit(0);
  }

  return mysqli_fetch_row($response);
}

function getID($username)
{
  global $db;
  $query = "SELECT accID FROM accounts WHERE accID=" . $username . ";";

  $response = $db->query($query);
  if ($db->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$db->error.PHP_EOL;
    exit(0);
  }

  return mysqli_fetch_row($response);
}

function newUser($username, $password, $email)
{
  global $db;
  $query = "INSERT INTO accounts (name, lifetimePoints, gamesWon, publicProfile, publicFriends, publicAchievements, email, password, highestScore, gamesPlayed) VALUES ('" . $username . "', 0, 0, 0, 0, 0, '" . $email . "', '" . $password . "', 0, 0);";

  $response = $db->query($query);
  if ($db->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$db->error.PHP_EOL;
    exit(0);
  }

  return "User created";
}

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($username,$password)
{
    global $db;
    
    echo "successfully connected to database".PHP_EOL;
    
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password';";
    
    $response = $db->query($query);
    if ($db->errno != 0)
    {
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$db->error.PHP_EOL;
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
      break;
    case "validate_session":
      return doValidate($request['sessionId']);
      break;
    case "get_friends":
      return getFriends($request['accountID']);
      break;
    case "get_user_data":
      return getUserData($request['accountID']);
      break;
    case "get_username_from_id":
      return getUsername($request['accountID']);
      break;
    case "get_id":
      return getID($request['username']);
      break;
    case "new_user":
      return newUser($request['username'], $request['password'], $request['email']);
      break;
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>
