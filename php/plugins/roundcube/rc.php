<?php
define("ROOT_FOLDER",     "../../");
require_once(ROOT_FOLDER . "config.php");
//require_once(ROOT_FOLDER . "utils.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "mng/checkUser.php");
require_once(ROOT_FOLDER . "plugins.php");
require_once(ROOT_FOLDER . "skins.php");
require_once(ROOT_FOLDER . "utils.php");

$dbmgr = $_SESSION['dbmgr'];
if ($dbmgr==null || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);
$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
if ($formulator==null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

// -1. DB Connected?
$dbmgr->connect();
if(!$dbmgr->isConnected()) {
	header("Location: http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. dirname($_SERVER['PHP_SELF'])
						. "/underconstruction.php");
	exit;
}


$rc_url=ROOT_FOLDER."plugins/roundcube/rc/";


require_once( getPluginSkinFile('roundcube',"rc.php") );
?>
