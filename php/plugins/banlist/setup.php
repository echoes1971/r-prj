<?php
/**
 * Compatibilità con le funzionalità del vecchio sito
 *
 * Sposto qua dentro tutto il codice che non appartiene al progetto.
 */

require_once(ROOT_FOLDER."db/dbschema.php");
require_once("dbschema.php");
//require_once("formulator.php");
require_once("formschema.php");
//@include(ROOT_FOLDER."../include.php");

// **** Main ****
do_hook('main_logic_before');
registerHook('main_logic_before','banlist','banlist_check_ban');
//registerHook('main_logic_after','banlist','banlist_hook_test');
// registerHook('header_before','banlist','banlist_hook_test');
// registerHook('header_after','banlist','banlist_hook_test');
// registerHook('divheader_before','banlist','banlist_adbrite_header');
// registerHook('topmenu_before','banlist','banlist_hook_test');
// registerHook('topmenu_after','banlist','banlist_hook_test');
// registerHook('divheader_after','banlist','banlist_hook_test');
// registerHook('divleft_before','banlist','banlist_hook_test');
// registerHook('divleft_after','banlist','banlist_paypal');
// registerHook('breadcrumb_after','banlist','banlist_adfly');
// registerHook('divmiddle_before','banlist','banlist_facebook_like');
// registerHook('divmiddle_after','banlist','banlist_hook_test');
// registerHook('divright_before','banlist','banlist_hook_test');
// registerHook('divright_after','banlist','banlist_hook_test');
// registerHook('footer_before','banlist','banlist_hook_test');
// registerHook('footer_after','banlist','banlist_iframe');
// registerHook('footer_content_before','banlist','banlist_hook_test');
// registerHook('footer_content_after','banlist','banlist_hook_test');
// **** Mng ****
//registerHook('gestione_menu','banlist','banlist_menu');
registerHook('gestione_menu_admin','banlist','banlist_menu');
// registerHook('mng_left_before','banlist','banlist_hook_test');
// registerHook('mng_left_after','banlist','banlist_hook_test');
// registerHook('mng_right_before','banlist','banlist_hook_test');
// registerHook('mng_right_after','banlist','banlist_hook_test');
registerHook('dbe_list_actions','banlist','banlist_dbe_list_actions');
// registerHook('dbe_view_actions','banlist','banlist_hook_test');
// registerHook('dbe_new_actions','banlist','banlist_hook_test');
// registerHook('dbe_modify_actions','banlist','banlist_hook_test');


function banlist_check_ban() {
	require_once("checkBan.php");
	check_ban();
}


function banlist_menu($params) {
	global $GROUP_ADMIN;
/*	$params['mymenu']['RRA'] = array(
		'Menu 1' => array( 'href'=>ROOT_FOLDER.'plugins/test1/pagina1.php',
									'target'=>'gestione_right',
								),
	);*/
	if($params['dbmgr']->hasGroup($GROUP_ADMIN)) {
//		$params['mymenu']['Banlist']['List'] = array( 'href'=>'dbe_list.php?dbetype=DBEBanned&formtype=FBanned',
		$params['mymenu']['Admin']['Banlist'] = array( 'href'=>'dbe_list.php?dbetype=DBEBanned&formtype=FBanned',
									'target'=>'gestione_right',
								);
	}
}

function banlist_dbe_list_actions($params) {
	global $prefisso_pagine;
	
	if($params['dbetype']=='DBEBanned') {
	?>&nbsp;<input type="button" class="formtable" value="Import from phpBB" onclick="javascript:parent.main_actions_mostra_url('Import from phpBB','<?php echo ROOT_FOLDER; ?>plugins/banlist/importFromPhpBB.php');" /><?php
	?>&nbsp;<input type="button" class="formtable" value="Check your IP" onclick="javascript:parent.main_actions_mostra_url('Check your IP','<?php echo ROOT_FOLDER; ?>plugins/banlist/checkBan.php');" /><?php
	} else if($params['dbetype']=='DBELog') {
	?>&nbsp;<input type="button" class="formtable" value="Fill from Banlist" onclick="javascript:parent.main_actions_mostra_url('Fill from Banlist','<?php echo ROOT_FOLDER; ?>plugins/banlist/mylog_autofill.php?dbetype=<?php echo $params['dbetype'] ?>&formtype=<?php echo $params['formtype'] ?>');" /><?php
	}
// 	?xxx>&nbsp;<input type="button" class="formtable" value="Clean &amp; Optimize" onclick="javascript:parent.main_actions_mostra_url('Optimize','<?php echo ROOT_FOLDER; ?xxx>plugins/banlist/smlog_optimize.php?dbetype=<xxx?php echo $params['dbetype'] ?xxx>&formtype=<xxx?php echo $params['formtype'] ?xxx>');" /><xxx?php
}

?>
