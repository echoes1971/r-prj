<?php
/**
 * @copyright &copy; 2005-2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: jsonserver.php $
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

header('Access-Control-Allow-Origin: http://localhost:3000');

header('Content-type: application/json');

define("ROOT_FOLDER",     "./");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "utils.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "plugins.php");
require_once(ROOT_FOLDER . "utils.php");

$dbmgr = array_key_exists('dbmgr',$_SESSION) ? $_SESSION['dbmgr'] : null;
if ($dbmgr===null || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);

define("MY_DEST_DIR",$GLOBALS[ 'root_directory' ]."/".$GLOBALS[ 'files_directory' ]);

require_once('JSON.php');
$json = new Services_JSON();

function _isAuthorized() {
	global $xmlrpc_require_login;
	if($xmlrpc_require_login===false)
		return true;
	if($xmlrpc_require_login===true && array_key_exists('utente',$_SESSION) && $_SESSION['utente']->getTypeName()>'')
		return true;
	return false;
}

function _dbeToJson(&$dbe) {
  $tmpArray = array();
  $tmpArray["_typeName"] = $dbe->getTypeName();
  $tmpArray["_typename"] = $dbe->getTypeName();
  $dict = $dbe->getValuesDictionary();
  $chiavi = array_keys($dict);
  foreach( array_keys($dict) as $k ) {
	$v = $dict[$k];
	if ($v===null) continue;
	if( is_string($v) && $v!='0000-00-00 00:00:00' ) {
		$_strencoding = mb_detect_encoding($v);
// 		echo "_dbeToJson: encoding=$_strencoding,".substr($v,0,40)."\n";
		if($_strencoding=='ASCII' || $_strencoding=='UTF-8')
			$tmpArray[$k] = $v;
		else
			$tmpArray[$k] = "base64:".base64_encode($v);
	} else {
		$tmpArray[$k] = $v;
	}
  }
  return $tmpArray;
}
function _JsonToDbe(&$dbejson) {
    global $dbmgr;
    if(count($dbejson)!=2) return $dbejson;
    $myfactory = $dbmgr->getFactory();
    $valori = array();
    $aClassname = $dbejson[0];

	$struttura = $dbejson[1];
	foreach( $struttura as $k=>$obj ) {
		$valori[$k] = $obj;
	}
    $ret = $myfactory->getInstance( $aClassname,
                $aNames=NULL, $aValues=NULL, $aAttrs=$valori
                );
    return $ret;
}

ob_start();

if(!array_key_exists('num_calls',$_SESSION)) { $_SESSION['num_calls']=0; };
$_SESSION['num_calls']++;

$raw_input = file_get_contents('php://input');

$json_request = $json->decode($raw_input);


// ********************* Server ***************************

function insert($dbe) {
	global $dbmgr;
	global $xmlrpc_require_login;
	
	$dbmgr->setVerbose(false);
	if(_isAuthorized()) {
		$dbe = $dbmgr->insert($dbe);
		$dbe->setValue('_typename',get_class($dbe));
	} else {
		echo "json_server.insert: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);
	
	return array(_dbeToJson($dbe));
}
function update($dbe) {
	global $dbmgr;
	global $xmlrpc_require_login;
	
	$dbmgr->setVerbose(false);
	if(_isAuthorized()) {
		$dbe = $dbmgr->update($dbe);
		if($dbe!==null) {
			$dbe->setValue('_typename',get_class($dbe));
		}
	} else {
		echo "json_server.update: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);
	
	return array(_dbeToJson($dbe));
}
function delete($dbe) {
	global $dbmgr;
	global $xmlrpc_require_login;
	
	$dbmgr->setVerbose(false);
	if(_isAuthorized()) {
		$dbe = $dbmgr->delete($dbe);
		$dbe->setValue('_typename',get_class($dbe));
	} else {
		echo "json_server.delete: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);
	
	return array(_dbeToJson($dbe));
}
function search($dbe,$uselike,$caseSensitive,$orderby,$ignore_deleted = true,$full_object = true) {
	global $dbmgr;
	global $xmlrpc_require_login;
	$utente = array_key_exists('utente',$_SESSION) ? $_SESSION['utente'] : null;
	
	$listadbe = array();
	
	// 2011.04.27: start.
	// 2011.04.27: opened for public objects
	if(true || _isAuthorized()) {
//	if(_isAuthorized()) {
	// 2011.04.27: end.
		$dbmgr->setVerbose(false);
		$listadbe = $dbmgr->search($dbe,$uselike,$caseSensitive,$orderby,$ignore_deleted,$full_object);
		$dbmgr->setVerbose(false);
	} else {
		echo "json_server.search: Authentication required!\n";
	}
	$retArray=array();
	foreach($listadbe as $mydbe ) {
		$retArray[]=_dbeToJson($mydbe);
	}
	return $retArray;
}

// ************* ObjectMgr: start.
function objectById($myid,$ignore_deleted) {
	global $dbmgr;
	global $xmlrpc_require_login;
	
	$dbmgr->setVerbose(false);
	$dbe = null;
	if(_isAuthorized()) {
		$dbe = $dbmgr->objectById($myid,$ignore_deleted);
		if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	} else {
		echo "json_server.objectById: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);
	
	return $dbe==null ? array() : array(_dbeToJson($dbe));
}
function fullObjectById($myid,$ignore_deleted) {
	global $dbmgr;
	global $xmlrpc_require_login;
	
	$dbmgr->setVerbose(false);
	$dbe = null;
	if(_isAuthorized()) {
		$dbe = $dbmgr->fullObjectById($myid,$ignore_deleted);
		if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	} else {
		echo "json_server.fullObjectById: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);

	return $dbe==null ? array() : array(_dbeToJson($dbe));
}
function objectByName($myid,$ignore_deleted) {
	global $dbmgr;
	global $xmlrpc_require_login;
	
	$dbmgr->setVerbose(false);
	$retArray = array();
	if(_isAuthorized()) {
		$tmp = $dbmgr->objectByName($myid,$ignore_deleted);
		foreach($tmp as $_dbe) {
			$_dbe->setValue('_typename',get_class($_dbe));
			$retArray[]=_dbeToJson($_dbe);
		}
	} else {
		echo "json_server.objectByName: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);
	
	return $retArray;
}
function fullObjectByName($myid,$ignore_deleted) {
	global $dbmgr;
	global $xmlrpc_require_login;
	
	$dbmgr->setVerbose(false);
	$retArray = array();
	if(_isAuthorized()) {
		$tmp = $dbmgr->fullObjectByName($myid,$ignore_deleted);
		foreach($tmp as $_dbe) {
			$_dbe->setValue('_typename',get_class($_dbe));
			$retArray[]=_dbeToJson($_dbe);
		}
	} else {
		echo "json_server.fullObjectByName: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);

	return $retArray;
}
// ************* ObjectMgr: end.

function ping() { return 'pong'; }
function _echo() { return func_get_args(); }
function login($user,$pwd) {
	global $dbmgr;
	$dbmgr->login($user,$pwd);
	$__utente = $dbmgr->getDBEUser();
	$_SESSION['utente'] = $__utente;
	return array( _dbeToJson($__utente) );
}
function getLoggedUser() {
	global $dbmgr;
	$dbmgr->setVerbose(false);
	$__utente=$dbmgr->getDBEUser();
	// Cleaning the pwd field :-) not good to show it back
	if($__utente!==null) $__utente->setValue('pwd',null);
	$dbmgr->setVerbose(false);
	return $__utente!==null ? array( _dbeToJson($__utente) ) : array();
}

function GetRandomUploadDestinationDirectory() {
	$messaggi = "OK";
	$ret=array();
	// Eseguo
	if(_isAuthorized()) {
		$ret['dest_dir'] = "tmp_".rand(0,9);
	} else {
		$messaggi = "KO";
		echo "json_server.GetRandomUploadDestinationDirectory: Authentication required!\n";
	}
	return $ret;
}
function _filtraFilename($s) { return str_replace("'","_",$s); }
function Upload($num_porzione_corrente,$num_porzioni_totali,$filename,$binario,$mydestdir) {
	global $database;
	global $username;
	$ret=array();
	if(_isAuthorized()) {
		if(!file_exists(MY_DEST_DIR."/".$mydestdir)) mkdir(MY_DEST_DIR."/".$mydestdir, 0777 );
		//print "Scrivo: $mydestdir/$filename\n";
		$modo = $num_porzione_corrente==0 ? "w" : "a";
		$fp=fopen(MY_DEST_DIR."/"."$mydestdir/$filename", $modo); // Apro in modo 'append'
		fwrite($fp, $binario );
		fclose($fp);
		$ret['filename'] = $filename;
		$ret['dest_dir'] = $mydestdir;
		//$mycmd = "ls -l ".MY_DEST_DIR."/$mydestdir/$filename";
		//echo "File salvato: ".`$mycmd`;
	} else {
		echo "json_server.Upload: Authentication required!\n";
	}
	return $ret;
}

function Download($uuid,$view_thumb) {
	global $dbmgr;
	global $xmlrpc_require_login;
	$utente = array_key_exists('utente',$_SESSION) ? $_SESSION['utente'] : null;
	$messaggi = "OK";
	$ret=array();
	if(_isAuthorized()) {
		$cerca = new DBEFile();
		$cerca->setValue('id',$uuid);
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
		if($can_read) {
			$dest_path = $mydbe->generaObjectPath();
			$dest_dir=realpath($GLOBALS['root_directory'].'/'.$mydbe->dest_directory);
			if($dest_path>'') $dest_dir.="/$dest_path";
			$filename = "$dest_dir/".($view_thumb ? $mydbe->getThumbnailFilename() : $mydbe->getValue('filename') );
			//echo "json_server.DownloadXmlrpc: filename=$filename\n";
			
			$tmp_nome = array_splice( explode("_",$mydbe->getValue('filename')) , 2);
			$nome = implode("_",$tmp_nome);
			
			$ret['mime']=mime_content_type($filename);
			$ret['filesize']=filesize($filename);
			$ret['filename']=$nome;
			
			$handle = fopen($filename, "rb");
			$ret['contents'] = base64_encode( fread($handle, filesize($filename)) );
			fclose($handle);
		} else {
			$messaggi = "KO";
			echo "json_server.Download: User not authorized!\n";
		}
	} else {
		$messaggi = "KO";
		echo "json_server.Download: Authentication required!\n";
	}
	
	return $ret;
}

$json_response = array();

$mymethod = $json_request->method=='echo' ? '_echo' : $json_request->method;
// echo "mymethod: $mymethod\n";

$myparams = array();
if($json_request!==null) {
	foreach($json_request->params as $_v) {
		$myparams []= is_string($_v) ?
				"'$_v'" :
				( is_bool($_v) ?
				  ( $_v?'true':'false' )
				  : $_v
				);
	}
}
if(function_exists($mymethod)) {
	$tmpArrays=array();
	$myeval = "";
	$tmpParams=array();
	for($_p=0; $_p<count($myparams); $_p++) {
		if(is_array($myparams[$_p])) {
			$tmpArrays[]=_JsonToDbe($myparams[$_p]);
			$myeval .= "\$par$_p=\$tmpArrays[".( count($tmpArrays)-1 )."];\n";
		} else if(is_string($myparams[$_p]) && strpos($myparams[$_p],"base64:")!==false) {
			$tmpArrays[]=base64_decode(substr($myparams[$_p],strlen("base64:")));
			$myeval .= "\$par$_p=\$tmpArrays[".( count($tmpArrays)-1 )."];\n";
		} else {
			$myeval .= "\$par$_p=".$myparams[$_p].";\n";
		}
		$tmpParams[]="\$par$_p";
	}
	$myeval .= "\$ret = $mymethod(".implode(",",$tmpParams).");";
	//echo "myeval: $myeval\n";
	eval($myeval);
	$json_response=$ret;
} elseif(strpos($mymethod,".")!==false) {
	$myeval = "\$ret = \$".implode("->",explode(".",$mymethod))."(".implode(",",$myparams).");";
	//echo "myeval: $myeval\n";
	eval($myeval);
	$json_response=$ret;
} else {
	echo "Unknown method: $mymethod\n";
}

$messaggi = ob_get_contents();
ob_end_clean();

echo "[";
echo $json->encodeUnsafe( base64_encode($messaggi) );
echo ",";
echo $json->encodeUnsafe( $json_response );
echo "]";

?>