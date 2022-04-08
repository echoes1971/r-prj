<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: header.php $
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

$messaggio = '';
$popupMessaggio=null;
if( array_key_exists('messaggio',$_REQUEST) ) {
	$messaggio = $_REQUEST['messaggio'];
	$popupMessaggio = new WTPopupDiv('header_message',"Message","$messaggio");
}
$popupIframe = new WTPopupIFrame('main_actions','New','','800px', '600px');

// if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE ")>0 ) {
// ?--><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
// <--?php
// }
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head><title><?php
  echo $_SESSION['site_title'];
  if(isset($current_obj) && $current_obj!==null && $current_obj->getValue('name')>'' && $current_obj->getValue('name')!='Home') echo " ".$current_obj->getValue('name'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" /><?php
if($dbmgr->getDBEUser()!==null) {
} else {
	?><meta http-equiv="refresh" content="120" /><?php
}
?><style type="text/css">

</style>
<link href="<?php echo getSkinFile("style.css"); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo getSkinFile("widgets.css"); ?>" rel="stylesheet" type="text/css" />
<?php
if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE ")>0 ) {
?><link href="<?php echo getSkinFile("style_ie.css"); ?>" rel="stylesheet" type="text/css" /><?php
}
foreach($plugins_enabled as $plugin_name) {
	echo "<link rel=\"stylesheet\" href=\"".getPluginSkinFolder($plugin_name)."$plugin_name.css\" type=\"text/css\" />\n";
}
?>
<link href="<?php echo getSkinFile("lcars.css"); ?>" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo ROOT_FOLDER; ?>js/3rdparties/prototype.js"></script>
<script type="text/javascript" src="<?php echo ROOT_FOLDER; ?>js/3rdparties/scriptaculous.js"></script>
<script type="text/javascript">
	function showHide(aDiv_name) {
		var aDiv = document.getElementById(aDiv_name);
		if(aDiv) {
			var shown = (aDiv.style.display != 'none');
			aDiv.style.display = shown ? 'none' : '';
			return true;
		} else
			return false;
	}
function reload() {
	document.location.href=document.location.href;
}
</script>
</head>
<body onload="javascript:<?php echo $popupIframe->getOnload().";"; if($messaggio>'') { echo $popupMessaggio->getOnload().";header_message_mostra();"; } ?>"><?php
if ( $messaggio>'' ) {
	echo $popupMessaggio->render();
}
// popup iframe
echo $popupIframe->render();
?><div class="lcars-app-container"><?php
do_hook('header_before');
echo "<div id=\"header\" class=\"lcars-row header\">";
?><div class="lcars-elbow left-bottom lcars-tan-bg"></div><?php
?><div class="lcars-bar horizontal"><?php
do_hook('divheader_before');

require_once(getSkinFile("_logo.php"));

if($dbmgr->isConnected()) {
 do_hook('topmenu_before');
 if(array_key_exists('root_obj',$_SESSION) && $_SESSION['root_obj']!==null) {
   echo "<div class=\"lcars-title horizontal lcars-tamarillo-bg\">";
   echo "<a class=\"lcars-white-color\" href=\"".ROOT_FOLDER."main.php?obj_id=".$_SESSION['root_obj']->getValue('id')."\">".$_SESSION['root_obj']->getValue('name')."</a>";
   echo "</div>";
 }
 if(array_key_exists('menu_top',$_SESSION) && is_array($_SESSION['menu_top'])) {
	foreach($_SESSION['menu_top'] as $menu_item) {
		if($menu_item->getTypeName()!='DBEFolder' && $menu_item->getTypeName()!='DBELink' && $menu_item->getTypeName()!='DBEPeople') continue;
		echo "<div class=\"lcars-title horizontal lcars-tamarillo-bg\">";
		if($menu_item->getTypeName()=='DBELink') {
			$tmpform = new FLink(); $tmpform->setValues($menu_item->getValuesDictionary());
			echo $tmpform->render_view($dbmgr);
		} else {
			echo "<a href=\"".ROOT_FOLDER."main.php?obj_id=".$menu_item->getValue('id')."\">".$menu_item->getValue('name')."</a>";
		}
		echo "</div>";
	}
 }
 if($dbmgr->getDBEUser()!==null) {
	echo "<div class=\"lcars-title horizontal lcars-tamarillo-bg\">";
	echo "<a href=\"".ROOT_FOLDER."mng/gestione.php\">Manage</a>";
	echo "</div>";
 }
 echo " ";
 do_hook('topmenu_after');
}
if($dbmgr->isConnected()) {
	echo "<div class=\"lcars-title right horizontal lcars-rust-bg\">";
  }
  if($dbmgr->getDBEUser()!==null) {
	  echo "<a href=\"".ROOT_FOLDER."logout_do.php\">Logout</a>";
	  echo "</div>";
	  echo "<div class=\"lcars-title right horizontal lcars-rust-bg\">";
	  echo $dbmgr->getDBEUser()->getValue('fullname');
  } elseif($dbmgr->isConnected()) {
	  echo "<a href=\"".ROOT_FOLDER."mng/login.php".( isset($current_obj_id) ? "?obj_id=$current_obj_id" : '' )."\">Login</a>";
  }
  if($dbmgr->isConnected()) {
   echo "</div>";
  }
  
  echo "<div class=\"lcars-title right horizontal lcars-rust-bg\">";
  echo "<form name=\"\" action=\"".ROOT_FOLDER."main.php\">";
  echo "<input id=\"search_object\" name=\"search_object\" type=\"text\" class=\"lcars-black-color\" placeholder=\"Search...\" />";
  echo "</form>";
  echo "</div>";
  
  
do_hook('divheader_after');
echo   "</div>";
?><div class="lcars-bar horizontal right-end decorated"></div><?php
echo "</div>";
do_hook('header_after');
?>