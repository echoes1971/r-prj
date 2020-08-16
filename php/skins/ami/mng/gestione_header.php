<?php
/**
 * @copyright &copy; 2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: gestione_header.php $
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
$popupMessaggio = null;
$my_session_message = getMessage();
if($my_session_message>'') {
	$popupMessaggio = new WTPopupDiv('header_message',"Message",$my_session_message);
} else if( array_key_exists('messaggio',$_REQUEST) && $_REQUEST['messaggio']>'' ) {
	$messaggio = $_REQUEST['messaggio'];
	$popupMessaggio = new WTPopupDiv('header_message',"Message","$messaggio");
}

//if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE ")>0 ) {
//?--><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
//<--?php
//}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><title>:: RProject</title>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<style type="text/css">body {
	MARGIN: 0px; BACKGROUND-COLOR: #ffffff
}
</style>
<link href="<?php echo getSkinFile("mng/mng.css"); ?>" rel="stylesheet" type="text/css">
<link href="<?php echo getSkinFile("widgets.css"); ?>" rel="stylesheet" type="text/css">
<?php
foreach($plugins_enabled as $plugin_name) {
	echo "<link rel=\"stylesheet\" href=\"".getPluginSkinFolder($plugin_name)."style.css\" type=\"text/css\" media=\"all\" />\n";
}
?>
<script type="text/javascript" src="<?php echo ROOT_FOLDER; ?>js/3rdparties/prototype.js"></script>
<script type="text/javascript" src="<?php echo ROOT_FOLDER; ?>js/3rdparties/scriptaculous.js"></script>
<script type="text/javascript" src="<?php echo ROOT_FOLDER; ?>js/3rdparties/xmlrpc.js"></script>
<script type="text/javascript" src="<?php echo ROOT_FOLDER; ?>js/dblayer.js"></script>
<script lang="Javascript">
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
<?php
if($popupMessaggio!==null) echo $popupMessaggio->getJS();
?>
</script>
</head>
<body onload="<?php if($messaggio>'' || $my_session_message>'') { echo $popupMessaggio->getOnload().";header_message_mostra();"; } ?>"><?php
if($popupMessaggio!==null) echo $popupMessaggio->render();
?>