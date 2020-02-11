<?php
/**
 * @copyright &copy; 2005-2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbedetail_association.php $
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
if ($formulator===null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

// 0. Lettura Parametri
$from_type=$_REQUEST['from_type'];
$to_type=$_REQUEST['to_type'];
$from_form=$_REQUEST['from_form'];
$to_form=$_REQUEST['to_form'];
$from_chiave=readFromRequest($_REQUEST);
$reload=array_key_exists('reload',$_REQUEST) ? $_REQUEST['reload'] : '';
$messaggio=array_key_exists('messaggio',$_REQUEST) ? $_REQUEST['messaggio'] : '';

/*
echo "from_type: $from_type<br>\n";
echo "to_type: $to_type<br>\n";
echo "from_form: $from_form<br>\n";
echo "to_form: $to_form<br>\n";
echo "from_chiave: "; var_dump($from_chiave); echo "<br>\n";
*/

// 1. Leggo le to_type associate
$fromDBE = $dbmgr->getInstance($from_type); // 2011.04.04 eval("\$fromDBE = new $from_type();");
$fromDBE->setValuesDictionary( $from_chiave );
$stringa_chiave_from=getKeyString($fromDBE);
$toDBE = $dbmgr->getInstance($to_type); // 2011.04.04 eval("\$toDBE = new $to_type();");
$to_list_full = $dbmgr->search($toDBE, $orderby=$toDBE->getOrderByString() );
$toDBE->readFKFrom( $fromDBE );
$to_list = $dbmgr->search($toDBE, $uselike=0, $orderby=$toDBE->getOrderByString() );
// 1.1 Dizionario di decodifica dei valori selezionati
$to_dict=array();
foreach($to_list as $tmp) {
	$stringa_chiave=getKeyString($tmp);
	$to_dict[$stringa_chiave]=$tmp;
}
// 1.2 Dizionario delle chiavi dei valori selezionati
$to_dict_keys=array_keys($to_dict);

// 2. Creo la lista di decodifica
$decoded_list_full=array();
$decoded_dict_full=array();
if( is_a($toDBE,'DBAssociation') ) {
	$decode_tablename = $fromDBE->getTableName()==$toDBE->getFromTableName() ? $toDBE->getToTableName() : $toDBE->getFromTableName();
	$_myFactory = $dbmgr->getFactory();
	$decode_type = $_myFactory->getInstanceByTableName( $decode_tablename );
	
	$decoded_list_full = $dbmgr->search($decode_type,$orderby=$decode_type->getOrderByString() );
} else {
	$decoded_list_full = $to_list_full;
}
// 2.1 Creo il dizionario di decodifica
foreach($decoded_list_full as $tmp) {
	$stringa_chiave=getKeyString($tmp);
	$decoded_dict_full[$stringa_chiave]=$tmp;
}

// 3. Determino la form da utilizzare per la visualizzazione
$from_form_instance = $formulator->getInstance($from_form); // 2011.04.04 eval("\$from_form_instance = new $from_form();");
$view_form = $formulator->getInstance($to_form); // 2011.04.04 eval("\$view_form = new $to_form();");
if( is_a($view_form, 'FAssociation') )
	$view_form = get_class( $from_form_instance ) == get_class( $view_form->getFromForm() ) ? $view_form->getToForm() : $view_form->getFromForm(); /** FIXME funziona con il formfactory? mettere in OR con un is_a ?  */



require_once( getSkinPage("mng/dbedetail_association.php") );
?>
