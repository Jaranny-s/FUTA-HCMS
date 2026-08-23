<?php
ob_start(); // output buffering is turned on 


session_start(); // Turns on sessions

// Assign file paths to PHP constants
// __FILE__ returns the current path to this file
// dirname() returns the path to the parent directory
define("PRIVATE_PATH", dirname(__FILE__));
define("PROJECT_PATH", dirname(PRIVATE_PATH));
define("PUBLIC_PATH", PROJECT_PATH . '/public');
define("SHARED_PATH", PRIVATE_PATH . '/shared');



// Assign the root URL to a PHP constant
// * Do not need to include the domain
// * Use same document root as webserver
// * Can dynamically find everything in URL up to "/public"
$public_end = strpos($_SERVER['SCRIPT_NAME'], '/public') + 7;
$doc_root = substr($_SERVER['SCRIPT_NAME'], 0, $public_end);
define("WWW_ROOT", $doc_root);




    require_once('helper_functions.php');
    require_once('establishment_functions.php');
    require_once('db.php');
    require_once('query_functions.php');
    require_once('log_action.php');
    require_once('auth.php');
    

    $db_1 = db_connect(); // opens the database connection
    $errors = [];

//this makes the system log out automatically 
// if the user hasn't been active on the page 
// for 20 minutes

define('INACTIVITY_LIMIT', 1200); // seconds

if (isset($_SESSION['LAST_ACTIVITY']) && 
    (time() - $_SESSION['LAST_ACTIVITY'] > INACTIVITY_LIMIT)) {

    session_unset();
    session_destroy();
    redirect_to(url_wrap('/staff/login.php'));
}

$_SESSION['LAST_ACTIVITY'] = time();


function get_settings() {
    global $db_1;

    $result = $db_1->query("SELECT * FROM settings LIMIT 1");
    return $result->fetch_assoc();
}

$settings = get_settings();
?>