<?php
/**
 * Compatibilità con le funzionalità del vecchio sito
 *
 * Sposto qua dentro tutto il codice che non appartiene al progetto.
 */

// require_once("dbschema.php");
require_once("formulator.php");
require_once("formschema.php");

// **** Main ****
//registerHook('header_before','tinymce','tinymce_flattr');
//registerHook('header_after','tinymce','tinymce_hook_test');
//registerHook('divheader_before','tinymce','tinymce_adbrite_header');
// registerHook('topmenu_before','tinymce','tinymce_hook_test');
// registerHook('topmenu_after','tinymce','tinymce_flattr');
//registerHook('divheader_after','tinymce','tinymce_flattr');
// registerHook('divleft_before','tinymce','tinymce_hook_test');
// registerHook('divleft_after','tinymce','tinymce_paypal');
// registerHook('breadcrumb_after','tinymce','tinymce_adfly');

// registerHook('divmiddle_before','tinymce','tinymce_facebook_like');
// registerHook('divmiddle_after','tinymce','tinymce_flattr');

// registerHook('divmiddle_after','tinymce','tinymce_hook_test');
// registerHook('divright_before','tinymce','tinymce_hook_test');
// registerHook('divright_after','tinymce','tinymce_hook_test');
// registerHook('footer_before','tinymce','tinymce_hook_test');
// registerHook('footer_after','tinymce','tinymce_iframe');
// registerHook('footer_content_before','tinymce','tinymce_hook_test');
// registerHook('footer_content_after','tinymce','tinymce_hook_test');
// **** Mng ****
// registerHook('gestione_menu','tinymce','tinymce_menu');
// registerHook('mng_left_before','tinymce','tinymce_hook_test');
// registerHook('mng_left_after','tinymce','tinymce_hook_test');
// registerHook('mng_right_before','tinymce','tinymce_hook_test');
// registerHook('mng_right_after','tinymce','tinymce_hook_test');
// registerHook('dbe_list_actions','tinymce','tinymce_dbe_list_actions');
// registerHook('dbe_view_actions','tinymce','tinymce_hook_test');
// registerHook('dbe_new_actions','tinymce','tinymce_hook_test');
// registerHook('dbe_modify_actions','tinymce','tinymce_hook_test');

function tinymce_menu($params) {
	global $GROUP_ADMIN;
/*	$params['mymenu']['RRA'] = array(
		'Menu 1' => array( 'href'=>ROOT_FOLDER.'plugins/test1/pagina1.php',
									'target'=>'gestione_right',
								),
	);*/
}

function tinymce_hook_test($params) {
	echo "<b>Hook Test</b>";
}

?>