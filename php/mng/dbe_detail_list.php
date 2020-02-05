<?php
/**
 * @copyright &copy; 2005-2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbe_detail_list.php $
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
require_once(ROOT_FOLDER . "skins.php");

$dbmgr = $_SESSION['dbmgr'];
if ($dbmgr==NULL || get_class($dbmgr)=='__PHP_Incomplete_Class') {
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

// 0. Lettura parametri
$dbetype = $_REQUEST['dbetype'];
$formtype = $_REQUEST['formtype'];
$masterformtype = $_REQUEST['masterformtype'];
$filterformtype = array_key_exists('filterformtype',$_REQUEST) ? $_REQUEST['filterformtype'] : null;

// 0.1 Istanzio la form
$myform = $formulator->getInstance($formtype); // 2011.04.04 eval("\$myform = new $formtype();");

// 0.2 Determino la filterForm
$filterForm=null;
if($filterformtype>"") {
	$filterForm = $formulator->getInstance($filterformtype); // 2011.04.04 eval("\$filterForm = new $filterformtype();");
} else if( $myform->getFilterForm()!=null ) {
	$filterForm=$myform->getFilterForm();
}
if($filterForm!=null) {
	if( array_key_exists( "filtra", $_REQUEST ) ) {
		$filterForm->readValuesFromArray($_REQUEST);
// 		$filterForm->writeValuesToArray($_SESSION,$filterformtype."_");
	} else {
// 		$filterForm->readValuesFromArray($_SESSION,$filterformtype."_",true);
	}
}

$cerca = $dbmgr->getInstance($dbetype); // 2011.04.04 eval("\$cerca = new $dbetype();");
if($filterForm!=null) $cerca->setValuesDictionary( $filterForm->getFilterValues() );
$listaDBE = $dbmgr->search( $cerca, 1, false, $orderby=$cerca->getOrderByString() );

$lista_form = $formulator->getInstance($formtype); // 2011.04.04 eval("\$lista_form = new $formtype();");
$lista_title=$lista_form->getListTitle();
$lista_colonne = $lista_form->getListColumnNames();
$prefisso_pagine = $lista_form->getPagePrefix();

require_once( getSkinPage("mng/dbe_detail_list.php") );
?>
