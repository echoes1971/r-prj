<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: obj_reload_do.php $
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
define("ROOT_FOLDER",     "../");
require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "utils.php");
require_once(ROOT_FOLDER . "db/dblayer.php");
require_once(ROOT_FOLDER . "db/dbschema.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");
require_once(ROOT_FOLDER . "formulator/formschema.php");
session_start();
require_once(ROOT_FOLDER . "plugins.php");
require_once(ROOT_FOLDER . "utils.php");

$redir_page = ROOT_FOLDER."mng/dbe_list.php";

$dbmgr = $_SESSION['dbmgr'];
if ($dbmgr==NULL || get_class($dbmgr)=='__PHP_Incomplete_Class') {
	$aFactory = new MyDBEFactory;
	$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	$_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);



// 0. Lettura parametri
$dbetype = $_REQUEST['dbetype'];
$formtype = $_REQUEST['formtype'];

eval("\$dbe = new $dbetype();");
// $dbe = new DBEMyLog();
$tablename = $dbmgr->buildTableName($dbe);

$queries = array(
 			"update $tablename set url='Amazon' where (ip like '67.202.29.%' or ip like '174.129.%' or ip like '184.72.%' or ip like '184.73.%') and url is null",
			"update $tablename set url='Amazon' where ((ip>='107.20.' and ip<'107.24.') or (ip>='204.236.128.' and ip<'204.237.') or (ip>='23.20.' and ip<'23.24.')) and url is null",
 			"update $tablename set url='Amazon' where ((ip>='50.16.' and ip<'50.20.') or (ip>='50.112.' and ip<'50.113.') or (ip>='67.202.' and ip<'67.203.')) and url is null",
 			"update $tablename set url='Brazil' where (ip like '201.13.%' or ip like '201.52.%' or ip like '201.53.%' or ip like '201.92.%' or ip like '174.129.%') and url is null",
 			"update $tablename set url='Canada Scarborough' where (ip like '70.27.%' or ip like '76.70.%') and url is null",
 			"update $tablename set url='China' where ( ip like '114.%' or ip like '121.70.%' or ip like '124.114.%' or ip like '124.115.%' ) and url is null",
 			"update $tablename set url='China' where ip>='202.108.' and ip<'202.109.' and url is null",
 			"update $tablename set url='China - crawl.baidu.com' where (ip like '123.112.%' or ip like '123.113.%' or ip like '123.114.%' or ip like '123.115.%' or ip like '123.116.%' or ip like '123.117.%' or ip like '123.118.%' or ip like '123.119.%' or ip like '123.120.%' or ip like '123.121.%' or ip like '123.122.%' or ip like '123.123.%' or ip like '123.124.%' or ip like '123.125.%' or ip like '123.126.%' or ip like '220.181.%') ",//and url is null",
            "update $tablename set url='China - crawl.baidu.com' where (ip like '180.76.%') ",//and url is null",
 			"update $tablename set url='facebook.com' where ( (ip>='66.220.144.' and ip <'66.220.160.') or (ip>='69.171.224.' and ip<'69.171.256.') or (ip>='69.63.176.' and ip<'69.63.192.') ) and url is null",
 			"update $tablename set url='Germany Berlin' where (ip like '91.64.78.%' or ip like '84.191.%' or ip like '87.176.%' or ip like '87.185.%') and url is null",
 			"update $tablename set url='Germany Stuttgart' where ip like '91.45.%' and url is null",
 			"update $tablename set url='Germany' where ( (ip>='80.128.' and ip<'80.146.') or (ip>='85.176.' and ip<'85.182.') or (ip>='91.32.' and ip<'91.64.') ) and url is null",
 			"update $tablename set url='Google' where ip like '74.125.%' and url is null",
 			"update $tablename set url='googlebot.com' where ip like '66.249.%' ",//and url is null",
 			"update $tablename set url='India Bangalore Software Technology Parks Of India' where ip>='203.193.128.' and ip<'203.193.192.' and url is null",
 			"update $tablename set url='India' where (ip like '115.%' or ip like '219.64.%' or ip like '219.65.%') and url is null",
 			"update $tablename set url='India New Delhi Bharti Airtel Ltd. Telemedia Services' where ip like '122.181.%' and url is null",
 			"update $tablename set url='Italy' where ip>='151.38.' and ip<'151.39.' and url is null",
 			"update $tablename set url='Italy' where ip>='151.46.' and ip<'151.47.' and url is null",
 			"update $tablename set url='Italy' where ip>='151.56.' and ip<'151.57.' and url is null",
 			"update $tablename set url='Italy' where ip like '151.80.%' and url is null",
 			"update $tablename set url='Italy' where ip>='212.171.48.' and ip<'212.171.50.' and url is null",
 			"update $tablename set url='Italy Rome' where (ip like '217.201.145.%' or ip like '217.201.149.%') and url is null",
 			"update $tablename set url='Italy Milan' where ip like '217.202.%' and url is null",
 			"update $tablename set url='Italy Trieste' where ip like '217.203.%' and url is null",
 			"update $tablename set url='Italy' where ip like '79.%' and url is null",
 			"update $tablename set url='Italy' where ip>='82.52.' and ip<'82.56.' and url is null",
 			"update $tablename set url='Italy' where ip like '87.6.%' and url is null",
 			"update $tablename set url='Italy Fastweb' where ip like '93.%' and url is null",
 			"update $tablename set url='Italy TIM' where ip like '95.74.%' and url is null",
 			"update $tablename set url='Japan Tokyo Baidu Inc' where ip like '119.%' and url is null",
 			"update $tablename set url='Japan Tokyo Open Computer Network' where ip like '222.145.%' and url is null",
 			"update $tablename set url='Kenya Nairobi' where ip like '41.204.187.%' and url is null",
 			"update $tablename set url='Korea, Republic Of Seoul Powercomm' where ip like '122.32.%' and url is null",
 			"update $tablename set url='Korea' where (ip>='121.128.' and ip<'121.192.') or (ip>='210.93.' and ip<'210.96.') or (ip>='61.96.' and ip<'61.112.') and url is null",
 			"update $tablename set url='Latvia Ad Technology Datacenter' where ip like '188.%' and url is null",
 			"update $tablename set url='Malaysia Kuching' where (ip like '124.13.%' or ip like '60.54.%') and url is null",
 			"update $tablename set url='Mexico Uninet S.a. De C.v ' where ip like '201.3.%' and url is null",
 			"update $tablename set url='msnbot.search.msn.com' where (ip like '65.52.%' or ip like '65.53.%' or ip like '65.54.%' or ip like '65.55.%' or ip like '207.46.%') ",//and url is null",
 			"update $tablename set url='msnbot.search.msn.com' where (ip>='157.54.' and ip<'157.61.') or (ip>='131.253.21.' and ip<'131.253.48.') ",//and url is null",
 			"update $tablename set url='Philippines Manila' where ip>='121.96.212.' and ip<'121.96.216.' and url is null",
 			"update $tablename set url='Poland' where (ip like '77.112.%' or ip like '77.113.%' or ip like '77.114.%' or ip like '77.115.%') and url is null",
 			"update $tablename set url='Poland Warsaw' where ip like '83.31.%' and url is null",
 			"update $tablename set url='Poland' where ip>='83.4.' and ip<'83.9.' and url is null",
 			"update $tablename set url='Poland' where ip>='83.10.' and ip<'83.12.' and url is null",
 			"update $tablename set url='Serbia' where (ip like '109.92.%' or ip like '109.93.%') and url is null",
 			"update $tablename set url='Singapore' where ip like '218.186.%' and url is null",
 			"update $tablename set url='Singapore Queenstown Singnet Pte Ltd' where ip like '220.255.%' and url is null",
 			"update $tablename set url='USA Dallas Softlayer Technologies Inc' where (ip like '173.192.%' or ip like '173.193.%') and url is null",
 			"update $tablename set url='South Africa Johannesburg' where ip like '198.54.%' and url is null",
 			"update $tablename set url='South Africa Pretoria' where ip like '196.25.255.%' and url is null",
 			"update $tablename set url='South Africa' where ip like '196.211.%' and url is null",
 			"update $tablename set url='South Africa' where (ip>='41.160.' and ip<'41.176.') and url is null",
 			"update $tablename set url='Spain Madrid' where ip like '84.77.%' and url is null",
 			"update $tablename set url='Sweden Gothenburg' where ip like '83.227.2.%' and url is null",
 			"update $tablename set url='Sweden Stockholm Orc Software Ab' where ip like '194.14.211.%' and url is null",
 			"update $tablename set url='Sweden Vasteras' where (ip like '85.231.180.%' or ip like '85.231.181.%') and url is null",
 			"update $tablename set url='Switzerland Zurich' where (ip like '84.75.%' or ip like '85.0.%' or ip like '85.1.%') and url is null",
 			"update $tablename set url='Turkey' where ip like '78.%' and url is null",
 			"update $tablename set url='UK Dragonara Alliance Ltd' where (ip like '194.8.74.%' or ip like '194.8.75.%') and url is null",
 			"update $tablename set url='USA Kent Microsoft Corp' where ip like '131.107.%' and url is null",
 			"update $tablename set url='USA Dallas Theplanet.com' where (ip>='184.172.' and ip<'184.174.') and url is null",
 			"update $tablename set url='websense' where (ip like '208.80.194.%' or ip like '208.80.195.%') and url is null",
 			"update $tablename set url='www.ask.com' where ip like '66.235.124%' and url is null",
 			"update $tablename set url='www.han.nl - Netherlands Arnhem Hogeschool Van Arnhem En Nijmegen' where ip like '145.74.%' and url is null",
 			"update $tablename set url='www.whois.sc - USA Seattle Compass Communications Inc' where ip like '216.145.11.%' and url is null",
 			"update $tablename set url='yahoo' where (ip like '67.195.%' or ip like '72.30.%' or ip like '74.6.%' or ip like '76.13.%') ",//and url is null",
            "update $tablename set url='yahoo' where (ip>='68.180.128.' and ip<'68.180.256.') and url is null",
            "update $tablename set url='yandex' where (ip like '141.8.132.%' or ip like '199.21.96.%' or ip like '199.21.97.%' or ip like '199.21.98.%' or ip like '199.21.99.%') ",//and url is null",
// 			"update $tablename set note2='' where `count`>=100",
// 			"optimize table $tablename",
			);


// echo "Auto complete...\n";
// echo "================\n";
// echo "\n";
// echo "\n";
// echo "Table name: $tablename\n";
// echo "\n";

$errors=array();
foreach($queries as $q) {
// 	echo "$q ... ";
	$dbmgr->connect();
	$dbmgr->db_query($q);
	$err = $dbmgr->db_error();
	if($err>'') $errors[]="Error: $err";
// 	echo ( $err>'' ? "KO\n  Error: $err" : "OK" ) . "\n";
}

$cgi_params=array();
$cgi_params[]="dbetype=$dbetype";
$cgi_params[]="formtype=$formtype";
if(count($errors)>0)
	setMessage(implode("\n",$errors));
else
	setMessage("Auto Complete: OK");

$redir_string = "Location: http".(array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"]>''?'s':'')."://" . $_SERVER['HTTP_HOST']
                      . dirname($_SERVER['PHP_SELF'])
                      . "/$redir_page?".implode("&",$cgi_params);

header( $redir_string );
?>
