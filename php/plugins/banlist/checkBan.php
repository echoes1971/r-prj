<?php

function check_ban() {
	global $dbschema_type_list;
	
	$dbmgr = $_SESSION['dbmgr'];
	if ($dbmgr===null || get_class($dbmgr)=='__PHP_Incomplete_Class') {
		
		if(defined('ROOT_FOLDER')!==true)
			define("ROOT_FOLDER",     "../../");
		require_once(ROOT_FOLDER . "config.php");
		require_once(ROOT_FOLDER . "utils.php");
		require_once(ROOT_FOLDER . "db/dblayer.php");
		require_once(ROOT_FOLDER . "db/dbschema.php");
		//session_start();
		require_once(ROOT_FOLDER . "utils.php");
		require_once(ROOT_FOLDER . "/plugins/banlist/dbschema.php");
		
		$aFactory = new MyDBEFactory;
		$dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory );
	}
	$dbmgr->setVerbose(false);
	
	$remote_ip = $_SERVER['REMOTE_ADDR'];
	
	$ip_array = explode('.',$remote_ip);
	
	$cerca = new DBEBanned();
	$cerca->setValue( 'ban_ip', $remote_ip );
	$lista = $dbmgr->search($cerca,$use_like=0);
	
	if(count($lista)==0 && count($ip_array)>=3) {
		$cerca = new DBEBanned();
		$cerca->setValue( 'ban_ip', $ip_array[0].".".$ip_array[1].".".$ip_array[2].".*" );
		$lista = $dbmgr->search($cerca,$use_like=0);
	}
	if(count($lista)==0 && count($ip_array)>=2) {
		$cerca = new DBEBanned();
		$cerca->setValue( 'ban_ip', $ip_array[0].".".$ip_array[1].".*.*" );
		$lista = $dbmgr->search($cerca,$use_like=0);
	}
	if(count($lista)==0 && count($ip_array)>=1) {
		$cerca = new DBEBanned();
		$cerca->setValue( 'ban_ip', $ip_array[0].".*.*.*" );
		$lista = $dbmgr->search($cerca,$use_like=0);
	}
	
	if(count($lista)>0) {
		$banned=$lista[0];
		$redir_to = $banned->getValue('redirected_to');
		
		//rproject_mylog("BANNED");
		$my_note = "BANNED";
		$my_note2 = $_SERVER["REQUEST_URI"];
		$remote_ip=$_SERVER['REMOTE_ADDR'];
		$oggi = strftime("%Y-%m-%d", date(time()) );
		$ora = strftime("%H:%M:%S", date(time()) );
		// Checking if an old entry exists
		$cerca = new DBELog();
		$cerca->setValue('ip', $remote_ip );
		$listaPast = $dbmgr->search( $cerca, 0, 'url desc' );
		if ($debug) { print "$listaPast: $listaPast"; }
		// Checking if today's entry exists
		$cerca = new DBELog();
		$cerca->setValue('ip', $remote_ip );
		$cerca->setValue('data', $oggi );
		$lista = $dbmgr->search( $cerca, 0, '' );
		$mydbe=new DBELog;
		$mydbe->setValue('ip', $remote_ip );
		$mydbe->setValue('data', $oggi );
		$mydbe->setValue('ora', $ora );
		// IF I have older entries for the same IP
		if( count($listaPast)>0 ) $mydbe->setValue('url', $listaPast[0]->getValue('url') );
		if (count($lista)==0) {
			$mydbe->setValue('count', 1 );
			if($my_note!=null && strlen($my_note)>0)
				$mydbe->setValue('note', $my_note );
			if($my_note2!=null && strlen($my_note2)>0)
				$mydbe->setValue('note2', "$ora-$my_note2");
			$dbmgr->insert($mydbe);
		} else {
			// Updating counter
			$mydbe->setValue('count', $lista[0]->getValue('count')+1 );
			if($my_note2!=null && strlen($my_note2)>0)
				$mydbe->setValue('note2', $lista[0]->getValue('note2') . "\n$ora-$my_note2");
			$dbmgr->update($mydbe);
		}
		
		//echo "<script>window.top.location='$redir_to';</script>";
		header("Location: $redir_to");
		exit;
	}
	
}


?>
