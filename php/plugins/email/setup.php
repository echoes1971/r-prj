<?php
/**
 * Email archive and management.
 */

require_once(ROOT_FOLDER."db/dbschema.php");
require_once("dbschema.php");
require_once("formschema.php");

// **** Main ****
// do_hook('main_logic_before');
// registerHook('main_logic_before','email','email_check_ban');
//registerHook('main_logic_after','email','email_hook_test');
// registerHook('header_before','email','email_hook_test');
// registerHook('header_after','email','email_hook_test');
// registerHook('divheader_before','email','email_adbrite_header');
// registerHook('topmenu_before','email','email_hook_test');
// registerHook('topmenu_after','email','email_hook_test');
// registerHook('divheader_after','email','email_hook_test');
// registerHook('divleft_before','email','email_hook_test');
// registerHook('divleft_after','email','email_paypal');
// registerHook('breadcrumb_after','email','email_adfly');
// registerHook('divmiddle_before','email','email_facebook_like');
// registerHook('divmiddle_after','email','email_hook_test');
// registerHook('divright_before','email','email_hook_test');
// registerHook('divright_after','email','email_hook_test');
// registerHook('footer_before','email','email_hook_test');
// registerHook('footer_after','email','email_iframe');
// registerHook('footer_content_before','email','email_hook_test');
// registerHook('footer_content_after','email','email_hook_test');
// **** Mng ****
registerHook('gestione_menu','email','email_menu');
// registerHook('mng_left_before','email','email_hook_test');
// registerHook('mng_left_after','email','email_hook_test');
// registerHook('mng_right_before','email','email_hook_test');
// registerHook('mng_right_after','email','email_hook_test');
// registerHook('dbe_list_actions','email','email_dbe_list_actions');
// registerHook('dbe_view_actions','email','email_hook_test');
// registerHook('dbe_new_actions','email','email_hook_test');
// registerHook('dbe_modify_actions','email','email_hook_test');


// function email_check_ban() {
// 	require_once("checkBan.php");
// 	check_ban();
// }


function email_menu($params) {
	global $GROUP_ADMIN;
	$params['mymenu']['Email'] = array(
		'Archive' => array( 'href'=>'dbe_list.php?dbetype=DBEMail&formtype=FMail',
									'target'=>'gestione_right',
								),
/*		'List' => array( 'href'=>ROOT_FOLDER.'plugins/email/pagina1.php',
									'target'=>'gestione_right',
								),*/
	);
/*	if($params['dbmgr']->hasGroup($GROUP_ADMIN)) {
		$params['mymenu']['Email']['List'] = array( 'href'=>'dbe_list.php?dbetype=DBEBanned&formtype=FBanned',
									'target'=>'gestione_right',
								);
	}*/
}

/*
function email_dbe_list_actions($params) {
	global $prefisso_pagine;
	if($params['dbetype']!='DBEBanned') return;
	
	?>&nbsp;<input type="button" class="formtable" value="Import from phpBB" onclick="javascript:parent.main_actions_mostra_url('Import from phpBB','<?php echo ROOT_FOLDER; ?>plugins/banlist/importFromPhpBB.php');" /><?php
	?>&nbsp;<input type="button" class="formtable" value="Check your IP" onclick="javascript:parent.main_actions_mostra_url('Check your IP','<?php echo ROOT_FOLDER; ?>plugins/banlist/checkBan.php');" /><?php
}*/

?>
