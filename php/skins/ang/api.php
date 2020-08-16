<?php

if(!defined("ROOT_FOLDER")) define("ROOT_FOLDER", "../../");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "plugins.php");
require_once(ROOT_FOLDER . "skins.php");
require_once(ROOT_FOLDER . "utils.php");


$dbmgr = array_key_exists('dbmgr',$_SESSION) ? $_SESSION['dbmgr'] : null;
if($dbmgr===null || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);
$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
if($formulator===null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

// -1. DB Connected?
$dbmgr->connect();
if(!$dbmgr->isConnected()) {
	$reloc = "Location: http".(array_key_exists('HTTPS',$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. (dirname($_SERVER['PHP_SELF'])=='/'?'':dirname($_SERVER['PHP_SELF']))
						. "/underconstruction.php";
	header($reloc);
	exit;
}


// 0.Parameters
$current_obj_id = array_key_exists('obj_id',$_REQUEST) ?
					$_REQUEST['obj_id'] : 
					( array_key_exists('field_id',$_REQUEST) ? $_REQUEST['field_id'] : $root_obj_id );
$current_obj_name = array_key_exists('name',$_REQUEST) ?
					$_REQUEST['name'] :
					( array_key_exists('title',$_REQUEST) ?
						str_replace('_',' ',$_REQUEST['title']) :
						'' );
$search_object = array_key_exists('search_object',$_REQUEST) ? $_REQUEST['search_object'] : ''; // 2012.07.23

// 0.1
$current_obj = $current_obj_name>'' ? $dbmgr->fullObjectByName($current_obj_name) : $dbmgr->fullObjectById($current_obj_id);



$myObj = (object)[]; //new stdClass();
$myObj->name = "Cippa";

$myJSON = json_encode($current_obj);

echo $myJSON;
?>
