
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

session_start();
//login();

function logout(){
    if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
  }
  
  session_destroy();

  return "Session destroyed";
}

if (!isset($_POST))
{
	$msg = "NO POST MESSAGE SET, POLITELY FUCK OFF";
	echo json_encode($msg);
	exit(0);
}
$request = $_POST;
//echo $request["type"];
$response = "unsupported request type, politely FUCK OFF";
switch ($request["type"])
{
	case "logout":
     		$response = logout();

	break;
}
echo json_encode($response);
exit(0);

?>
