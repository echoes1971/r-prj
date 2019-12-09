<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: checkUser.php $
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

if(!defined("ROOT_FOLDER")) define("ROOT_FOLDER",     "../");

$redir_page="mng/login.php";

$utente = array_key_exists('utente',$_SESSION) ? $_SESSION['utente'] : null;

if($utente==null) {
	$nuovo_url="http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. dirname($_SERVER['PHP_SELF'])
						. "/". ROOT_FOLDER
						. $redir_page;
	
	// Check DB Version before redirecting
	$dbmgr = array_key_exists('dbmgr',$_SESSION) ? $_SESSION['dbmgr'] : null;
// 	echo "dbmgr: ".($dbmgr==null ? "null" : $dbmgr->db_version())."<br/>\n";
    // IF the version is zero ==> REDIRECT to the db_update page
    if($dbmgr!==null && $dbmgr->db_version()===0) {
        $nuovo_url="http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. dirname($_SERVER['PHP_SELF'])
						. "/". ROOT_FOLDER
						. "mng/db_update.php";
						
//         echo $_SERVER['PHP_SELF'] . "<br/>\n";
    }

    if(strpos($_SERVER['PHP_SELF'], "mng/db_update.php")===false && strpos($_SERVER['PHP_SELF'], "mng/db_update_do.php")===false) {
        echo "<script>window.top.location='$nuovo_url';</script>";
    }
}
?>
