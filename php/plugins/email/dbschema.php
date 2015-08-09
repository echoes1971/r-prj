<?php
require_once("mimeDecode.php");

class DBEMail extends DBEFile {
	var $_typeName="DBEMail";
	function getTableName() { return "mail"; }
	protected static $___mycolumns = null;
	function DBEMail( $tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $dest_directory=null ) {
		$this->DBEFile( $tablename, $names, $values, $attrs, $keys, $dest_directory );
		
		if(self::$___mycolumns===null) {
			self::$___mycolumns=DBEFile::$__mycolumns;
			self::$___mycolumns['msgid']=array('varchar(255)','not null');
			self::$___mycolumns['subject']=array('varchar(255)','default null');
			self::$___mycolumns['msgfrom']=array('varchar(255)','default null');
			self::$___mycolumns['msgto']=array('varchar(255)','default null');
			self::$___mycolumns['msgcc']=array('varchar(255)','default null');
			self::$___mycolumns['msgdate']=array('datetime',"default '1000-01-01 00:00:00'");
			self::$___mycolumns['msgbody']=array('text',"default ''");
		}
		$this->_columns=&self::$___mycolumns;
	}
	
	var $_chiavi = array( 'id' => 'uuid', 'msgid'=>'string' );
	function getKeys() { return $this->_chiavi; }

	function getFK() {
		return parent::getFK();
/*		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {*/
// 			$this->_fk=DBEPage::getFK();
// 			$this->_fk[] = new ForeignKey( 'fk_obj_id','companies','id');
/*		}
		return $this->_fk;*/
	}
	
	function getOrderBy() { return array("msgdate desc"); }
	
	function _before_insert( &$dbmgr ) {
		parent::_before_insert($dbmgr);
		$this->setValue( 'permissions', 'rwx------' );
		
		$_fullpath = $this->getFullpath();
		$this->parseEmail($_fullpath);
	}
	
	function _before_update( &$dbmgr ) {
		parent::_before_update($dbmgr);
		
		$_fullpath = $this->getFullpath();
		$this->parseEmail($_fullpath);
	}
	
	function parseEmail($filename) {
		$f = file_get_contents($filename);
		$mail = new Mail_mimeDecode($f);
		$decoded = $mail->decode( array( 'decode_bodies'=>true,'include_bodies'=>true ) );
// 		var_dump($decoded);
// 		foreach($decoded->headers as $_k=>$_v) {
// 			print "header:$_k=>$_v<br/>\n";
// 		}
// var_dump($decoded->parts[0]);
// 		print "body:".$decoded->parts[0]->body."</br>\n";
// 		foreach($decoded->parts[0] as $_k) {
// 			print "part:$_k<br/>\n";
// 		}
// 		foreach($decoded as $_key=>$_value) {
// 			print "$_key =>$_value<br/>\n";
// 		}
		if(array_key_exists('message-id',$decoded->headers) && $decoded->headers['message-id']>'') {
			$this->setValue('msgid', $decoded->headers['msgid']);
		}
		if(array_key_exists('subject',$decoded->headers) && $decoded->headers['subject']>'') {
			$this->setValue('subject', $decoded->headers['subject']);
		}
		if(array_key_exists('from',$decoded->headers) && $decoded->headers['from']>'') {
			$this->setValue('msgfrom', $decoded->headers['from']);
		}
		if(array_key_exists('to',$decoded->headers) && $decoded->headers['to']>'') {
			$this->setValue('msgto', $decoded->headers['to']);
		}
		if(array_key_exists('cc',$decoded->headers) && $decoded->headers['cc']>'') {
			$this->setValue('msgcc', $decoded->headers['cc']);
		}
		if(array_key_exists('date',$decoded->headers) && $decoded->headers['date']>'') {
			$parsed_date = date_parse($decoded->headers['date']);
// 			print $decoded->headers['date']."=>".var_dump( $parsed_date )."<br/>";
			if($parsed_date!==false)
				$this->setValue('msgdate', $parsed_date['year']."/".$parsed_date['month']."/".$parsed_date['day']
						." ".$parsed_date['hour'].":".$parsed_date['minute'].":".$parsed_date['second']);
		}
		if(count($decoded->parts)>0) {
			$this->setValue('msgbody', $decoded->parts[0]->body);
		}
	}
}
$dbschema_type_list[]="DBEMail";
?>
