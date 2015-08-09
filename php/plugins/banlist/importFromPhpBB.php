<?php
define("ROOT_FOLDER",     "../../");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "utils.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "plugins.php");
require_once(ROOT_FOLDER . "utils.php");

$dbmgr = $_SESSION['dbmgr'];
if ($dbmgr==NULL || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);


$db_server2 = "zzz";
$db_user2 = "yyy";
$db_pwd2 = "xxx";
$db_db2 = "www";
$db_schema2="";
$dbmgr2 = new DBMgr($db_server2, $db_user2, $db_pwd2, $db_db2, $db_schema2,new DBEFactory);
$dbmgr2->setVerbose(false);
$dbmgr2->connect();
$bannati = $dbmgr2->select("","","select ban_ip, ban_reason from phpbb_sm_banlist where ban_ip>'' order by ban_ip");


$dbe = new DBEBanned();
$tablename = $dbmgr->buildTableName($dbe);

$queries = array(
//			"update $tablename set note2='' where `count`>=100",
			"optimize table $tablename",
			);
?><html>
<head>
<style>
body {
	color: #00ff00;
	background-color: black;
	font-size: 12px;
}
</style>
</head>
<body>
<pre><?php



echo "Import from phpBB...\n";
echo "====================\n";
echo "\n";
echo "\n";
echo "Table name: $tablename\n";
echo "\n";

foreach($bannati as $bannato) {
// 	echo $bannato->to_string()."\n";
	$cerca = new DBEBanned();
	$cerca->setValue('ban_ip',$bannato->getValue('ban_ip'));
	$lista = $dbmgr->search($cerca,$uselike=false);
	if(count($lista)>0) continue;
	$nuovo = new DBEBanned();
	$nuovo->setValue('ban_ip',$bannato->getValue('ban_ip'));
	$nuovo->setValue('description',$bannato->getValue('ban_reason'));
	$nuovo = $dbmgr->insert($nuovo);
	echo "Created: ".$nuovo->to_string()."\n";
// 	break;
}


?></pre>
</body>
