<?php
/**
 * @copyright &copy; 2005-2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: login_do.php $
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

$redir_page = ROOT_FOLDER."main.php";

$dbmgr = array_key_exists('dbmgr',$_SESSION) ? $_SESSION['dbmgr'] : null;
if($dbmgr==NULL || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);
$formulator = array_key_exists('formulator',$_SESSION) ? $_SESSION['formulator'] : null;
if($formulator==null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

$from_obj_id = array_key_exists('from_obj_id',$_REQUEST) ? $_REQUEST['from_obj_id'] : null;
if($from_obj_id!==null) $redir_page = ROOT_FOLDER."main.php?obj_id=$from_obj_id";

$valori = readFromRequest( $_REQUEST );
$cerca = new DBEUser(NULL,NULL,NULL,$attrs=$valori,NULL) ;
$ris = $dbmgr->search( $cerca, $uselike=0 );


if( count($valori)==2 && $valori['login']>"" && $valori['pwd']>"" && count($ris)==1 ) {
	// Utente trovato
	$utente = $ris[0];
	$_SESSION['utente'] = $utente;
	$dbmgr->setDBEUser($utente);
	$cerca = new DBEUserGroup();
	$cerca->readFKFrom($utente);
	$lista=$dbmgr->search( $cerca, $uselike=0 );
	$lista_gruppi=array();
	// 2010.07.28: inizio.
	foreach($lista as $g) { $lista_gruppi[]=$g->getValue('group_id'); }
// 	foreach($lista as $g) { $lista_gruppi[]=intval($g->getValue('group_id')); }
	// 2010.07.28: fine.
	if(!in_array($utente->getValue('group_id'), $lista_gruppi))
		$lista_gruppi[]=$utente->getValue('group_id');
	$dbmgr->setUserGroupsList($lista_gruppi);
	if($dbmgr->db_version()!=DB_VERSION && $utente->isRoot()) {
		$redir_page = "db_update.php?messaggio=DB not up-to-date";
	}
	$_SESSION['menu_top']=null;
} elseif(count($ris)==0 && $dbmgr->db_version()==0 && $valori['login']=="adm" && $valori['pwd']=="adm") {
	$cerca->setValue('fullname','Adm (installer)');
	$_SESSION['utente'] = $cerca;
	$dbmgr->setDBEUser($cerca);
	$redir_page = "db_update.php?messaggio=DB not installed";
} else {
	// Utente NON trovato
	$redir_page = "login.php?messaggio=Login error";
}
// echo "Utente: ".$utente->to_string()."<br/>";
// var_dump($lista_gruppi);echo "<br/>";
// exit();

header("Location: http".(array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS']>''?'s':'').'://' . $_SERVER['HTTP_HOST']
                      . dirname($_SERVER['PHP_SELF'])
                      . "/" . $redir_page);
exit;
?>