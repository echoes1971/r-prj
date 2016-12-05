<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: main.php $
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

if(!defined("ROOT_FOLDER")) define("ROOT_FOLDER", "");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
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
$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
if ($formulator===null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

// -1. DB Connected?
$dbmgr->connect();
if(!$dbmgr->isConnected()) {
	$reloc = "Location: http".(array_key_exists('HTTPS',$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. (dirname($_SERVER['PHP_SELF'])=='/'?'':dirname($_SERVER['PHP_SELF']))
						. "/underconstruction.php";
	header($reloc);
	exit;
}

do_hook('main_logic_before');

// Log
rproject_mylog();

// 0.Parameters
$current_obj_id = array_key_exists('obj_id',$_REQUEST) ?
					$_REQUEST['obj_id'] : 
					( array_key_exists('field_id',$_REQUEST) ? $_REQUEST['field_id'] : $root_obj_id );
$current_obj_name = array_key_exists('name',$_REQUEST) ?
					$_REQUEST['name'] :
					( array_key_exists('title',$_REQUEST) ?
						str_replace('_',' ',$_REQUEST['title']) :
						'' );
$search_object = array_key_exists('search_object',$_REQUEST) ? $_REQUEST['search_object'] : ''; // 2012.07.23

// 0.1
$current_obj = $current_obj_name>'' ? $dbmgr->fullObjectByName($current_obj_name) : $dbmgr->fullObjectById($current_obj_id);
if($current_obj_name>'' && $current_obj!==null) $current_obj_id = $current_obj->getValue('id');
$root_obj = array_key_exists('root_obj', $_SESSION) ? $_SESSION['root_obj'] : null;
if ($root_obj===null || get_class($root_obj)=='__PHP_Incomplete_Class') {
	$root_obj = $dbmgr->fullObjectById($root_obj_id);
	// 2011.06.27: start.
	// 2011.06.27: if not found with a generic search, let's search a folder
	if($root_obj===null) {
		$_searchRoot=new DBEFolder();
		$_searchRoot->setValue('id',$root_obj_id);
		$_listRoot = $dbmgr->search($_searchRoot, $uselike=0);
		$root_obj = count($_listRoot)==1 ? $_listRoot[0] : null;
		$current_obj = $root_obj;
	}
	// 2011.06.27: end.
	$_SESSION['root_obj']=$root_obj;
}

// NON HO UN SITO CONFIGURATO
if($root_obj===null && !$dbmgr->hasGroup($GROUP_ADMIN)) {
	$reloc = "Location: http".(array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS']>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. (dirname($_SERVER['PHP_SELF'])=='/'?'':dirname($_SERVER['PHP_SELF']))
						. "/underconstruction.php";
	header($reloc);
	exit;
}
if($current_obj===null) {
// if($current_obj===null && !$dbmgr->hasGroup($GROUP_ADMIN)) {
	header("Location: http".(array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS']>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. (dirname($_SERVER['PHP_SELF'])=='/'?'':dirname($_SERVER['PHP_SELF']))
						. "/main.php?messaggio=Object not found");
	exit;
// }
}

// 2012.07.23: start.
// Search results
$searchresult_items = array();
if($search_object>'') {
	$searchresult_ids = array();
	$search = new DBEObject();
	$search->setValue('name',$search_object);
	$_searchresults_tmp = $dbmgr->search($search);
	foreach($_searchresults_tmp as $_searchresult_tmp) {
		$dbetype = $_searchresult_tmp->getValue('classname')>'' ? $_searchresult_tmp->getValue('classname') : $_searchresult_tmp->getTypeName();
		$formtype = $formulator->getFormNameByDBEName($dbetype);
		$myform = $formulator->getInstance($formtype,'Modify','dbe_modify_do.php'); // 2011.04.04 eval("\$myform = new $formtype('Modify','dbe_modify_do.php');");
		$myform->setValues($_searchresult_tmp->getValuesDictionary());
		$searchresult_items[]=$myform;
		$searchresult_ids[]=$_searchresult_tmp->getValue('id');
	}

	$search = new DBEObject();
	$search->setValue('description',$search_object);
	$_searchresults_tmp = $dbmgr->search($search);
	foreach($_searchresults_tmp as $_searchresult_tmp) {
		if(in_array($_searchresult_tmp->getValue('id'),$searchresult_ids)) continue;
		$dbetype = $_searchresult_tmp->getValue('classname')>'' ? $_searchresult_tmp->getValue('classname') : $_searchresult_tmp->getTypeName();
		$formtype = $formulator->getFormNameByDBEName($dbetype);
		$myform = $formulator->getInstance($formtype,'Modify','dbe_modify_do.php'); // 2011.04.04 eval("\$myform = new $formtype('Modify','dbe_modify_do.php');");
		$myform->setValues($_searchresult_tmp->getValuesDictionary());
		$searchresult_items[]=$myform;
		$searchresult_ids[]=$_searchresult_tmp->getValue('id');
	}
	// Only one result ==> redirect
	if(count($searchresult_items)==1) {
		header("Location: http".(array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS']>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. (dirname($_SERVER['PHP_SELF'])=='/'?'':dirname($_SERVER['PHP_SELF']))
						. "/main.php?obj_id=".$searchresult_items[0]->getValue('id'));
	exit;
// 		$current_obj = $searchresult_items[0];
	}
}
// 2012.07.23: start.


$dbetype = $current_obj!==null ? $current_obj->getTypeName() : "";
$formtype = $current_obj!==null ? $formulator->getFormNameByDBEName($dbetype) : "";

$current_form = null;
if($current_obj!==null) {
	$current_form = $formulator->getInstanceByDBEName( $current_obj->getTypeName() );
	$current_form->setValues($current_obj->getValuesDictionary());
}

// Link to User's Profile
$myuser = $dbmgr->getDBEUser();
$mypeople = null;
if($myuser!==null) {
	$cerca=new DBEPeople();
	$cerca->setValue('fk_users_id',$myuser->getValue('id'));
	$lista = $dbmgr->search($cerca,$uselike=0);
	if(count($lista)==1) {
		$mypeople = $lista[0];
	}
}


// 1. Parents list
$parent_list=array();
$my_current_obj = $current_obj;
while($current_obj!=null && $my_current_obj!=null && $my_current_obj->getValue('id')!=$root_obj_id) {
	$parent_list[]=$my_current_obj;
	$my_current_obj = $dbmgr->fullObjectById($my_current_obj->getValue('father_id'));
}
$parent_list[]=$root_obj;
$parent_list = array_reverse($parent_list);

function getChilds($my_obj_id,&$current_obj,&$current_form,$without_index_page=true) {
	if($current_form==null) return array();
	global $dbmgr;
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
	for($i=0; $i<$current_form->getDetailFormsCount(); $i++) {
		$childForm = $current_form->getDetail($i);
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
				if( $_menu_list[$_i]->getValue('id')!=$_oid ) continue;
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
	return $menu_list;
}

// Menu Top
$menu_top = array_key_exists('menu_top',$_SESSION) ? $_SESSION['menu_top'] : null;
if ($menu_top===null || is_array($menu_top)) {
        $__current__form = $formulator->getInstanceByDBEName("DBEFolder");
	$menu_top = getChilds($root_obj_id,$root_obj,$__current__form);//$current_obj->getTypeName() );)
	$_SESSION['menu_top']=$menu_top;
}
if($mypeople!==null) $menu_top[]=$mypeople;

// Menu List
$menu_list = getChilds($current_obj_id,$current_obj,$current_form,false);

// Index page
$index_page = null;
$index_page_form = null;
for($_c=0; $_c<count($menu_list); $_c++) {
	if($menu_list[$_c]->getTypeName()=='DBEPage' && $menu_list[$_c]->getValue('name')=='index') {
		$index_page = $menu_list[$_c];
		array_splice($menu_list,$_c,1);
		$index_page_form = $formulator->getInstanceByDBEName( $index_page->getTypeName() );
		$index_page_form->setValues($index_page->getValuesDictionary());
		break;
	}
}

// Menu Tree
$menu_tree = array();
$dbmgr->setVerbose(false);
foreach($parent_list as $_current_obj) {
	$_current_obj_id = $_current_obj->getValue('id');
	$_current_form = $formulator->getInstanceByDBEName( $_current_obj->getTypeName() );
	$menu_tree[$_current_obj_id]=getChilds($_current_obj_id,$_current_obj,$_current_form);
	if("$_current_obj_id"==$root_obj_id) {
		if($mypeople!==null) $menu_tree[$_current_obj_id][]=$mypeople;
	}
}
$dbmgr->setVerbose(false);

do_hook('main_logic_after');

require_once( getSkinPage("main.php") );
?>