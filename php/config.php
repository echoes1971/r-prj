<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: config.php $
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
error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED & ~E_NOTICE);
/* Silence the "Different declarations" Warnings
 * See:
 *  https://stackoverflow.com/questions/36079651/silence-declaration-should-be-compatible-warnings-in-php-7
 */
if(PHP_MAJOR_VERSION >= 7) {
    set_error_handler(function ($errno, $errstr) {
       return strpos($errstr, 'Declaration of') === 0
        || strpos($errstr, 'mysqli_ping(): Couldn\'t fetch mysqli') === 0;
    }, E_WARNING);
}

/**
 *	File di configurazione.
 */
date_default_timezone_set('Europe/Rome');

require_once("utils.php");

$root_uri=getRootUri();
$root_directory=getRootFolder();
$files_directory="files";
// $files_directory=ROOT_FOLDER."files";
$files_downloader="$root_uri/download.php";
// $files_downloader="$root_uri/".(ROOT_FOLDER>''?ROOT_FOLDER:"")."download.php";

$GLOBALS['root_uri'] = $root_uri;
$GLOBALS['root_directory'] = $root_directory;
$GLOBALS['files_directory'] = $files_directory;
$GLOBALS['files_downloader'] = $files_downloader;
$GLOBALS['skin'] = 'default';
$GLOBALS['site_title'] = ':: R-Project ::';

//	DB
$db_connection_provider='MYConnectionProvider';
$GLOBALS['db_connection_provider'] = $db_connection_provider;
$db_server = "localhost";
$db_user = "root";
$db_pwd = "";
$db_db = "rproject";
$db_schema = "rprj";

// Languages
$lingue_sito = array('ita','eng','ted','esp');
$GLOBALS['lingue_sito'] = $lingue_sito;
$lingue_sito_decode = array('ita'=>'Italiano','eng'=>'Inglese','ted'=>'Tedesco','esp'=>'Spagnolo');
$GLOBALS['lingue_sito_decode'] = $lingue_sito_decode;

// Predefined Groups
$GROUP_ADMIN='-2';
$GROUP_USERS='-3';
$GROUP_GUESTS='-4';
$GROUP_PROJECT='-5';
$GROUP_WEBMASTER='-6';

// Xmlrpc Server
global $xmlrpc_require_login; $xmlrpc_require_login=true;

// Plugins
global $plugins_enabled;
$plugins_enabled=array();

// Portal
global $root_obj_id; $root_obj_id='-10';

$pecnam_host = $_SERVER["HTTP_HOST"];
// Bugfix: non funziona nell'area di test, es. epap.sicurezzapostale.it:8443
if(strcmp($pecnam_host,":")>0) {
  $tmp_pecnam_host = explode(":",$pecnam_host);
  $pecnam_host = $tmp_pecnam_host[0];
}
@include ROOT_FOLDER.'config_'.$pecnam_host.'.php';

@include(ROOT_FOLDER."config_local.php");
?>
