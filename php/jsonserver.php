<?php
/**
 * @copyright &copy; 2005-2022 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
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

// **** Cross-site: start.
// This to persist cookies in Cross-Site calls
// On the client:
// - xhr.withCredentials = true;
// On the server side:
// - Access-Control-Allow-Origin: http://localhost:3000
// - Access-Control-Allow-Credentials: true
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
// header('Access-Control-Allow-Origin: *');
// **** Cross-site: end.



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

$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
if($formulator===null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}


define("MY_DEST_DIR",$GLOBALS[ 'root_directory' ]."/".$GLOBALS[ 'files_directory' ]);

require_once('JSON.php');
$json = new Services_JSON();

function _isAuthorized() {
	global $xmlrpc_require_login;
	if($xmlrpc_require_login===false)
		return true;
	if($xmlrpc_require_login===true && array_key_exists('utente',$_SESSION) && $_SESSION['utente']!==null && $_SESSION['utente']->getTypeName()>'')
		return true;
	return false;
}

function _dbeToJson(&$dbe) {
	// 2022.03.18: start.
	if($dbe===null) return array();
	// 2022.03.18: end.
	$tmpArray = array();
  $tmpArray["_typeName"] = $dbe->getTypeName();
  $tmpArray["_typename"] = $dbe->getTypeName();
  $tmpArray["_tablename"] = $dbe->getTableName();
  $tmpArray["_keys"] = $dbe->getKeys();
  $tmpArray["_fk"] = $dbe->getFK();
  $dict = $dbe->getValuesDictionary();
  $chiavi = array_keys($dict);
  foreach(array_keys($dict) as $k ) {
	$v = $dict[$k];
	if ($v===null) continue;
	if(is_string($v) && $v!='0000-00-00 00:00:00' ) {
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
	foreach($struttura as $k=>$obj ) {
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
	// 2022.04.05: start.
	// 2022.04.05: closed for DBEUser, DBEGroup and DBELog
	if(!in_array($dbe->getTypeName(),["DBEUser","DBEGroup","DBELog"]) || _isAuthorized()) {
//	if(true || _isAuthorized()) {
	// 2022.04.05: end.
//	if(_isAuthorized()) {
	// 2011.04.27: end.
		$dbmgr->setVerbose(true);
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

function select($tablename, $searchString) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    // ob_start();
    $dbmgr->setVerbose(false);
    $listadbe = array();
    if(_isAuthorized()) {
        $myfactory = $dbmgr->getFactory();
        $classname = get_class($myfactory->getInstanceByTableName($tablename));
        $listadbe = $dbmgr->select($classname, $tablename, $searchString);
    } else {
        echo "jsonserver.select: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    // $messaggi = ob_get_contents();
    // ob_end_clean();
    
    $retArray=array();
    foreach($listadbe as $mydbe ) {
        // $retArray[]=$mydbe->getValuesDictionary();
        // $retArray[]=_arrayToXmlrpc($mydbe->getValuesDictionary());
        $retArray[]=_dbeToJson($mydbe);
    }
	return $retArray;
}

function selectAsArray($tablename, $searchString) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    // ob_start();
    $dbmgr->setVerbose(false);
    $listadbe = array();
    if(_isAuthorized()) {
        $myfactory = $dbmgr->getFactory();
        $classname = get_class($myfactory->getInstanceByTableName($tablename));
        $listadbe = $dbmgr->select($classname, $tablename, $searchString);
    } else {
        echo "jsonserver.selectAsArray: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    // $messaggi = ob_get_contents();
    // ob_end_clean();
    
    $retArray=array();
    foreach($listadbe as $mydbe ) {
        $retArray[]=$mydbe->getValuesDictionary();
    }
	return $retArray;
}

/**
 * Normal DBEs are not publicly searchable, only DBOs with the right permissions
 */
function searchDBEById($myid,$ignore_deleted) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$dbe = null;
	if(_isAuthorized()) {
		$dbe = $dbmgr->searchDBEById($myid,$ignore_deleted);
		if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	} else {
		echo "json_server.searchDBEById: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);
	
	return $dbe==null ? array() : array(_dbeToJson($dbe));
}
/**
 * See searchDBEById: authorization required here!!!!
 */
function fullDBEById($myid,$ignore_deleted) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$dbe = null;
	if(_isAuthorized()) {
		$dbe = $dbmgr->fullDBEById($myid,$ignore_deleted);
		if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	} else {
		echo "json_server.fullDBEById: Authentication required!\n";
	}
	$dbmgr->setVerbose(false);
	
	return $dbe==null ? array() : array(_dbeToJson($dbe));
}

// ************* ObjectMgr: start.
function objectById($myid,$ignore_deleted) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$dbe = null;
	// if(_isAuthorized()) {
		$dbe = $dbmgr->objectById($myid,$ignore_deleted);
		if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	// } else {
	// 	echo "json_server.objectById: Authentication required!\n";
	// }
	$dbmgr->setVerbose(false);
	
	return $dbe==null ? array() : array(_dbeToJson($dbe));
}
function fullObjectById($myid,$ignore_deleted) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$dbe = null;
	// if(_isAuthorized()) {
		$dbe = $dbmgr->fullObjectById($myid,$ignore_deleted);
		if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	// } else {
	// 	echo "json_server.fullObjectById: Authentication required!\n";
	// }
	$dbmgr->setVerbose(false);

	return $dbe==null ? array() : array(_dbeToJson($dbe));
}
function searchByName($name,$uselike=false,$tablenames=null,$ignore_deleted=true) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$retArray = array();
	// if(_isAuthorized()) {
		$tmp = $dbmgr->searchByName($name,$uselike,$tablenames,$ignore_deleted);
		foreach($tmp as $_dbe) {
			$_dbe->setValue('_typename',get_class($_dbe));
			$retArray[]=_dbeToJson($_dbe);
		}
	// } else {
	// 	echo "json_server.objectByName: Authentication required!\n";
	// }
	$dbmgr->setVerbose(false);
	
	return $retArray;
}
function objectByName($myid,$ignore_deleted) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$retArray = array();
	// if(_isAuthorized()) {
		$tmp = $dbmgr->objectByName($myid,$ignore_deleted);
		foreach($tmp as $_dbe) {
			$_dbe->setValue('_typename',get_class($_dbe));
			$retArray[]=_dbeToJson($_dbe);
		}
	// } else {
	// 	echo "json_server.objectByName: Authentication required!\n";
	// }
	$dbmgr->setVerbose(false);
	
	return $retArray;
}
function fullObjectByName($myid,$ignore_deleted) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$retArray = array();
	// if(_isAuthorized()) {
		$tmp = $dbmgr->fullObjectByName($myid,$ignore_deleted);
		foreach($tmp as $_dbe) {
			$_dbe->setValue('_typename',get_class($_dbe));
			$retArray[]=_dbeToJson($_dbe);
		}
	// } else {
	// 	echo "json_server.fullObjectByName: Authentication required!\n";
	// }
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
	$user_groups = $dbmgr->getUserGroupsList();
	return array( _dbeToJson($__utente), $user_groups );
}
function getLoggedUser() {
	global $dbmgr;
	$dbmgr->setVerbose(false);
	$__utente=$dbmgr->getDBEUser();
	$user_groups = $dbmgr->getUserGroupsList();
	// Cleaning the pwd field :-) not good to show it back
	if($__utente!==null) $__utente->setValue('pwd',null);
	$dbmgr->setVerbose(false);
	return $__utente!==null ? array( _dbeToJson($__utente), $user_groups ) : array();
}
function logout() {
	global $dbmgr;
	$dbmgr->logout();
	$__utente = $dbmgr->getDBEUser();
	$_SESSION['utente'] = $__utente;
	return array( _dbeToJson($__utente) );
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

function getDBEInstance($aclassname) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$dbe = $dbmgr->getInstance($aclassname);
	if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	$dbmgr->setVerbose(false);

	return $dbe==null ? array() : array(_dbeToJson($dbe));
}
function getNewDBEInstance($aclassname, $father_id) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);


	$dbe = $dbmgr->getInstance($aclassname);
	$dbe->setValue('fk_obj_id', $father_id);
	// $mydbe->setValuesDictionary( $myform->getValues() );
	if(is_a($dbe,'DBEObject'))
		$dbe->setDefaultValues($dbmgr);
	
	if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	$dbmgr->setVerbose(false);

	return $dbe==null ? array() : array(_dbeToJson($dbe));
}
function getDBEInstanceByTablename($aTablename) {
	global $dbmgr;
	
	$dbmgr->setVerbose(false);
	$dbe = $dbmgr->getInstanceByTableName($aTablename);
	if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
	$dbmgr->setVerbose(false);

	return $dbe==null ? array() : array(_dbeToJson($dbe));
}

function getDBE2FormMapping() {
	global $formulator;
	$ret = $formulator->getDBE2FormMapping();
	return $ret;
}
function getAllFormClassnames() {
	global $formulator;
	$ret = $formulator->getAllClassnames();
	return $ret;
}
function _ffield2dict($field) {
	$ret = array(
		'_classname' => get_class($field),
		'name' => $field->aNomeCampo,
		'title' => $field->_title,
		'description' => $field->_description,
		'size' => $field->_size,
		'value' => $field->aValore,
		'cssClass' => $field->aClasseCss,
		'isArray' => $field->isArray,
		/**
		 * Tipo di dato: s=stringa, n=numero, d=data
		 * Default=stringa
		 */
		'type' => $field->tipo,
		);
	if(get_class($field)==='FCheckBox') {
		$ret["valueslist"] = $field->listaValori;
		$ret["multiselect"] = $field->multiselezione;
		$ret["separator_string"] = $field->stringa_separatrice;
	}
	if(get_class($field)==='FDateTime' || get_class($field)==='FDateTimeReadOnly') {
		$ret["show_date"] = $field->aVisualizzaData;
		$ret["show_time"] = $field->aVisualizzaOra;
	}
	if(get_class($field)==='FFileField') {
		$ret["dest_directory"] = $field->dest_directory;
	}
	if(get_class($field)==='FList') {
		$ret["valueslist"] = $field->listaValori;
		$ret["height"] = $field->altezza;
		$ret["multiselect"] = $field->multiselezione;
	}
	if(get_class($field)==='FTextArea' || get_class($field)==='FHtml') {
		$ret["width"] = $field->width;
		$ret["height"] = $field->height;
		$ret["basicFormatting"] = $field->basicFormatting;
	}
	return $ret;
}
function _form2dictionary($form) {
	$fields = [];
	foreach($form->getFieldNames() as $fieldname) {
		$f = $form->getField($fieldname);
		$fields []= _ffield2dict($f);
	}
	$groups = [];
	foreach($form->getGroupNames() as $n) {
		$g = $form->getGroup($n);
		$_n = $n==='' ? '_' : $n;
		$groups[$_n] = $g; //_ffield2dict($f);
	}
	return array(
		'_classname' => get_class($form),
		'detailIcon' => $form->getDetailIcon(),
		'detailTitle' => $form->getDetailTitle(),
		'detailColumnNames' => $form->getDetailColumnNames(),
		'detailReadOnlyColumnNames' => $form->getDetailReadOnlyColumnNames(),

		'viewColumnNames' => $form->getViewColumnNames(),

		'filterForm' => $form->getFilterForm()!==null ? get_class($form->getFilterForm()) : null,
		'filterFields' => $form->getFilterFields(),
		'filterReadOnlyColumnNames' => $form->getFilterReadOnlyColumnNames(),

		'listTitle' => $form->getListTitle(),
		'listColumnNames' => $form->getListColumnNames(),
		'listEditableColumnNames' => $form->getListEditableColumnNames(),

		'decodeGroupNames' => $form->getDecodeGroupNames(),
		'pagePrefix' => $form->getPagePrefix(),
		'dbe' => get_class($form->getDBE()),
		'codice' => $form->getCodice(),
		'shortDescription' => $form->getShortDescription(),
		'actions' => $form->getActions(),
		'listActions' => $form->getListActions(),
		'action' => $form->getAction(),
		'method' => $form->getMethod(),
		'name' => $form->getName(),
		'enctype' => $form->getEnctype(),

		'fieldNames' => $form->getFieldNames(),
		'fields' => $fields,

		'groupNames' => $form->getGroupNames(),
		'groups' => $groups,

		'detailForms' => is_a($form,"FMasterDetail") ? $form->getDetailForms() : []
		// readValuesFromArray
	);
}
function getFormInstance($aClassname) {
	global $formulator;
	$form = $formulator->getInstance($aClassname);
	return _form2dictionary($form);
}
function getFormInstanceByDBEName($aClassname) {
	global $formulator;
	$ret = $formulator->getInstanceByDBEName($aClassname);
	return _form2dictionary($ret);
}

function getRootObj() {
	global $root_obj_id;
	// echo "root_obj_id: $root_obj_id\n";
	$ret = fullObjectById($root_obj_id,false);
	// echo "ret: ".json_encode($ret)."\n";
	return $ret;
}
function getChilds($current_obj,$without_index_page=true) {
	global $dbmgr;
	global $formulator;

	$my_obj_id = $current_obj->getValue('id');
	$form = $formulator->getInstanceByDBEName($current_obj->getTypeName());
	// Childs
	$search = new DBEObject();
	$search->setValue('father_id',$my_obj_id);
	$_menu_list_tmp = $dbmgr->search($search,$uselike=0);
	$_menu_list=array();
	$_menu_list_ids=array();
	foreach($_menu_list_tmp as $_item) {
		if($without_index_page && $_item->getValue('name')=='index') continue;
		if(in_array($_item->getValue('id'),$_menu_list_ids)) continue;
		$_menu_list_ids[]=$_item->getValue('id');
		$_menu_list[]=$_item;
	}
	// Linked Childs
	for($i=0; $i<$form->getDetailFormsCount(); $i++) {
		$childForm = $form->getDetail($i);
		$childDbe = $childForm->getDBE();
		$childDbe->readFKFrom($current_obj);
		$tmp = $dbmgr->search($childDbe,$uselike=0);
		foreach($tmp as $_linked_child) {
			if($without_index_page && $_linked_child->getValue('name')=='index') continue;
			if($_linked_child===null) continue;
			if(in_array($_linked_child->getValue('id'),$_menu_list_ids)) continue;
			$_menu_list_ids[]=$_linked_child->getValue('id');
			$_menu_list[]=$_linked_child;
		}
	}
	// Sorting folder items...
	$menu_list=array();
	$menu_list_ids=array();
	if($current_obj->getValue('childs_sort_order')>'') {
		$childs_sort_order=preg_split("/,/",$current_obj->getValue('childs_sort_order'));
		foreach($childs_sort_order as $_oid) {
			for($_i=0; $_i<count($_menu_list); $_i++) {
				if($_menu_list[$_i]->getValue('id')!=$_oid) continue;
				$menu_list[]=$_menu_list[$_i];
				$menu_list_ids[]=$_menu_list[$_i]->getValue('id');
				array_splice($_menu_list, $_i,1);
				break;
			}
		}
		foreach($_menu_list as $_item) if(!in_array($_item->getValue('id'),$menu_list_ids)) $menu_list[]=$_item;
	} else {
		$menu_list=$_menu_list;
	}
	
    $retArray=array();
    foreach($menu_list as $mydbe ) {
        $retArray[]=_dbeToJson($mydbe); //->getValuesDictionary();
    }
	return $retArray;
	// return $menu_list;
}

// **** DEBUG: start.
if(false) {
	$my_root = $dbmgr->fullObjectById($root_obj_id,false);
	echo "root:" . $my_root->to_string() . "\n";
	// echo "root:" . $json->encodeUnsafe($my_root) . "\n";
	$childs = getChilds($my_root);
	foreach($childs as $c) {
		echo "- " . $c->to_string() . "\n";
	}
}
// **** DEBUG: end.


$json_response = array();

$mymethod = $json_request->method=='echo' ? '_echo' : $json_request->method;
// echo "mymethod: $mymethod\n";

// $_tmp = $dbmgr->getDBEUser();
// if($_tmp!==null) {
// 	$_tmp->setValue('pwd','****');
// 	echo "json_server: current user=".($_tmp!==null ? $_tmp->to_string() : "--")."\n";
// }

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
	// echo "myeval: $myeval\n";
	eval($myeval);
	// echo "ret: $ret\n";
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