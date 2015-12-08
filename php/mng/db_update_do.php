<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: db_update_do.php $
 * @package rproject
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */
define("ROOT_FOLDER", "../");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "utils.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "mng/checkUser.php");
require_once(ROOT_FOLDER . "plugins.php");

$dbmgr = $_SESSION['dbmgr'];
if ($dbmgr==NULL || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);

$my_db_version = $dbmgr->db_version();
$new_db_version = $my_db_version;

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
echo ($my_db_version==0 ? "Install" : "Update")."\n";
echo "=======\n";
echo "\n";
echo "\n";

echo "Updating from version: $my_db_version\n";
echo "\n";

$update_errors=array();

// Table definitions update
$tables_update_errors=array();
foreach($dbmgr->getFactory()->getAllClassnames() as $classname) {
	if($classname=='default') continue;
// 	echo "Class: $classname\n";
	$dbe = $dbmgr->getFactory()->getInstance($classname);
	$tablename = $dbmgr->buildTableName( $dbe );
	echo "Table: $tablename\n";
	
	$dbmgr->connect();
// 	echo "Error: ".$dbmgr->db_error()."\n";
// 	echo "connected: ".$dbmgr->isConnected()."\n";
	
	$lista = $dbmgr->getColumnsForTable($tablename);
	$sql = "";
	if(count($lista)==0) {
		$sql .= "create table ".$dbmgr->buildTableName( $dbe )." (\n";
		foreach( $dbe->getColumns() as $col => $def ) {
			$type = $def[0];
			$constraints = count($def)>1 ? $def[1] : "";
// 			echo "type: $type\n";
// 			echo "constraints: $constraints\n";
			$type = DBEntity::dbeType2dbType($type);
			// 2011.03.04: inizio.
			// 2011.03.04: le foreign key POSSONO essere nulle in generale
			$not_null = $dbe->isPrimaryKey($col) ? "not null" : "";
// 			$not_null = $dbe->isPrimaryKey($col) || $dbe->isFK($col) ? "not null" : "";
			// 2011.03.04: fine.
			$sql .= " $col $type ".($constraints>'' ? $constraints : $not_null).",\n";
		}
		$sql .= " primary key(".implode(",",array_keys($dbe->getKeys())).")\n";
		$sql .= ");\n";
		$index_number=0;
		foreach(array_keys($dbe->getKeys()) as $pk) {
			$sql.="create index $tablename"."_".($index_number++)." on $tablename ($pk);\n";
		}
		foreach($dbe->getFK() as $fk) {
			$sql.="create index $tablename"."_".($index_number++)." on $tablename (".$fk->colonna_fk.");\n";
		}
	} else {
		$dbe_columns = $dbe->getColumns();
		$missing_dbe=array();
		$missing_table=array();
		$differs=array();
		// Controllo colonne
		foreach(array_keys($dbe_columns) as $col) {
			if(! array_key_exists($col,$lista) )
				$missing_table[] = $col;
		}
		foreach($lista as $col=>$def) {
			if( array_key_exists($col,$dbe_columns) ) {
				$_type_db  = $def['Type'];
				$_type_dbe = DBEntity::dbeType2dbType($dbe_columns[$col][0]);
				$_constraints_db  = DBEntity::dbConstraints2dbeConstraints($def);
				$_constraints_dbe = count($dbe_columns[$col])>1 ? $dbe_columns[$col][1] : '';
				if( $_type_db==$_type_dbe && $_constraints_db==$_constraints_dbe ) {
				} else {
// 					if($_type_db!=$_type_dbe) echo " $_type_db::$_type_dbe\n";
// 					if($_constraints_db!=$_constraints_dbe) echo " $_constraints_db::$_constraints_dbe\n";
					$differs[]=$col;
// 					echo "$col => "; var_dump($def);
				}
			} else {
				$missing_dbe[]=$col;
				echo "$col => "; var_dump($def);
			}
// 			echo var_dump($row); //->to_string("\n")."\n";
		}
		
		// Report
		if(count($missing_dbe)>0) {
			echo "Missing on DBE:\n";
			foreach($missing_dbe as $col) {
				echo " $col\n";
				$constraints = array();
				if( $lista[$col]['Null']=='NO' ) $constraints[]="not null";
				if( $lista[$col]['Default']>'' ) $constraints[]="default ".$lista[$col]['Default'];
				$constraints = (count($constraints)>0 ? "'".implode(" ",$constraints)."'," : '');
				echo "'$col'=>array('".DBEntity::dbType2dbeType($lista[$col]['Type'])."',"
						.($constraints>'' ? $constraints : '')
						."),\n";
				$_sql = "alter table $tablename drop column $col;";
				$sql .= $_sql;
			}
		}
		if(count($missing_table)>0) {
			echo "Missing on table:"; var_dump($missing_table);
			foreach($missing_table as $col) {
				echo " $col\n";
				$dbe_definition = DBEntity::dbeType2dbType($dbe_columns[$col][0])." ".(count($dbe_columns[$col])>1?$dbe_columns[$col][1]:'');
				$_sql = "alter table $tablename add column $col $dbe_definition;";
				$sql .= $_sql;
			}
		}
		if(count($differs)>0) {
			echo "Differs:\n";
			foreach($differs as $col) {
				echo " $col\n";
				$dbe_definition = DBEntity::dbeType2dbType($dbe_columns[$col][0])." ".(count($dbe_columns[$col])>1?$dbe_columns[$col][1]:'');
				$db_definition  = $lista[$col]['Type']." ".DBEntity::dbConstraints2dbeConstraints($lista[$col]);
				echo "  DBE[$col]:$dbe_definition\n";
				echo "   DB[$col]:$db_definition\n";
				$_sql = "alter table $tablename modify column $col $dbe_definition;";
				$sql .= $_sql;
			}
		}
		// Controllo chiavi
		$missing_keys=array();
		foreach(array_keys($dbe->getKeys()) as $pk) {
			if( array_key_exists($pk, $lista) && $lista[$pk]['Key']=='PRI' ) continue;
// 			echo "Missing key: ";var_dump($lista[$pk]);
			$missing_keys[]=$pk;
			break;
		}
		if(count($missing_keys)>0) {
			$sql .= "alter table $tablename drop primary key;";
			$sql .= "alter table $tablename add primary key(".implode(",",array_keys($dbe->getKeys())).");";
			foreach($missing_keys as $pk) {
				$sql.="create index $tablename"."_$pk on $tablename ($pk);";
			}
		}
		
	}
	foreach(explode(";",$sql) as $_sql) {
		$_sql = str_replace("\n","",$_sql);
		if($_sql>'') {
			echo " Executing: $_sql\n";
			$dbmgr->connect();
			$dbmgr->db_query($_sql);
			$_errors = $dbmgr->db_error();
			if($_errors>'') {
				$update_errors[]=$_errors." ($_sql)";
				break;
			}
		}
	}
	if(!($sql>'')) echo "       OK";
	echo "\n";
	if(count($update_errors)>0) break;
}
echo "\n";

// Extra tasks
$all_ok=count($update_errors)==0;
if($all_ok && $my_db_version<1) {
	echo "Section $new_db_version: start.\n";
	$queries = array(
		"insert into _dbversion values ('rprj',0)",
		//"insert into _dbversion values (0)",
		"insert into _users values ( -1, 'adm','adm','','Administrator',-2 )",
		"insert into _groups values ( -2, 'Admin','System admins' )",
		"insert into _groups values ( -3, 'Users','System users' )",
		"insert into _groups values ( -4, 'Guests','System guests (read only)' )",
		"insert into _groups values ( -5, 'Project','R-Project user' )",
		"insert into _groups values ( -6, 'Webmaster','Web content creators' )",
		"insert into _users_groups ( user_id, group_id ) values( -1, -2 )",
		"insert into _users_groups ( user_id, group_id ) values( -1, -5 )",
		"insert into _users_groups ( user_id, group_id ) values( -1, -6 )",
		// Web folders
		"insert into _folders ("
			."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,fk_obj_id,childs_sort_order"
			.") values (-10,-1,-6,'rwxrw-r--',-1,now(),-1,now(),0,'Home','',0,'-11,-12,-13,-14')",
		"insert into _folders ("
			."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,fk_obj_id,childs_sort_order"
			.") values (-11,-1,-6,'rwxrw-r--',-1,now(),-1,now(),-10,'Products','',0,'')",
		"insert into _folders ("
			."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,fk_obj_id,childs_sort_order"
			.") values (-12,-1,-6,'rwxrw-r--',-1,now(),-1,now(),-10,'Services','',0,'')",
		"insert into _folders ("
			."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,fk_obj_id,childs_sort_order"
			.") values (-13,-1,-6,'rwxrw-r--',-1,now(),-1,now(),-10,'Downloads','',0,'')",
		"insert into _folders ("
			."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,fk_obj_id,childs_sort_order"
			.") values (-14,-1,-6,'rwxrw-r--',-1,now(),-1,now(),-10,'About us','',0,'')",
		// Web pages: under construction
		"insert into _pages ("
		."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,html,fk_obj_id"
		.") VALUES ("
		."'-20','-1','-6','rwxrw-r--','-1',now(),'-1',now(),'-10','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-10'"
		.")",
		"insert into _pages ("
		."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,html,fk_obj_id"
		.") VALUES ("
		."'-21','-1','-6','rwxrw-r--','-1',now(),'-1',now(),'-11','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-11'"
		.")",
		"insert into _pages ("
		."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,html,fk_obj_id"
		.") VALUES ("
		."'-22','-1','-6','rwxrw-r--','-1',now(),'-1',now(),'-12','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-12'"
		.")",
		"insert into _pages ("
		."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,html,fk_obj_id"
		.") VALUES ("
		."'-23','-1','-6','rwxrw-r--','-1',now(),'-1',now(),'-13','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-13'"
		.")",
		"insert into _pages ("
		."id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description,html,fk_obj_id"
		.") VALUES ("
		."'-24','-1','-6','rwxrw-r--','-1',now(),'-1',now(),'-14','index','','<div id=\"underconstruction\"><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/><h1>Under Construction</h1><br/></div>','-14'"
		.")",
	);
	for($q=0; $q<count($queries); $q++) {
		$_query = str_replace("insert into _","insert into ".$dbmgr->getSchema()."_",$queries[$q]);
		$dbmgr->connect();
		$dbmgr->db_query($_query);
		$errors = $dbmgr->db_error();
		if(substr($errors,0,15)=='Duplicate entry') $errors='';
		else echo htmlentities(" $_query\n");
		if($errors>'') {
			echo "DB Error: $errors\n";
			echo "   Query: $_query\n";
			$all_ok = false;
			$update_errors[]=$errors;
			break;
		}
	}
	
	// Country list
	$filename = "countrylist.sql";
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);
	$countries = explode(";",$contents);
	for($q=0; $q<count($countries); $q++) {
		$_query = str_replace("insert into rra_","insert into ".$dbmgr->getSchema()."_",$countries[$q]);
		$_query = str_replace("\n","",$_query);
		if( !($_query>'')
			|| substr($_query,0,10)=='drop table'
			|| substr($_query,0,12)=='create table'
			)
			continue;
		$dbmgr->connect();
		$dbmgr->db_query($_query);
		$errors = $dbmgr->db_error();
		if(substr($errors,0,15)=='Duplicate entry') $errors='';
		else echo htmlentities(" $_query\n");
		if($errors>'') {
			echo "DB Error: $errors\n";
			$all_ok = false;
			$update_errors[]=$errors;
			break;
		}
	}
	
	echo "Section $new_db_version: end.\n\n";
	if($all_ok) $new_db_version++;
}
if($all_ok && $my_db_version<2) {
	echo "Blocco $new_db_version: inizio.\n";
	$queries = array(
			"create index ".$dbmgr->getSchema()."_events_idx2 on ".$dbmgr->getSchema()."_events (start_date)",
			"create index ".$dbmgr->getSchema()."_events_idx3 on ".$dbmgr->getSchema()."_events (end_date)",
	);
	for($q=0; $q<count($queries); $q++) {
		$dbmgr->connect();
		$dbmgr->db_query($queries[$q],$dbmgr->getConnection());
	}
	echo "Blocco $new_db_version: fine.\n";
	$new_db_version++;
}

$stringa="update ".$dbmgr->buildTableName( new DBEDBVersion() )." set version=$new_db_version";
echo "Version: $stringa\n";
$dbmgr->connect();
$dbmgr->db_query($stringa);
$errors = $dbmgr->db_error();
if($errors>'') $update_errors[]=$errors;

echo "\n";

if($new_db_version<DB_VERSION) {
	echo "Troubles in updating: last installed version $new_db_version.\n";
	if(count($update_errors)>0) {
		echo "Errors:\n ".implode("\n ",$update_errors)."\n\n";
	}

} else {
	echo "Update: success!\n";
	echo "New version: ".DB_VERSION."\n";
	echo "\n";
	echo "Please <a target=\"_top\" href=\"".ROOT_FOLDER."logout_do.php\" style=\"font-weight:bold;color:green;\">LOGOUT</a>.\n";
	echo "\n";
}
echo "\n";

?></pre>
</body>
