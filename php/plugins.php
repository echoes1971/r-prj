<?php
/**
 * @copyright &copy; 2005-2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: plugins.php $
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

global $plugins_enabled;
global $rproject_plugin_hooks;

if(!defined($rproject_plugin_hooks)) $rproject_plugin_hooks=array();

/**
 * @par $hook_name nome dell'hook desiderato
 * @par $plugin_name nome del plugin che si sta registrando
 * @par $callback_name nome della callback da invocare
 * @return true se ok, false altrimenti
 */
function registerHook($hook_name,$plugin_name,$callback_name) {
	global $rproject_plugin_hooks;
	if(!array_key_exists($hook_name,$rproject_plugin_hooks)) $rproject_plugin_hooks[$hook_name]=array();
	$rproject_plugin_hooks[$hook_name][$plugin_name]=$callback_name;
	return true;
}
function do_hook($hook_name,$params=array()) {
	global $rproject_plugin_hooks;
	$ret=array();
	if(!array_key_exists($hook_name,$rproject_plugin_hooks)) return;
	foreach($rproject_plugin_hooks[$hook_name] as $plugin_name=>$callback_name) {
		$ret[$plugin_name]=$callback_name($params);
	}
	return $ret;
}

foreach($plugins_enabled as $plugin_name) {
	require_once("plugins/$plugin_name/setup.php");
}

?>