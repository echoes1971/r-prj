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

// drop tables kware_companies,kware_countrylist,kware_dbversion,kware_files,kware_folders,kware_groups,kware_links,kware_log,kware_notes,kware_objects,kware_pages,kware_people,kware_projects,kware_projects_companies,kware_projects_people,kware_projects_people_roles,kware_projects_projects,kware_projects_projects_roles,kware_timetracks,kware_todo,kware_todo_tipo,kware_users,kware_users_groups,kware_societa,kware_projects_companies_roles;

$my_db_version = $dbmgr->db_version();
$new_db_version = $my_db_version;

// 0. Lettura parametri
$dbetype = $_REQUEST['dbetype'];
$formtype = $_REQUEST['formtype'];

// $dbetype2 = $dbetype=='DBELog' ? 'DBEMyLog' : 'DBELog';
$dbetype3 = 'DBEBanned';

eval("\$dbe = new $dbetype();");
// eval("\$dbe2 = new $dbetype2();");
$dbe3 = $dbmgr->getInstanceByTableName($dbetype3);
// eval("\$dbe3 = new $dbetype3();");
// $dbe = new DBEMyLog();
$tablename = $dbmgr->buildTableName($dbe);
// $tablename2 = $dbmgr->buildTableName($dbe2);
$tablename3 = $dbmgr->buildTableName($dbe3)."banned";

$q="select * from $tablename where url is null or url='' order by ip,data,ora";
$lista = $dbmgr->select($dbetype,$dbe->getTableName(),$q);

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



echo "Fill from Banlist...\n";
echo "============\n";
echo "\n";
echo "\n";
echo "Table name: $tablename\n";
echo "\n";
echo "$q\n\n";
echo "Missing: ".count($lista)."\n";
echo "\n";
foreach($lista as $mydbe) {
	$myip = $mydbe->getValue('ip');
	echo "Checking $myip... ";
	$foundOn=$tablename;
	$q = "select distinct(url) as url from $tablename where url is not null and url>'' and ip='$myip' order by url desc";
	$l = $dbmgr->select($dbetype,$dbe->getTableName(),$q);
	if(count($l)==0) {
		$ip_array = explode('.',$myip);
		$_ip_list = array($myip,
							$ip_array[0].".".$ip_array[1].".".$ip_array[2].".*",
							$ip_array[0].".".$ip_array[1].".*.*",
							$ip_array[0].".*.*.*"
							);
		foreach($_ip_list as $_tmpip) {
			$q = "select concat('BANNED - ',description) as url from $tablename3 where ban_ip='$_tmpip' order by url desc";
			$l = $dbmgr->select($dbetype,$dbe3->getTableName(),$q);
			if(count($l)>0) break;
		}
		$foundOn = $tablename3;
	}
	if(count($l)==0) {
		echo "not found.\n"; continue;
	}
	
	foreach($l as $dbe) {
		$myurl = $dbe->getValue('url');
		echo " $myip => $myurl ($foundOn)\n";
		$q2 = "update $tablename set url='$myurl' where ip='$myip' and (url is null or url='')";
		$dbmgr->db_query($q2);
		$err = $dbmgr->db_error();
		if($err>'') echo " Error: $err\n";
	}
}

?></pre>
</body>
