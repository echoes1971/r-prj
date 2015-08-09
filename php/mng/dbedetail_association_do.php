<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbedetail_association_do.php $
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

$redir_page = "dbedetail_association.php";

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

// 0. Lettura Parametri
$from_type=$_REQUEST['from_type'];
$to_type=$_REQUEST['to_type'];
$from_form=$_REQUEST['from_form'];
$to_form=$_REQUEST['to_form'];
$from_chiave=readFromRequest($_REQUEST);
$key_list = array_keys( readFromRequest($_REQUEST, 'key_list_') );

// 1. Leggo le to_type associate
$fromDBE = $dbmgr->getInstance($from_type); // 2011.04.04 eval("\$fromDBE = new $from_type();");
$fromDBE->setValuesDictionary( $from_chiave );
$toDBE = $dbmgr->getInstance($to_type); // 2011.04.04 eval("\$toDBE = new $to_type();");
$toDBE->readFKFrom( $fromDBE );


// echo "from_type: $from_type<br>\n";
// echo "to_type: $to_type<br>\n";
// echo "from_form: $from_form<br>\n";
// echo "to_form: $to_form<br>\n";
// echo "from_chiave: "; var_dump($from_chiave); echo "<br>\n";
// echo "key_list: "; var_dump($key_list); echo "<br>\n";
// echo "fromDBE: ".$fromDBE->to_string()."<br/>\n";
// echo "toDBE: ".$toDBE->to_string()."<br/>\n";


if( is_a($toDBE,'DBAssociation') ) {
	$decode_tablename = $fromDBE->getTableName()==$toDBE->getFromTableName() ? $toDBE->getToTableName() : $toDBE->getFromTableName();
	$_myFactory = $dbmgr->getFactory();
	$decode_type = $_myFactory->getInstanceByTableName( $decode_tablename );
	
	// 1. Cancello le vecchie relazioni
	$cerca = $toDBE;
// 	echo "cerca: ".$cerca->to_string()."<br/>\n";
	$da_cancellare = $dbmgr->search($cerca,$orderby=$cerca->getOrderByString() );
	foreach($da_cancellare as $questa) {
// 		echo "da cancellare: ".$questa->to_string()."<br/>\n";
		$dbmgr->delete($questa);
	}
	// 2. Inserisco le nuove
	foreach($key_list as $k) {
		$decode_type = setKeyString($decode_type,$k);
		$toDBE->readFKFrom($decode_type);
// 		echo "da inserire: ".$toDBE->to_string()."<br/>\n";
		$dbmgr->insert($toDBE);
	}
} else {
	// 0. Campi FK nella toDBE
	$fks = $toDBE->getFKForTable( $fromDBE->getTableName() );
// 	echo "fks: "; foreach($fks as $fk) echo $fk->to_string()."; "; echo "<br/>\n";
	$fk_fields = array(); foreach($fks as $fk) $fk_fields[]=$fk->colonna_fk;
// 	echo "fk_fields: "; foreach($fk_fields as $fk) echo $fk."; "; echo "<br/>\n";
	// 1. Cancello le vecchie relazioni
	$cerca = $toDBE;
// 	echo "cerca: ".$cerca->to_string()."<br/>\n";
	$da_cancellare = $dbmgr->search($cerca,$orderby=$cerca->getOrderByString() );
	foreach($da_cancellare as $questa) {
		/** TODO non e' una cosa molto carina */
		foreach($fk_fields as $fk) if( is_numeric($questa->getValue($fk)) ) $questa->setValue($fk,0); else $questa->setValue($fk,'');
// 		echo "da cancellare: ".$questa->to_string()."<br/>\n";
// 		echo "query: ".$dbmgr->_buildUpdateString($questa)."<br/>\n";
		$dbmgr->update($questa);
	}
	// 2. Inserisco le nuove
	foreach($key_list as $k) {
		$toDBE = setKeyString($toDBE,$k);
// 		echo "da aggiornare: ".$toDBE->to_string()."<br/>\n";
// 		echo "query: ".$dbmgr->_buildUpdateString($toDBE)."<br/>\n";
		$dbmgr->update($toDBE);
	}
}


$cgi_params=array();
$cgi_params[]="from_type=$from_type";
$cgi_params[]="to_type=$to_type";
$cgi_params[]="from_form=$from_form";
$cgi_params[]="to_form=$to_form";
$cgi_params[]=$fromDBE->getCGIKeysCondition();
$cgi_params[]="reload=reload";
$cgi_params[]="messaggio=";

$redir_string = "Location: http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
                      . dirname($_SERVER['PHP_SELF'])
                      . "/" . $redir_page . "?".implode("&",$cgi_params);
// echo "redir_string: $redir_string<br>\n";

// exit();



header( $redir_string ); /* Redirect browser */
?>
