<?php
/**
 * @copyright &copy; 2005-2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbe_modify.php $
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
if(!defined("ROOT_FOLDER")) define("ROOT_FOLDER", "../");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "mng/checkUser.php");
require_once(ROOT_FOLDER . "plugins.php");
require_once(ROOT_FOLDER . "skins.php");
require_once(ROOT_FOLDER . "utils.php");

$dbmgr = array_key_exists('dbmgr',$_SESSION) ? $_SESSION['dbmgr'] : null;
if ($dbmgr===null || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);
$formulator = array_key_exists('formulator',$_SESSION) ? $_SESSION['formulator'] : null;
if ($formulator===null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

// 0. Lettura parametri
$dbetype = $_REQUEST['dbetype'];
$formtype = $_REQUEST['formtype'];


$myform = $formulator->getInstance($formtype,'Modify','dbe_modify_do.php'); // 2011.04.04 eval("\$myform = new $formtype('Modify','dbe_modify_do.php');");
$myform->readValuesFromRequest($_REQUEST);

$cerca = $dbmgr->getInstance($dbetype,$aNames=null,$aValues=null,$aAttrs=$myform->getValues()); // 2011.04.04 eval("\$cerca = new $dbetype( NULL, NULL, NULL, \$myform->getValues(), NULL );");
$lista = $dbmgr->search( $cerca, 0, null );
if(count($lista)!=1) {
	$messaggio = array_key_exists('messaggio',$_REQUEST) && $_REQUEST['messaggio']>'' ? "?messaggio=".$_REQUEST['messaggio'] : '';
	$redir_page = $myform->getPagePrefix()."_list.php?dbetype=$dbetype&formtype=$formtype";
	$redir_string = "Location: http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. dirname($_SERVER['PHP_SELF'])
						. "/$redir_page$messaggio";
	header( $redir_string );
	die();
}
$mydbe = $lista[0];
$childs=array();

if(is_a($myform, 'FMasterDetail')) {
	for($i=0; $i<$myform->getDetailFormsCount(); $i++) {
		$childForm = $myform->getDetail($i);
		$childDbe = $childForm->getDBE();
		$childDbe->readFKFrom($mydbe);
		$tmp = $dbmgr->search($childDbe,$uselike=0);
		if (is_a($childForm, 'FAssociation')) {
			$tmp2=array();
			$dest_form = get_class($myform)==get_class($childForm->getFromForm()) ? $childForm->getToForm() : $childForm->getFromForm();
			$dest_dbe = $dest_form->getDBE();
			foreach($tmp as $assdbe) {
				$dest_dbe = $assdbe->writeFKTo($dest_dbe);
				$tmplist = $dbmgr->search($dest_dbe,$uselike=0);
				$tmp2[] = $tmplist[0];
			}
			$tmp=$tmp2;
		}
		$childs[get_class($childForm)] = $tmp;
	}
}

$myform->setValues($mydbe->getValuesDictionary());

$campiVisibili = $myform->getDetailColumnNames();
$campiReadonly = $myform->getDetailReadOnlyColumnNames();

// *************** Gestione Menu ***************
require_once(ROOT_FOLDER . "mng/_mng_menu.php");

require_once(getSkinPage("mng/dbe_modify.php"));
?>
