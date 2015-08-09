<?php

if($_SESSION!==null) {
	$_SESSION['site_title'] = ' :: R-Project :: ';
}

// **** Main ****
//registerHook('header_before','skin_kware','skin_kware_body_before2');
// registerHook('header_after','skin_kware','skin_kware_info');
//registerHook('divheader_before','skin_kware','skin_kware_hook_test');
// registerHook('divheader_after','skin_kware','skin_kware_docs');
// registerHook('divleft_before','skin_kware','skin_kware_hook_test');
// registerHook('divleft_after','skin_kware','skin_kware_hook_test');
// registerHook('breadcrumb_after','skin_kware','skin_kware_hook_test');
// registerHook('divmiddle_before','skin_kware','skin_kware_adbrite');
// registerHook('divmiddle_after','skin_kware','skin_kware_hook_test');
// registerHook('divright_before','skin_kware','skin_kware_hook_test');
// registerHook('divright_after','skin_kware','skin_kware_hook_test');
// registerHook('footer_before','skin_kware','skin_kware_hook_test');
//registerHook('footer_after','skin_kware','skin_kware_body_after2');
// registerHook('footer_content_before','skin_kware','skin_kware_hook_test');
// registerHook('footer_content_after','skin_kware','skin_kware_hook_test');
// **** Mng ****
// registerHook('gestione_menu','skin_kware','skin_kware_menu');
// registerHook('mng_left_before','skin_kware','skin_kware_hook_test');
// registerHook('mng_left_after','skin_kware','skin_kware_hook_test');
// registerHook('mng_right_before','skin_kware','skin_kware_hook_test');
// registerHook('mng_right_after','skin_kware','skin_kware_hook_test');
// registerHook('dbe_list_actions','skin_kware','skin_kware_dbe_list_actions');
// registerHook('dbe_view_actions','skin_kware','skin_kware_hook_test');
// registerHook('dbe_new_actions','skin_kware','skin_kware_hook_test');
// registerHook('dbe_modify_actions','skin_kware','skin_kware_hook_test');

function skin_kware_hook_test($params) {
	echo "<b>Hook Test</b>";
}

?>
