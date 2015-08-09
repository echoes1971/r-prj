<?php
/**
 * Email archive and management.
 */

/*
require_once(ROOT_FOLDER."db/dbschema.php");
require_once("dbschema.php");
require_once("formschema.php");
*/
// **** Main ****
// do_hook('main_logic_before');
// registerHook('main_logic_before','roundcube','roundcube_check_ban');
//registerHook('main_logic_after','roundcube','roundcube_hook_test');
// registerHook('header_before','roundcube','roundcube_hook_test');
// registerHook('header_after','roundcube','roundcube_hook_test');
// registerHook('divheader_before','roundcube','roundcube_adbrite_header');
// registerHook('topmenu_before','roundcube','roundcube_hook_test');
registerHook('topmenu_after','roundcube','roundcube_topmenu_after');
// registerHook('divheader_after','roundcube','roundcube_hook_test');
// registerHook('divleft_before','roundcube','roundcube_hook_test');
// registerHook('divleft_after','roundcube','roundcube_paypal');
// registerHook('breadcrumb_after','roundcube','roundcube_adfly');
// registerHook('divmiddle_before','roundcube','roundcube_facebook_like');
// registerHook('divmiddle_after','roundcube','roundcube_hook_test');
// registerHook('divright_before','roundcube','roundcube_hook_test');
// registerHook('divright_after','roundcube','roundcube_hook_test');
// registerHook('footer_before','roundcube','roundcube_hook_test');
// registerHook('footer_after','roundcube','roundcube_iframe');
// registerHook('footer_content_before','roundcube','roundcube_hook_test');
// registerHook('footer_content_after','roundcube','roundcube_hook_test');
// **** Mng ****
//registerHook('gestione_menu','roundcube','roundcube_menu');
// registerHook('mng_left_before','roundcube','roundcube_hook_test');
// registerHook('mng_left_after','roundcube','roundcube_hook_test');
// registerHook('mng_right_before','roundcube','roundcube_hook_test');
// registerHook('mng_right_after','roundcube','roundcube_hook_test');
// registerHook('dbe_list_actions','roundcube','roundcube_dbe_list_actions');
// registerHook('dbe_view_actions','roundcube','roundcube_hook_test');
// registerHook('dbe_new_actions','roundcube','roundcube_hook_test');
// registerHook('dbe_modify_actions','roundcube','roundcube_hook_test');



function roundcube_topmenu_after($params) {
  global $dbmgr;
  if($dbmgr->getDBEUser()!==null) {
    echo "<a href=\"".ROOT_FOLDER."plugins/roundcube/rc.php\">Webmail</a>";
//     echo " <a href=\"".ROOT_FOLDER."plugins/roundcube/rc.php\">Webmail</a> ::";
  }
}

?>
