<?php
define("ROOT_FOLDER",     "../");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "utils.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "plugins.php");
require_once(ROOT_FOLDER . "utils.php");

$redir_page = ROOT_FOLDER."mng/dbe_list.php";

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


eval("\$dbe = new $dbetype();");
// $dbe = new DBEMyLog();
$tablename = $dbmgr->buildTableName($dbe);

$q="select * from $tablename where url is null or url='' order by ip,data,ora";
$lista = $dbmgr->select($dbetype,$dbe->getTableName(),$q);

// echo "Auto fill...\n";
// echo "============\n";
// echo "\n";
// echo "\n";
// echo "Table name: $tablename\n";
// echo "\n";
// echo "$q\n\n";
// echo "Missing: ".count($lista)."\n";
// echo "\n";
$errors=array();
$errors[]="Missing: ".count($lista)."<br/>";
foreach($lista as $mydbe) {
// 	echo $mydbe->to_string()."\n";
	$myip = $mydbe->getValue('ip');
// 	echo "Checking $myip... ";
	$foundOn=$tablename;
	$q = "select distinct(url) as url from $tablename where url is not null and url>'' and ip='$myip' order by url desc";
// 	echo "$q\n";
	$l = $dbmgr->select($dbetype,$dbe->getTableName(),$q);
	if(count($l)==0) {
		$errors[]="$myip... not found.<br/>";
// 		echo "not found.\n";
		continue;
	}
	
// 	echo "Entries:\n";
	foreach($l as $dbe) {
		$myurl = $dbe->getValue('url');
// 		echo " $myip => $myurl ($foundOn)\n";
		$q2 = "update $tablename set url='$myurl' where ip='$myip' and (url is null or url='')";
// 		echo " $q2\n";
		$dbmgr->db_query($q2);
		$err = $dbmgr->db_error();
// 		if($err>'') echo " Error: $err\n";
		if($err>'') $errors[]="Error: $err";
	}
}



$cgi_params=array();
$cgi_params[]="dbetype=$dbetype";
$cgi_params[]="formtype=$formtype";
if(count($errors)>0)
	setMessage(implode("\n",$errors));
else
	setMessage("Auto Fill: OK");

$redir_string = "Location: http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
                      . dirname($_SERVER['PHP_SELF'])
                      . "/$redir_page?".implode("&",$cgi_params);

header( $redir_string );
?>
