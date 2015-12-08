<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: obj_reload_do.php $
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

$queries = array(
			"update $tablename set note2='' where `count`>=100",
			"optimize table $tablename",
			);

$errors=array();
foreach($queries as $q) {
// 	echo "$q ... ";
	$dbmgr->db_query($q);
	$err = $dbmgr->db_error();
	if($err>'') $errors[]="Error: $err";
// 	echo ( $err>'' ? "KO\n  Error: $err" : "OK" ) . "\n";
}

$cgi_params=array();
$cgi_params[]="dbetype=$dbetype";
$cgi_params[]="formtype=$formtype";
if(count($errors)>0)
	setMessage(implode("\n",$errors));
else
	setMessage("Optimize: OK");

$redir_string = "Location: http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
                      . dirname($_SERVER['PHP_SELF'])
                      . "/$redir_page?".implode("&",$cgi_params);

header( $redir_string ); /* Redirect browser */

?>
