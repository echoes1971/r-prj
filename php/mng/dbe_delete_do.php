<?php
/**
 * @copyright &copy; 2005-2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbe_delete_do.php $
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

$redir_page = "dbe_list.php";

$dbmgr = $_SESSION['dbmgr'];
if($dbmgr==NULL || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);
$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
if($formulator==null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

// 0. Lettura parametri
$dbetype = $_REQUEST['dbetype'];
$formtype = $_REQUEST['formtype'];
$redir_to = array_key_exists('redir_to',$_REQUEST) ? $_REQUEST['redir_to'] : ''; // 2011.03.07

$myform = $formulator->getInstance($formtype,'xxx','dbe_list.php'); // 2011.04.04 eval("\$myform = new $formtype('xxx','dbe_list.php');");
$myform->readValuesFromRequest( $_REQUEST );

$mydbe = $dbmgr->getInstance($dbetype,$aNames=null,$aValues=null,$aAttrs=$myform->getValues()); // 2011.04.04 eval("\$mydbe = new $dbetype( NULL, NULL, NULL, \$myform->getValues(), NULL);");
$mydbe = $dbmgr->delete( $mydbe );

// 2011.03.07: inizio.
$father_obj=null;
if($redir_to=='father_id' && $myform->getValue('father_id')>'') {
	$redir_page = "dbe_modify.php";
	$father_obj = $dbmgr->fullObjectById($myform->getValue('father_id'));
	if($father_obj!==null) {
		$dbetype = $father_obj->getTypeName();
		$formtype = $formulator->getFormNameByDBEName( $dbetype );
	}
}
// 2011.03.07: fine.

$cgi_params=array();
$cgi_params[]="dbetype=$dbetype";
$cgi_params[]="formtype=$formtype";
if($mydbe==null) {
	$cgi_params[]="messaggio=".urlencode("Error while removing.");
// 2011.03.07: inizio.
} elseif($redir_to=='father_id' && $father_obj!==null) {
	$cgi_params[]="field_id=".urlencode($myform->getValue('father_id'));
// 2011.03.07: fine.
} else {
	$cgi_params[]=$mydbe->getCGIKeysCondition();
	$cgi_params[]="messaggio=".urlencode("");
}

$redir_string = "Location: http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
                      . dirname($_SERVER['PHP_SELF'])
                      . "/$redir_page?".implode("&",$cgi_params);
// die();
header( $redir_string ); /* Redirect browser */
?>
