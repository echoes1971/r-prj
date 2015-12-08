<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: download.php $
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

define("ROOT_FOLDER", ""); //"./");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "utils.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
// require_once(ROOT_FOLDER . "mng/checkUser.php");
//require_once(ROOT_FOLDER . "skins.php");
//require_once(ROOT_FOLDER . "utils.php");

$dbmgr = $_SESSION['dbmgr'];
if ($dbmgr===null || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);
$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
if ($formulator===null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

// 0. Lettura parametri
$myform = $formulator->getInstance('FFile','View',''); // 2011.04.04 new FFile('View','');
$myform->readValuesFromRequest($_REQUEST);
if(array_key_exists('name',$_REQUEST) && $_REQUEST['name']>'') { $myform->setValue('name',$_REQUEST['name']); }
$view_thumb = array_key_exists('view_thumb',$_REQUEST) && $_REQUEST['view_thumb']>'' ? true : false;

$cerca = new DBEFile( null, null, null, $myform->getValues(), null );
$lista = $dbmgr->search( $cerca, 0, null );
$mydbe = $lista[0];

$can_read=false;
$_myuser=$dbmgr->getDBEUser();
if( $mydbe->canRead() ) {
	$can_read=true;
} elseif( $mydbe->canRead('G') && $dbmgr->hasGroup($mydbe->getGroupId()) ) {
	$can_read=true;
} elseif( $mydbe->canRead('U') && $dbmgr->getDBEUser()!=null && $mydbe->getOwnerId()==$_myuser->getValue('id') ) {
	$can_read=true;
}
if(!$can_read) {
	echo "<html>";
	echo "<body>";
	echo "<h1>User not authorized!</h1>";
	echo "</body>";
	echo "</html>";
	die();
}
$dest_path = $mydbe->generaObjectPath();
$dest_dir=realpath($GLOBALS['root_directory'].'/'.$mydbe->dest_directory);
if($dest_path>'') $dest_dir.="/$dest_path";
$filename = "$dest_dir/".($view_thumb ? $mydbe->getThumbnailFilename() : $mydbe->getValue('filename') );

// 2011.03.17: inizio.
$tmp_nome = array_splice( explode("_",$mydbe->getValue('filename')) , 2);
$nome = implode("_",$tmp_nome);
//$nome = $mydbe->getValue('filename');
// 2011.03.17: fine.


rproject_mylog("RDownload","[".$cerca->getValue('id')."] filename: $nome");



header("Content-Type: " . mime_content_type($filename));
header("Content-Length: " .filesize($filename));

header("Content-Disposition: attachment; filename=\"$nome\"");
header("Content-Location: $nome");
header("Content-Base: $nome");

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

readfile($filename);
exit();

?>
