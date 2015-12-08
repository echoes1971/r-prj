<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: skins.php $
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

// echo "skin: ".$_SESSION['skin']."<br/>\n";
if($_SESSION!==null) {
// 	echo "OK session :-)<br/>\n";
	if(!array_key_exists('skin',$_SESSION) || !($_SESSION['skin']>'')) $_SESSION['skin'] = $GLOBALS['skin'];
	if(array_key_exists('skin',$_REQUEST) && $_REQUEST['skin']>'') $_SESSION['skin'] = $_REQUEST['skin'];
	$_SESSION['site_title'] = $GLOBALS['site_title'];
} else {
// 	echo "no session :-(<br/>\n";
}

function __checkSkinInSession() {
	if(!array_key_exists('skin',$_SESSION) || !($_SESSION['skin']>'')) $_SESSION['skin'] = $GLOBALS['skin'];
	if(array_key_exists('skin',$_REQUEST) && $_REQUEST['skin']>'') $_SESSION['skin'] = $_REQUEST['skin'];
}
function getSkinFolder() {
	__checkSkinInSession();
	return ROOT_FOLDER . "skins/" . ($_SESSION!==null?$_SESSION['skin']:$GLOBALS['skin']) . "/";
}
/**
 * @param aPagePath page path relative to the skin's root
 */
function getSkinPage($aPagePath, $check_plugins_first=true) {
	__checkSkinInSession();
	if($check_plugins_first===true) {
		global $plugins_enabled;
		$_ret=null;
		foreach($plugins_enabled as $_plugin) {
			$_mypage = ROOT_FOLDER . "plugins/$_plugin/skins/".($_SESSION!==null&&array_key_exists('skin',$_SESSION)&&$_SESSION['skin']>''?$_SESSION['skin']:$GLOBALS['skin'])."/$aPagePath";
// 	echo "mypage: $mypage<br/>\n";
			if( file_exists($_mypage) ) $_ret = $_mypage;
			if($_ret!==null) break;
			$_mypage = ROOT_FOLDER . "plugins/$_plugin/skins/default/$aPagePath";
// 	echo "mypage2: $mypage<br/>\n";
			if( file_exists($_mypage) ) $_ret = $_mypage;
			if($_ret!==null) break;
		}
		if($_ret!==null) return $_ret;
	}
	$mypage = ROOT_FOLDER . "skins/".($_SESSION!==null&&array_key_exists('skin',$_SESSION)&&$_SESSION['skin']>''?$_SESSION['skin']:$GLOBALS['skin'])."/$aPagePath";
	if( file_exists($mypage) ) return $mypage;
	return ROOT_FOLDER . "skins/default/$aPagePath";
}
function getSkinFile($aFilePath, $check_plugins_first=true) { return getSkinPage($aFilePath,$check_plugins_first); }
function getPluginSkinFolder($plugin_name) {
	__checkSkinInSession();
	$ret = ROOT_FOLDER . "plugins/$plugin_name/skins/".($_SESSION!==null&&array_key_exists('skin',$_SESSION)&&$_SESSION['skin']>''?$_SESSION['skin']:$GLOBALS['skin'])."/";
	if( file_exists($ret) ) return $ret;
	return ROOT_FOLDER . "plugins/$plugin_name/skins/default/";
}
function getPluginSkinFile($plugin_name,$aFilePath, $return_null_if_not_found=false) {
	__checkSkinInSession();
	$mypage = ROOT_FOLDER . "plugins/$plugin_name/skins/".($_SESSION!==null&&array_key_exists('skin',$_SESSION)&&$_SESSION['skin']>''?$_SESSION['skin']:$GLOBALS['skin'])."/$aFilePath";
	if( file_exists($mypage) ) return $mypage;
	// 2011.06.27: start.
	if( $return_null_if_not_found ) {
		$mypage2 = ROOT_FOLDER . "plugins/$plugin_name/skins/default/$aFilePath";
		if( file_exists($mypage2) ) return $mypage2;
		return null;
	}
	// 2011.06.27: end.
	return ROOT_FOLDER . "plugins/$plugin_name/skins/default/$aFilePath";
}

@include(getSkinPage("setup.php"));
?>