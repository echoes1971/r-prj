<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: sitemap.php $
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

header("Content-type: text/xml; charset=utf-8");

if(!defined("ROOT_FOLDER")) define("ROOT_FOLDER", "");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "plugins.php");
require_once(ROOT_FOLDER . "skins.php");
//require_once(ROOT_FOLDER . "utils.php");


$dbmgr = array_key_exists('dbmgr',$_SESSION) ? $_SESSION['dbmgr'] : null;
if ($dbmgr===null || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);
$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
if ($formulator===null || get_class($formulator)=='__PHP_Incomplete_Class') {
	$formulator = new MyFormFactory;
	$_SESSION['formulator'] = $formulator;
}

// -1. DB Connected?
$dbmgr->connect();
if(!$dbmgr->isConnected()) {
	$reloc = "Location: http".(array_key_exists('HTTPS',$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
						. (dirname($_SERVER['PHP_SELF'])=='/'?'':dirname($_SERVER['PHP_SELF']))
						. "/underconstruction.php";
	header($reloc);
	exit;
}

$search = new DBEFolder();
$search->setValue('id',$root_obj_id);
$lista = $dbmgr->search($search,$uselike=0);
$root_obj = $lista[0];

function getChilds($myid) {
	global $dbmgr;
	$search = new DBEObject();
	$search->setValue('father_id',$myid);
	$ret = $dbmgr->search($search,$uselike=0);
	return $ret;
}

$childs = getChilds($root_obj->getValue('id'));

$_dirname_self = dirname($_SERVER['PHP_SELF']);

$_base_url = "http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')
                      ."://" . $_SERVER['HTTP_HOST']
                      . ($_dirname_self=='/'?'':$_dirname_self);

echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
// Root object
$myobj = $root_obj;

echo "<url>\n";
echo "<loc>$_base_url</loc>\n";
$mydate = $myobj->getValue('last_modify_date')>'' ? $myobj->getValue('last_modify_date') : $myobj->getValue('creation_date');
$mydate = preg_split( "/ /", $mydate );
echo "<lastmod>".( $mydate[0] )."</lastmod>\n";
echo "<changefreq>daily</changefreq>\n";
echo "</url>\n";
$nipoti = array();
$contatore = 0;
foreach($childs as $myobj) {
	echo "<url>\n";
//        echo "<!-- ".($contatore++).": ".$myobj->getValue('name')." -->\n";
	echo "<loc>$_base_url/main.php?obj_id=".$myobj->getValue('id')."</loc>\n";
	$mydate = $myobj->getValue('last_modify_date')>'' ? $myobj->getValue('last_modify_date') : $myobj->getValue('creation_date');
	$mydate = preg_split( "/ /", $mydate );
	echo "<lastmod>".( $mydate[0] )."</lastmod>\n";
	echo "<changefreq>daily</changefreq>\n";
	echo "</url>\n";
	$tmp = getChilds($myobj->getValue('id'));
	foreach($tmp as $_tmp) $nipoti[]=$_tmp;
}
foreach($nipoti as $myobj) {
        echo "<url>\n";
//	echo "<!-- ".($contatore++).": ".$myobj->getValue('name')." -->\n";
        echo "<loc>$_base_url/main.php?obj_id=".$myobj->getValue('id')."</loc>\n";
        $mydate = $myobj->getValue('last_modify_date')>'' ? $myobj->getValue('last_modify_date') : $myobj->getValue('creation_date');
        $mydate = preg_split( "/ /", $mydate );
        echo "<lastmod>".( $mydate[0] )."</lastmod>\n";
        echo "<changefreq>daily</changefreq>\n";
        echo "</url>\n";
}

echo "</urlset>\n";
?>
