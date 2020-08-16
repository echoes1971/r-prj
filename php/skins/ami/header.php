<?php
/**
 * @copyright &copy; 2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
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
if(array_key_exists('messaggio',$_REQUEST)) {
 $messaggio = $_REQUEST['messaggio'];
 $popupMessaggio = new WTPopupDiv('header_message',"Message","$messaggio");
}
$popupIframe = new WTPopupIFrame('main_actions','New','','800px', '600px');

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"><head><title><?php
  echo $_SESSION['site_title'];
  if(isset($current_obj) && $current_obj!==null && $current_obj->getValue('name')>'' && $current_obj->getValue('name')!='Home') echo " ".$current_obj->getValue('name'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" /><?php
if($dbmgr->getDBEUser()!==null) {
} else {
 echo "<meta http-equiv=\"refresh\" content=\"120\" />";
}
?><style type="text/css">

</style>
<link href="<?php echo getSkinFile("bootstrap-4.3.1-dist/css/bootstrap.min.css"); ?>" rel="stylesheet" />
<link href="<?php echo getSkinFile("style.css"); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo getSkinFile("widgets.css"); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo getSkinFile("glyph.css"); ?>" rel="stylesheet" type="text/css" />
<?php
if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE ")>0 ) {
 echo "<link href=\"".getSkinFile("style_ie.css")."\" rel=\"stylesheet\" type=\"text/css\" />";
}
foreach($plugins_enabled as $plugin_name) {
 echo "<link rel=\"stylesheet\" href=\"".getPluginSkinFolder($plugin_name)."$plugin_name.css\" type=\"text/css\" />\n";
}
?>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

<script src="<?php echo getSkinFile("bootstrap-4.3.1-dist/js/bootstrap.min.js"); ?>"></script>
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
<body onload="javascript:<?php echo $popupIframe->getOnload().";"; if($messaggio>'') { echo $popupMessaggio->getOnload().";header_message_mostra();"; } ?>">
<?php
if ( $messaggio>'' ) {
 echo $popupMessaggio->render();
}
// popup iframe
echo $popupIframe->render();

echo "<div class=\"container-fluid\">"; // 20200129

do_hook('header_before');
echo "<div id=\"header\" class=\"row\">";
echo " <div class=\"col-12\">";
do_hook('divheader_before');

require_once(getSkinFile("_logo.php"));

echo " </div>";


do_hook('divheader_after');

echo "</div>";

echo "<div class=\"row\">";

if($dbmgr->isConnected()) {
//  echo "<div id=\"top_menu\" class=\"col-12\">";
 echo "<nav id=\"top_menu\" class=\"col-12 navbar navbar-expand-lg navbar-light\">";
 echo " <button class=\"navbar-toggler\" type=\"button\" data-toggle=\"collapse\" data-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">";
 echo "  <span class=\"navbar-toggler-icon\"></span>";
 echo " </button>";

 echo " <div class=\"collapse navbar-collapse\" id=\"navbarSupportedContent\">";
 echo "  <ul class=\"navbar-nav mr-auto\">";
 do_hook('topmenu_before');
 if(array_key_exists('root_obj',$_SESSION) && $_SESSION['root_obj']!==null) {
  echo " <li class=\"nav-item\"><a href=\"".ROOT_FOLDER."main.php?obj_id=".$_SESSION['root_obj']->getValue('id')."\">".$_SESSION['root_obj']->getValue('name')."</a></li>";
//   echo " <a class=\"navbar-brand\" href=\"".ROOT_FOLDER."main.php?obj_id=".$_SESSION['root_obj']->getValue('id')."\">".$_SESSION['root_obj']->getValue('name')."</a>";
 }
 if(array_key_exists('menu_top',$_SESSION) && is_array($_SESSION['menu_top'])) {
  foreach($_SESSION['menu_top'] as $menu_item) {
   if($menu_item->getTypeName()!='DBEFolder' && $menu_item->getTypeName()!='DBELink' && $menu_item->getTypeName()!='DBEPeople') continue;
   echo "<li class=\"nav-item\">";
   if($menu_item->getTypeName()=='DBELink') {
    $tmpform = new FLink(); $tmpform->setValues($menu_item->getValuesDictionary());
    echo " ".$tmpform->render_view()." "; //$dbmgr)." ";
   } else {
    echo " <a href=\"".ROOT_FOLDER."main.php?obj_id=".$menu_item->getValue('id')."\">".$menu_item->getValue('name')."</a> ";
   }
   echo "</li>";
  }
 }
 if($dbmgr->getDBEUser()!==null) {
  echo " <li class=\"nav-item\"><a href=\"".ROOT_FOLDER."mng/gestione.php\">Manage</a></li>";
 }
 echo "<li class=\"nav-item\">";
 do_hook('topmenu_after');
 echo "</li>";
 echo "  </ul>";
 echo " </div>";

 // echo " <div id=\"user_div\" class=\"col-2\">";
 echo "<div id=\"user_div\" class=\"dropdown\">";
 if($dbmgr->getDBEUser()!==null) {
//   echo "<font size=\"1\">&nbsp;<b>";
  echo "<b>User:&nbsp;";
  echo $dbmgr->getDBEUser()->getValue('fullname');
  echo "</b>";
  echo "<a href=\"".ROOT_FOLDER."logout_do.php\">Logout</a>";
//   echo "</b></font>&nbsp;";
 } elseif($dbmgr->isConnected()) {
  echo "<a href=\"".ROOT_FOLDER."mng/login.php".(isset($current_obj_id) ? "?obj_id=$current_obj_id" : '')."\">Login</a>";
 }
 echo " </div>";

//  echo " <div id=\"search_div\">";
 echo "<form name=\"\" action=\"".ROOT_FOLDER."main.php\" class=\"form-inline \">";
 echo "<input type=\"search\" aria-label=\"Search\" id=\"search_object\" name=\"search_object\" type=\"text\" />";
 echo "</form>";
//  echo " </div>";
//  echo "<hr class=\"amiga\" />";
 echo "</nav>";
//  echo "</div>";
}
echo "</div>";

do_hook('header_after');
?>
