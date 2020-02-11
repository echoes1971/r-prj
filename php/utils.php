<?php
/**
 * @copyright &copy; 2005-2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: utils.php $
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

function getRootUri($path_separator="/") {
	// 2011.03.31: start.
	$filepath=array_reverse(explode($path_separator,dirname($_SERVER["SCRIPT_FILENAME"])));
	$uri=array_reverse(explode("/",dirname($_SERVER["PHP_SELF"])));
// 	$filepath=array_reverse(explode($path_separator,dirname($_SERVER["SCRIPT_FILENAME"])));
// 	$uri=array_reverse(explode("/",dirname($_SERVER["PHP_SELF"])));
	// 2011.03.31: end.
	$maxindice=min(count($filepath), count($uri));
	$indice_diversi=-1;
	for($i=0; $indice_diversi<0 && $i<$maxindice; $i++) {
		if($filepath[$i]!=$uri[$i])
			$indice_diversi=$i;
	}
	$ret = implode("/", array_reverse(array_slice($uri,$indice_diversi-1)));
	return $ret;
}
function getRootFolder($path_separator="/") {
	// 2011.03.31: start.
	$filepath=array_reverse(explode($path_separator,dirname($_SERVER["SCRIPT_FILENAME"])));
	$uri=array_reverse(explode("/",dirname($_SERVER["PHP_SELF"])));
// 	$filepath=array_reverse(explode($path_separator,$_SERVER["SCRIPT_FILENAME"]));
// 	$uri=array_reverse(explode("/",$_SERVER["PHP_SELF"]));
	// 2011.03.31: end.
	$maxindice=min(count($filepath), count($uri));
	$indice_diversi=-1;
	for($i=0; $indice_diversi<0 && $i<$maxindice; $i++) {
		if($filepath[$i]!=$uri[$i])
			$indice_diversi=$i;
	}
	// 2011.03.11: start.
	$ret = $indice_diversi>0 ?
			implode($path_separator, array_reverse(array_slice($filepath,$indice_diversi-1)))
			: dirname($_SERVER["SCRIPT_FILENAME"]);
// 	$ret = implode($path_separator, array_reverse(array_slice($filepath,$indice_diversi-1)));
	// 2011.03.11: end.
	return $ret;
}

function getTodayString($with_time=true, $date_separator="/") {
	$oggi_array = getdate(time());
	$oggi = $oggi_array['year'].$date_separator.( strlen($oggi_array['mon'])<2 ? "0" : "").$oggi_array['mon'].$date_separator.( strlen($oggi_array['mday'])<2 ? "0" : "").$oggi_array['mday'];
	if($with_time) $oggi.=" ".( strlen($oggi_array['hours'])<2 ? "0" : "").$oggi_array['hours'].":".( strlen($oggi_array['minutes'])<2 ? "0" : "").$oggi_array['minutes'];
	return $oggi;
}

/**
 * Data una DBE, ritorna una stringa con i valori dei campi chiave
 */
function getKeyString($dbe) {
	return implode("_", array_values( $dbe->getKeysDictionary()));
}
function setKeyString($dbe,$key_string) {
	$chiavi = array_keys($dbe->getKeys());
	$valori=explode("_",$key_string);
	for($i=0; $i<count($chiavi);$i++) {
		$dbe->setValue($chiavi[$i],$valori[$i]);
	}
	return $dbe;
}

/** Reads parameters from request starting with field_ */
function readFromRequest( $aRequest, $prefix='field_') {
	$ret = array();
	$len_ret = 0;
	$chiavi = array_keys( $aRequest);
	foreach($chiavi as $c) {
		$pos = strpos( $c, $prefix);
		if($pos === false) {
		} else {
			$v = $aRequest[$c];
			$ret[substr($c, strlen($prefix))] = $v;
		}
	}
	return $ret;
}
/** Reads parameters from a generic array starting with field_ */
function readFromArray( &$aArray, $prefix='field_') {
	$ret = array();
	$len_ret = 0;
	$chiavi = array_keys($aArray);
	foreach($chiavi as $c) {
		$pos = strpos($c, $prefix);
		if($pos === false) {
		} else {
			$v = $aArray[$c];
			$ret[substr($c, strlen($prefix))] = $v;
		}
	}
	return $ret;
}

/**
 * Ritorna il linguaggio del client collegato:
 * ita: se l'header 'Accept-Language' contiene la stringa 'it'
 * eng, altrimenti
 */
function getClientLanguage() {
	$linguaggio='';
	// SE viene passato il parametro language
	if(array_key_exists('language',$_REQUEST) && $_REQUEST['language']!='') {
		// setto il linguaggio in sessione
		$_SESSION['language'] = $_REQUEST['language'];
	}
	if(array_key_exists('language',$_SESSION) && $_SESSION['language']!='') {
		$linguaggio = $_SESSION['language'];
	} else {
		if(array_key_exists('HTTP_ACCEPT_LANGUAGE', $_ENV))
			$linguaggio = $_ENV[ 'HTTP_ACCEPT_LANGUAGE' ];
	}
	
	$_pos = strpos( $linguaggio, 'it');
	if($_pos === false) {
		return 'eng';
	} else {
		return 'ita';
	}
}

function rproject_mylog($note=null,$note2=null,$debug=false) {
	global $dbmgr;
	$my_note = $note===null && array_key_exists('note',$_REQUEST) ? $_REQUEST['note'] : $note;
	$my_note2 = $note2===null ?
					(array_key_exists('note2',$_REQUEST) && $_REQUEST['note2']>'' ?
						$_REQUEST['note2'] : $_SERVER["REQUEST_URI"])
					: $note2;
	$remote_ip=$_SERVER['REMOTE_ADDR'];
	if(array_key_exists('HTTP_X_FORWARDED_FOR',$_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR']>'') {
		$remote_ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	$oggi = strftime("%Y-%m-%d", date(time()));
	$ora = strftime("%H:%M:%S", date(time()));
	
	if($debug) {
		echo "remote_ip: $remote_ip<br>";
		echo "oggi: $oggi<br>";
		echo "ora: $ora<br>";
	}
	
	// Checking if an old entry exists
	// 2012.07.21: start.
// 	$cerca = new DBELog();
// 	$cerca->setValue('ip', $remote_ip);
// 	$listaPast = $dbmgr->search( $cerca, 0, 'url desc');
// 	if($debug) { print "$listaPast: $listaPast"; }
	// 2012.07.21: end.
	
	// Checking if today's entry exists
	$cerca = new DBELog();
	$cerca->setValue('ip', $remote_ip);
	$cerca->setValue('data', $oggi);
	$lista = $dbmgr->search( $cerca, 0, '');
	if($debug) { print "lista: "; var_dump( $lista); }
	
	$mydbe=new DBELog;
	$mydbe->setValue('ip', $remote_ip);
	$mydbe->setValue('data', $oggi);
	$mydbe->setValue('ora', $ora);
	
	// 2012.07.21: start.
// 	// IF I have older entries for the same IP
// 	if(count($listaPast)>0) $mydbe->setValue('url', $listaPast[0]->getValue('url'));
	// 2012.07.21: end.
	
	if(count($lista)==0) {
		// 2012.07.21: start.
		// FIXME maybe using a max on url desc could work better, something like
		// FIXME select max(url) as url from _log where ip='remote_ip'
		$cerca = new DBELog();
		$cerca->setValue('ip', $remote_ip);
		$listaPast = $dbmgr->search( $cerca, 0, 'url desc');
		if($debug) { print "$listaPast: $listaPast"; }
		// IF I have older entries for the same IP
		if(count($listaPast)>0) $mydbe->setValue('url', $listaPast[0]->getValue('url'));
		// 2012.07.21: end.
		// New entry
		$mydbe->setValue('count', 1);
		// ONLY IN CASE OF A NEW ENTRY
		if($my_note!=null && strlen($my_note)>0)
			$mydbe->setValue('note', $my_note);
		if($my_note2!=null && strlen($my_note2)>0)
			$mydbe->setValue('note2', "$ora-$my_note2");
		$dbmgr->insert($mydbe);
	} else {
		// Updating counter
		$mydbe->setValue('count', $lista[0]->getValue('count')+1);
		if($my_note2!=null && strlen($my_note2)>0)
			$mydbe->setValue('note2', $lista[0]->getValue('note2') . "\n$ora-$my_note2");
		$dbmgr->update($mydbe);
	}
}

function setMessage($msg,$key='session_message') {
	$_SESSION[$key]=$msg;
}
function getMessage($key='session_message') {
// 	global $_SESSION;
// 	echo "utils::getMessage: \$_SESSION['$key']=".$_SESSION[$key]."<br/>\n";
	$ret = array_key_exists($key,$_SESSION) && $_SESSION[$key]>'' ? $_SESSION["$key"] : '';
	$_SESSION[$key]='';
// 	$_SESSION["session_message"]=null;
// 	var_dump($_SESSION);
	return $ret;
}
?>
