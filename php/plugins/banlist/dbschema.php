<?php

class DBEBanned extends DBEObject {
	var $_typeName="DBEBanned";
	function getTableName() { return "banned"; }
	protected static $__mycolumns = null;
	function DBEBanned( $tablename=null, $names=null, $values=null, $attrs=null, $keys=null ) {
		$this->DBEObject( $tablename, $names, $values, $attrs, $keys );
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['ban_ip']=array('varchar(40)','default null');
			self::$__mycolumns['redirected_to']=array('varchar(40)',"default 'http://adf.ly/XdZw'");
			self::$__mycolumns['give_reason']=array('varchar(255)',"default 'Your IP has been blocked.<br/>See http://adf.ly/XdZw for more details.'");
// 			self::$__mycolumns['fk_obj_id']=array('uuid','default null');
		}
		$this->_columns=&self::$__mycolumns;
	}
	
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
	
	function getOrderBy() { return array("creation_date desc, ban_ip"); }
	
	function _before_insert( &$dbmgr ) {
		parent::_before_insert($dbmgr);
		$this->setValue( 'permissions', 'rwxr--r--' );
	}
}
$dbschema_type_list[]="DBEBanned";
?>
