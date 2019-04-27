<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: gestione_menu.php $
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
if($dbmgr==NULL || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);

$myuser = $dbmgr->getDBEUser();
$cerca=new DBEPeople();
$cerca->setValue('fk_users_id',$myuser->getValue('id'));
$lista = $dbmgr->search($cerca,$uselike=0);
$link_profilo='';
if(count($lista)==1)
	$link_profilo = "dbe_view.php?dbetype=DBEPeople&formtype=FPeople&field_id=".$lista[0]->getValue('id');

$link_preferences = "dbe_view.php?dbetype=DBEUser&formtype=FUser&field_id=".$myuser->getValue('id');


$mymenu = array(
);

// Webmaster
if($dbmgr->hasGroup($GROUP_WEBMASTER)) {
	$mymenu['Webmaster'] = array(
		'Home' => array( 'href'=>'dbe_view.php?dbetype=DBEFolder&formtype=FFolder'
									.'&filtra=filtra&field_id='.$_SESSION['root_obj']->getValue('id'),
									'target'=>'gestione_right',
								),
		'Folders' => array( 'href'=>'dbe_list.php?dbetype=DBEFolder&formtype=FFolder'
									.'&filtra=filtra&field_father_id='.$_SESSION['root_obj']->getValue('id'),
									'target'=>'gestione_right',
								),
		'Pages' => array( 'href'=>'dbe_list.php?dbetype=DBEPage&formtype=FPage'
									.'&filtra=filtra&field_father_id='.$_SESSION['root_obj']->getValue('id'),
									'target'=>'gestione_right',
								),
		'News' => array( 'href'=>'dbe_list.php?dbetype=DBENews&formtype=FNews'
									.'&filtra=filtra&field_father_id='.$_SESSION['root_obj']->getValue('id'),
									'target'=>'gestione_right',
								),
		'Files' => array( 'href'=>'dbe_list.php?dbetype=DBEFile&formtype=FFile'
									.'&filtra=filtra&field_father_id='.$_SESSION['root_obj']->getValue('id'),
									'target'=>'gestione_right',
								),
	);
}

// Projects
if($dbmgr->hasGroup($GROUP_PROJECT)) {
	$mymenu['Projects'] = array(
		'Projects' => array( 'href'=>'dbe_list.php?dbetype=DBEProject&formtype=FProject',
									'target'=>'gestione_right',
								),
		'Todos' => array( 'href'=>'dbe_list.php?dbetype=DBETodo&formtype=FTodo&filterformtype=FTodoFilter',
									'target'=>'gestione_right',
								),
		'Timetracks' => array( 'href'=>'dbe_list.php?dbetype=DBETimetrack&formtype=FTimetrack',
									'target'=>'gestione_right',
								),

/* 		'Folders' => array( 'href'=>'dbe_list.php?dbetype=DBEFolder&formtype=FFolder',
 									'target'=>'gestione_right',
 								),
		'Pages' => array( 'href'=>'dbe_list.php?dbetype=DBEPage&formtype=FPage',
									'target'=>'gestione_right',
								),*/
		'Notes' => array( 'href'=>'dbe_list.php?dbetype=DBENote&formtype=FNote',
									'target'=>'gestione_right',
								),
/*		'Files' => array( 'href'=>'dbe_list.php?dbetype=DBEFile&formtype=FFile',
									'target'=>'gestione_right',
								),*/
		// Events: TEST
		'Events' => array( 'href'=>'dbe_list.php?dbetype=DBEEvent&formtype=FEvent',
									'target'=>'gestione_right',
								),
	);
	
	if($dbmgr->hasGroup($GROUP_ADMIN)) {
		$mymenu['Projects::Admin'] = array(
			'Prj::Company::Roles' => array( 'href'=>'dbe_list.php?dbetype=DBEProjectCompanyRole&formtype=FProjectCompanyRole',
										'target'=>'gestione_right',
									),
			'Prj::People::Roles' => array( 'href'=>'dbe_list.php?dbetype=DBEProjectPeopleRole&formtype=FProjectPeopleRole',
										'target'=>'gestione_right',
									),
			'Prj::Prj::Roles' => array( 'href'=>'dbe_list.php?dbetype=DBEProjectProjectRole&formtype=FProjectProjectRole',
										'target'=>'gestione_right',
									),
			'Todo::Kind' => array( 'href'=>'dbe_list.php?dbetype=DBETodoTipo&formtype=FTodoTipo',
										'target'=>'gestione_right',
									),
		);
	}
}

// Plugins
do_hook('gestione_menu',array( 'mymenu'=>&$mymenu, 'dbmgr'=>&$dbmgr, ) );

// Contacts

$mymenu['Contacts'] = array(
		'Companies' => array( 'href'=>'dbe_list.php?dbetype=DBECompany&formtype=FCompany',
									'target'=>'gestione_right',
								),
		'People' => array( 'href'=>'dbe_list.php?dbetype=DBEPeople&formtype=FPeople',
									'target'=>'gestione_right',
								),
	);

$mymenu['User'] = array(
		'Profile' => array( 'href'=>$link_profilo, 'target'=>'gestione_right', ),
		'Preferences' => array( 'href'=>$link_preferences, 'target'=>'gestione_right', ),
		'Logout' => array( 'href'=>'logout_do.php', 'target'=>'_top', ),
	);

// Administration
if($dbmgr->hasGroup($GROUP_ADMIN)) {
	$mymenu['Admin'] = array(
		'DB' => array( 'href'=>'db_update.php',
									'target'=>'gestione_right',
									'title'=>'DB Management',
								),
		'Users' => array( 'href'=>'dbe_list.php?dbetype=DBEUser&formtype=FUser',
									'target'=>'gestione_right',
									'title'=>'Users Management',
								),
		'Groups' => array( 'href'=>'dbe_list.php?dbetype=DBEGroup&formtype=FGroup',
									'target'=>'gestione_right',
								),
		'Log' => array( 'href'=>'dbe_list.php?dbetype=DBELog&formtype=FLog',
									'target'=>'gestione_right',
								),
	);
}

// Plugins
do_hook('gestione_menu_admin',array( 'mymenu'=>&$mymenu, 'dbmgr'=>&$dbmgr, ) );


require_once( getSkinPage("mng/gestione_menu.php") );
?>