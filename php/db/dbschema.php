<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbschema.php $
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

/**
 * Versions
 *
 * - 1 first version
 * added deleted management on objects
 * - 2 added events table
 */
define("DB_VERSION",2);

$dbschema_type_list=array();

/** *********************************** RRA Framework: start. *********************************** */
/** DB Version */
class DBEDBVersion extends DBEntity {
	var $_typeName="DBEDBVersion";
	public static $_mycolumns = array(
                'model_name'=> array('varchar(100)','not null'),
				'version'=>array('int','not null'),
			);
	function DBEDBVersion($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=null) {
		$this->DBEntity($tablename, $names, $values, $attrs, $keys, $columns!==null ? $columns : self::$_mycolumns);
	}
	function getTableName() { return "dbversion"; }
	
	// Statica
	var $_chiavi = array('model_name'=>'varchar(255)'); //array('version' => 'int');
	function getKeys() { return $this->_chiavi; }
	function getOrderBy() { return array("version"); }
	
	function _before_insert(&$dbmgr) {}
	function _after_insert(&$dbmgr) {}

	function version() { $tmp = $this->getValue('version'); return $tmp===null ? 0 : $tmp; }

}
$dbschema_type_list[]="DBEDBVersion";

class DBEUser extends DBEntity {
	var $_typeName='DBEUser';
	public static $_mycolumns = array(
				'id'=>array('uuid','not null'),
				'login'=>array('varchar(255)','not null'),
				'pwd'=>array('varchar(255)','not null'),
				'pwd_salt'=>array('varchar(4)',"default ''"),
				'fullname'=>array('text','default null'),
				'group_id'=>array('uuid','not null'),
			);
	function DBEUser($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=null) {
		$this->DBEntity($tablename, $names, $values, $attrs, $keys, $columns!==null ? $columns : self::$_mycolumns);
	}
	function getTableName() { return "users"; }
	
	// Statica
	var $_chiavi = array('id' => 'uuid');
	function getKeys() { return $this->_chiavi; }
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			$this->_fk[] = new ForeignKey('group_id','groups','id');
		}
		return $this->_fk;
	}
	function getOrderBy() { return array("fullname"); }
	
	function checkNewPassword() {
		$ret = true;
/*		if($this->getValue('old_pwd')>'') {
			$this->setValue('pwd',$this->getValue('old_pwd'));
		} else if($this->getValue('new_pwd')>'' && $this->getValue('new_pwd')==$this->getValue('new2_pwd')) {
			$this->setValue('pwd',$this->getValue('new_pwd'));
		} else {
			$ret = false;
		}
		$this->setValue('old_pwd',null);
		$this->setValue('new_pwd',null);
		$this->setValue('new2_pwd',null);*/
		return $ret;
	}
	
	function _before_insert(&$dbmgr) {
		$myid = $dbmgr->getNextUuid($this);
		$this->setValue('id', $myid);
		if($this->checkNewPassword()) {
			$this->_createGroup($dbmgr);
		}
	}
	function _after_insert(&$dbmgr) {
		$this->_checkGroupAssociation($dbmgr);
	}
	function _after_update(&$dbmgr) {
		$this->_checkGroupAssociation($dbmgr);
	}
	function _after_delete(&$dbmgr) {
		$cerca=new DBEUserGroup();
		$cerca->setValue('user_id',$this->getValue('id'));
		$lista = $dbmgr->search($cerca, $uselike=0);
		foreach($lista as $ass) {
			$dbmgr->delete($ass);
		}
		$this->_deleteGroup($dbmgr);
	}
	
	function _createGroup(&$dbmgr) {
		$dbe = new DBEGroup();
		$dbe->setValue('name', $this->getValue('login'));
		$dbe->setValue('description', "Private group for ".$this->getValue('id')."-".$this->getValue('login'));
		$dbe = $dbmgr->insert($dbe);
		$this->setValue('group_id', $dbe->getValue('id'));
	}
	function _deleteGroup(&$dbmgr) {
		$dbe = new DBEGroup();
		$dbe->setValue('id', $this->getValue('group_id'));
		$dbe = $dbmgr->delete($dbe);
	}
	
	function _checkGroupAssociation(&$dbmgr) {
		$ug=new DBEUserGroup();
		$ug->setValue('user_id',$this->getValue('id'));
		$ug->setValue('group_id',$this->getValue('group_id'));
		$exists = $dbmgr->exists($ug);
		if(!$exists)
			$dbmgr->insert($ug);
	}
	
	function isRoot() { return $this->getValue('id')==-1; }
}
$dbschema_type_list[]='DBEUser';
class DBEGroup extends DBEntity {
	var $_typeName='DBEGroup';
	public static $_mycolumns = array(
				'id'=>array('uuid','not null'),
				'name'=>array('varchar(255)','not null'),
				'description'=>array('text','default null'),
			);
	function DBEGroup($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=null) {
		$this->DBEntity($tablename, $names, $values, $attrs, $keys, $columns!==null ? $columns : self::$_mycolumns);
	}
	function getTableName() { return "groups"; }
	
	// Statica
	var $_chiavi = array('id' => 'uuid');
	function getKeys() { return $this->_chiavi; }
	function getOrderBy() { return array("name"); }
	
	function _before_insert(&$dbmgr) {
		$myid = $dbmgr->getNextUuid($this);
		$this->setValue('id', $myid);
	}
	/**
	 * Assegna automaticamente il creatore del gruppo come membro (DBEUserGroup)
	 * una volta creato il gruppo.
	 * Lo aggiunge inoltre automaticamente alla lista dei gruppi dell'utente in dbmgr
	 */
	function _after_insert(&$dbmgr) {
		$dbe = new DBEUserGroup();
		$dbe->setValue('group_id', $this->getValue('id'));
		$dbe->setValue('user_id', $dbmgr->getDBEUser()->getValue('id'));
		$dbmgr->insert($dbe);
		$dbmgr->addGroup($this->getValue('id'));
	}
	function _after_delete(&$dbmgr) {
		$cerca=new DBEUserGroup();
		$cerca->setValue('group_id',$this->getValue('id'));
		$lista = $dbmgr->search($cerca, $uselike=0);
		foreach($lista as $ass) {
			$dbmgr->delete($ass);
		}
	}
}
$dbschema_type_list[]='DBEGroup';
class DBEUserGroup extends DBAssociation {
	var $_typeName="DBEUserGroup";
	function DBEUserGroup($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=null) {
		$this->DBAssociation($tablename, $names, $values, $attrs, $keys, $columns!==null ? $columns
			: array(
				'user_id'=>array('uuid','not null'),
				'group_id'=>array('uuid','not null'),
			));
	}
	function getTableName() { return "users_groups"; }
	
	// Statica
	var $_chiavi = array('user_id' => 'uuid', 'group_id'=>'uuid');
	function getKeys() { return $this->_chiavi; }
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			$this->_fk[] = new ForeignKey('user_id','users','id');
			$this->_fk[] = new ForeignKey('group_id','groups','id');
		}
		return $this->_fk;
	}
}
$dbschema_type_list[]="DBEUserGroup";

class DBELog extends DBEntity {
	var $_typeName="DBELog";
	public static $_mycolumns = array(
				'ip'=>array('varchar(16)','not null'),
				'data'=>array('date',"not null default '0000-00-00'"),
				'ora'=>array('time',"not null default '00:00:00'"),
				'count'=>array('int',"not null default 0"),
				'url'=>array('varchar(255)','default null'),
				'note'=>array('varchar(255)',"not null default ''"),
				'note2'=>array('text','not null'),
			);
	function DBELog($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=null) {
		$this->DBEntity($tablename, $names, $values, $attrs, $keys, $columns!==null ? $columns : self::$_mycolumns);
	}
	function getTableName() { return "log"; }
	
	// Statica
	var $_chiavi = array('ip' => 'text', 'data'=>'date');
	function getKeys() {
		$_chiavi = array();
		$_chiavi['ip'] = 'varchar(16)';
		$_chiavi['data'] = 'date';
		return $_chiavi;
	}
	function getOrderBy() { return array('data desc','ora desc'); }
	
	function _before_insert(&$dbmgr) {
// 		$nomeTabella = $dbmgr->_buildTableName("seq_id");
// 		$tmp = $dbmgr->select($nomeTabella, "select id as id from $nomeTabella");
// 		$myid = $tmp[0]->getValue('id') + 1;
//  		$myid = $dbmgr->getNextId($this);
//  		$this->setValue('id', $myid);
	}
}
$dbschema_type_list[]="DBELog";


class DBEObject extends DBEntity {
	public static $_mycolumns = array(
				'id'=>array('uuid','not null'),
				'owner'=>array('uuid','not null'),
				'group_id'=>array('uuid','not null'),
				'permissions'=>array('char(9)',"not null default 'rwx------'"), // user,group,all
				'creator'=>array('uuid','not null'),
				'creation_date'=>array('datetime','default null'),
				'last_modify'=>array('uuid','not null'),
				'last_modify_date'=>array('datetime','default null'),
				// 2011.05.15: start.
				'deleted_by'=>array('uuid','default null'),
				'deleted_date'=>array('datetime',"not null default '0000-00-00 00:00:00'"),
				// 2011.05.15: end.
				'father_id'=>array('uuid','default null'),
				'name'=>array('varchar(255)','not null'),
				'description'=>array('text','default null'),
			);
	function DBEObject($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=null) {
		$this->DBEntity($tablename, $names, $values, $attrs, $keys
			,$columns!==null ? $columns
			: self::$_mycolumns
			);
		if($columns===null) {
			if($this->_columns===null) $this->_columns=&DBEObject::$_mycolumns;
		} else {
			$this->_columns=$columns;
		}
	}
	var $_typeName='DBEObject';
	function getTableName() { return "objects"; }
	
	// Statica
	var $_chiavi = array('id' => 'uuid');
	function getKeys() {
		return $this->_chiavi;
	}
	function getOrderBy() {
		return array("name");
	}
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			$this->_fk[] = new ForeignKey('owner','users','id');
			$this->_fk[] = new ForeignKey('group_id','groups','id');
			$this->_fk[] = new ForeignKey('creator','users','id');
			$this->_fk[] = new ForeignKey('last_modify','users','id');
			$this->_fk[] = new ForeignKey('deleted_by','users','id');
			$this->_fk[] = new ForeignKey('father_id',$this->getTableName(),'id');
		}
		return $this->_fk;
	}
	
	function _getTodayString() {
		$oggi_array = getdate(time());
		$oggi = $oggi_array['year'] . "-" . (strlen($oggi_array['mon'])<2 ? "0" : "") . $oggi_array['mon'] . "-" . (strlen($oggi_array['mday'])<2 ? "0" : "") . $oggi_array['mday'] . " " . (strlen($oggi_array['hours'])<2 ? "0" : "") . $oggi_array['hours'] . ":" . (strlen($oggi_array['minutes'])<2 ? "0" : "") . $oggi_array['minutes'] . ":00";
		return $oggi;
	}
	
	function getOwnerId() { return $this->getValue('owner'); }
	function getGroupId() { return $this->getValue('group_id'); }
	
	/** @date 2011.05.15 */
	function isDeleted() { return $this->getValue('deleted_date')>'0000-00-00 00:00:00'; }
	
	function canRead($kind='') {
		if(!($this->getValue('permissions')>'')) return true;
		switch($kind) {
			case 'U': // User
				return substr($this->getValue('permissions'), 0+0, 1)=='r';
				break;
			case 'G': // Group
				return substr($this->getValue('permissions'), 0+3, 1)=='r';
				break;
			default: // All
				return substr($this->getValue('permissions'), 0+6, 1)=='r';
				break;
		}
	}
	function canWrite($kind='') {
		if(!($this->getValue('permissions')>'')) return true;
		switch($kind) {
			case 'U': // User
				return substr($this->getValue('permissions'), 1+0, 1)=='w';
				break;
			case 'G': // Group
				return substr($this->getValue('permissions'), 1+3, 1)=='w';
				break;
			default: // All
				return substr($this->getValue('permissions'), 1+6, 1)=='w';
				break;
		}
	}
	function canExecute($kind='') {
		if(!($this->getValue('permissions')>'')) return true;
		switch($kind) {
			case 'U': // User
				return substr($this->getValue('permissions'), 2+0, 1)=='x';
				break;
			case 'G': // Group
				return substr($this->getValue('permissions'), 2+3, 1)=='x';
				break;
			default: // All
				return substr($this->getValue('permissions'), 2+6, 1)=='x';
				break;
		}
	}
	
	function setDefaultValues(&$dbmgr) {
		$myuser = $dbmgr->getDBEUser();
		if($myuser!=null) {
			if(!($this->getValue('owner')>''))
				$this->setValue('owner',$myuser->getValue('id'));
			if(!($this->getValue('group_id')>''))
				$this->setValue('group_id',$myuser->getValue('group_id'));
			$this->setValue('creator',$myuser->getValue('id'));
			$this->setValue('last_modify',$myuser->getValue('id'));
		}
		$this->setValue('creation_date', $this->_getTodayString());
		$this->setValue('last_modify_date', $this->_getTodayString());
		// 2011.05.16: start.
		$this->setValue('deleted_date', '0000-00-00 00:00:00');
		// 2011.05.16: end.
		
		if($this->getValue('father_id')===null) {
			$this->setValue('father_id',0);
			
			// ATTENZIONE: questo non ce l'hanno tutti gli oggetti!!!
			if($this->getValue('fk_obj_id')>'') {
				$fkobj = $dbmgr->objectById($this->getValue('fk_obj_id'));
				// 2011.09.06 the owner is BY DEFAULT the creator - $this->setValue('owner',$fkobj->getValue('owner'));
				$this->setValue('group_id',$fkobj->getValue('group_id'));
				$this->setValue('permissions',$fkobj->getValue('permissions'));
				
				// 2010.07.16: siamo in fase di creazione da un 'master': leggo quindi direttamente il figlio
				$this->setValue('father_id',$this->getValue('fk_obj_id'));
			}
		} else {
			$father = $dbmgr->objectById($this->getValue('father_id'));
			if($father!==null) {
// 				$this->setValue('owner',$father->getValue('owner'));
				$this->setValue('group_id',$father->getValue('group_id'));
				$this->setValue('permissions',$father->getValue('permissions'));
			}
		}
		
	}
	
	function _before_insert(&$dbmgr) {
		$myid = $dbmgr->getNextUuid($this);
		$this->setValue('id', $myid);
		$this->setDefaultValues($dbmgr);
	}
	function _before_update(&$dbmgr) {
		$myuser = $dbmgr->getDBEUser();
		if($myuser!=null) {
			$this->setValue('last_modify',$myuser->getValue('id'));
		}
		$this->setValue('last_modify_date', $this->_getTodayString());
	}
	function _before_delete(&$dbmgr) {
		if($this->isDeleted()) return;
		// I remove all the values but the keys, so that a proper update string can be built
/*		foreach($this->getNames() as $_k) {
			if($this->isPrimaryKey($_k)) continue;
			$this->setValue($_k,null);
		}*/
		$myuser = $dbmgr->getDBEUser();
		if($myuser!=null) {
			$this->setValue('deleted_by',$myuser->getValue('id'));
		}
		$this->setValue('deleted_date', $this->_getTodayString());
	}
}
$dbschema_type_list[]='DBEObject';


class ObjectMgr extends DBMgr {
	// function ObjectMgr($server, $user, $pwd, $dbname, $schema, $aDBEFactory=null,$dbeuser=null, $user_groups_list=array()) {
	// 	echo "STOCAZZO\n";
	// 	$this->DBMgr($server, $user, $pwd, $dbname, $schema, $aDBEFactory,$dbeuser, $user_groups_list);
	// 	echo "STOPPARDI\n";
	// }
	function canRead(&$obj) {
		$ret = false;
		$myuser = $this->getDBEUser();
		if($obj->canRead())
			$ret=true;
		elseif($obj->canRead('G') && $this->hasGroup($obj->getGroupId()))
			$ret=true;
		elseif($obj->canRead('U') && $myuser!==null && $myuser->getValue('id')==$obj->getOwnerId())
			$ret=true;
		return $ret;
	}
	function canWrite(&$obj) {
		$ret = false;
		$myuser = $this->getDBEUser();
		// 2012.05.16: start.
		if($myuser!==null && $obj->getValue('creator')==$myuser->getValue('id')) {
			$ret=true;
		} else {
		// 2012.05.16: end.
			if($obj->canWrite())
				$ret=true;
			elseif($obj->canWrite('G') && $this->hasGroup($obj->getGroupId()))
				$ret=true;
			elseif($obj->canWrite('U') && $myuser!==null && $myuser->getValue('id')==$obj->getOwnerId())
				$ret=true;
		// 2012.05.16: start.
		}
		// 2012.05.16: end.
		return $ret;
	}
	function canExecute(&$obj) {
		$ret = false;
		$myuser = $this->getDBEUser();
		// 2012.05.16: start.
		if($myuser!==null && $obj->getValue('creator')==$myuser->getValue('id')) {
			$ret=true;
		} else {
		// 2012.05.16: end.
			if($obj->canExecute())
				$ret=true;
			elseif($obj->canExecute('G') && $this->hasGroup($obj->getGroupId()))
				$ret=true;
			elseif($obj->canExecute('U') && $myuser!==null && $myuser->getValue('id')==$obj->getOwnerId())
				$ret=true;
		// 2012.05.16: start.
		}
		// 2012.05.16: end.
		return $ret;
	}
	function select($classname, $tablename, $searchString) {
		if($this->_verbose) { print "ObjectMgr.select: start.<br/>\n"; }
		global $GROUP_ADMIN;
		$tmp = parent::select($classname, $tablename, $searchString);
		$myuser = $this->getDBEUser();
		if($this->_verbose) { print "ObjectMgr.select: myuser=".($myuser===null?"NULL":$myuser->to_string())." <br/>"; }
		if($myuser!==null && $myuser->isRoot())
			return $tmp;
		$ret = array();
		foreach($tmp as $obj) {
			if(is_a($obj, 'DBEObject')) {
				if($myuser!==null && $obj->getValue('creator')==$myuser->getValue('id')) {
					$ret[]=$obj;
					continue;
				}
				if($this->_verbose) { print "ObjectMgr.select: obj.deleted_date=".$obj->getValue('deleted_date')."<br/>\n"; }
				if($obj->isDeleted())
					continue;
				if($obj->canRead())
					$ret[]=$obj;
				elseif($obj->canRead('G') && $this->hasGroup($obj->getGroupId()))
					$ret[]=$obj;
				elseif($obj->canRead('U') && $myuser!==null && $myuser->getValue('id')==$obj->getOwnerId())
					$ret[]=$obj;
				continue;
/* 2012.05.16: start.
			} elseif(is_a($obj, 'DBEUser')) {
				if($myuser===null || $this->hasGroup($GROUP_ADMIN) || $obj->getValue('id')==$myuser->getValue('id'))
					$ret[]=$obj;
				continue;
			} elseif(is_a($obj, 'DBEGroup')) {
				if($myuser===null || $this->hasGroup($GROUP_ADMIN) || $this->hasGroup($obj->getValue('id')))
					$ret[]=$obj;
				continue;
2012.05.16: end. */
			}
			$ret[]=$obj;
		}
		if($this->_verbose) {
			print "ObjectMgr.select: ret=".count($ret)."<br/>\n";
			print "ObjectMgr.select: end.<br/>\n";
		}
		return $ret;
	}
	function insert($dbe) {
		$have_permission=true;
		if(is_a($dbe, 'DBEObject')) {
			// 2012.05.16: start.
// FIXME if default values are not setted, the following will FAIL!!!!
			$have_permission = $this->canWrite($dbe);
/*			$have_permission=false;
			$myuser = $this->getDBEUser();
			if($myuser!==null && $dbe->getValue('creator')==$myuser->getValue('id')) {
				$have_permission=true;
			} else {
				if($dbe->canWrite())
					$have_permission=true;
				elseif($dbe->canWrite('G') && $this->hasGroup($dbe->getGroupId()))
					$have_permission=true;
				elseif($dbe->canWrite('U') && $myuser!==null && $myuser->getValue('id')==$dbe->getOwnerId())
					$have_permission=true;
			}
*/			// 2012.05.16: end.
		}
		return $have_permission ? parent::insert($dbe) : null;
	}
	
	function update($dbe) {
		$have_permission=true;
		if(is_a($dbe, 'DBEObject')) {
			// 2012.05.16: start.
			$have_permission = $this->canWrite($dbe);
/*			$have_permission=false;
			$myuser = $this->getDBEUser();
			if($myuser!==null && $dbe->getValue('creator')==$myuser->getValue('id')) {
				$have_permission=true;
			} else {
				if($dbe->canWrite())
					$have_permission=true;
				elseif($dbe->canWrite('G') && $this->hasGroup($dbe->getGroupId()))
					$have_permission=true;
				elseif($dbe->canWrite('U') && $myuser!==null && $myuser->getValue('id')==$dbe->getOwnerId())
					$have_permission=true;
			}
*/			// 2012.05.16: end.
		}
		return $have_permission ? parent::update($dbe) : null;
	}
	
	function delete($dbe) {
		$have_permission=true;
		if(is_a($dbe, 'DBEObject')) {
			// FIXME 2012.04.03: start.
			// FIXME 2012.04.03: HORRIBLE PATCH!!!
			$tmpdbe = $this->fullObjectById($dbe->getValue('id'), false);
			$dbe = $tmpdbe===null ? $dbe : $tmpdbe;
// 			$dbe = $this->fullObjectById($dbe->getValue('id'), false);
			// FIXME 2012.04.03: end.
			// 2012.05.16: start.
			$have_permission = $this->canWrite($dbe);
/*			$have_permission=false;
			$myuser = $this->getDBEUser();
			if($myuser!==null && $dbe->getValue('creator')==$myuser->getValue('id')) {
				$have_permission=true;
			} else {
				if($dbe->canWrite())
					$have_permission=true;
				elseif($dbe->canWrite('G') && $this->hasGroup($dbe->getGroupId()))
					$have_permission=true;
				elseif($dbe->canWrite('U') && $myuser!==null && $myuser->getValue('id')==$dbe->getOwnerId())
					$have_permission=true;
			}
*/			// 2012.05.16: end.
		}
		if($have_permission) {
			if(!is_a($dbe, 'DBEObject') || $dbe->isDeleted()) {
				return parent::delete($dbe);
			} else {
				$this->connect();
				$dbe->_before_delete($this);
				$sqlString = $this->_buildUpdateString($dbe);
				if($this->_verbose) {
					print "ObjectMgr.delete: sqlString = $sqlString<br />\n";
				}
				$result = $this->db_query($sqlString);
// 				echo "DB Error:" . $this->db_error() . "<br/>\n";
				$dbe->_after_delete($this);
				return $dbe;
			}
		} else {
			return $dbe;
		}
	}
	
	function _buildSelectString($dbe, $uselike=1,$caseSensitive=false) {
		if($dbe->getTypeName()!='DBEObject')
			return parent::_buildSelectString($dbe,$uselike,$caseSensitive);
		$tipi = $this->getFactory()->getRegisteredTypes();
		$q = array();
		foreach($tipi as $tablename=>$classname) {
			$mydbe = $this->getInstance($classname); // 2011.04.04 eval("\$mydbe=new $classname;");
			if(!is_a($mydbe,'DBEObject') || is_a($mydbe,"DBAssociation") || $mydbe->getTypeName()=='DBEObject') continue;
			$mydbe->setValuesDictionary($dbe->getValuesDictionary());
			$q[]=str_replace("select * ",
					"select '$classname' as classname,id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description ",
					parent::_buildSelectString($mydbe,$uselike,$caseSensitive));
		}
		$searchString = implode($q, " union ");
		return $searchString;
	}
	
	/**	Provides Search methods
	 * @par dbe contains search parameters
	 * @par uselike
	 * @par caseSensitive
	 * @par order_by optional order by string
	 * @par ignore_deleted ignores objects marked as deleted
	 * @par full_object retrieve the whole data of the object, only common object attributes otherwise
	 */
	function search($dbe, $uselike=1, $caseSensitive=false, $orderby=null,$ignore_deleted=true,$full_object=true) {
		if($this->_verbose) { printf("ObjectMgr::search: start.<br/>\n"); }
		if($dbe->getTypeName()!='DBEObject') {
			if(is_a($dbe,'DBEObject') && $ignore_deleted===true) $dbe->setValue('deleted_date','0000-00-00 00:00:00');
			return parent::search($dbe, $uselike, $caseSensitive, $orderby);
		}
		// 2011.05.16: if unspecified, I want non deleted objects
		if($ignore_deleted===true) $dbe->setValue('deleted_date','0000-00-00 00:00:00');
		$ret=array();
		$tmp = parent::search($dbe, $uselike, $caseSensitive, $orderby);
		// 2012.03.05: start.
		if(!$full_object) {
			if($this->_verbose) { printf("ObjectMgr::search: end.<br/>\n"); }
			return $tmp;
		}
		// 2012.03.05: end.
		// FIXME ottimizzare
		foreach($tmp as $_obj) {
			eval("\$cerca=new ".$_obj->getValue('classname')."();");
			$cerca->setValue('id',$_obj->getValue('id'));
			$lista = $this->search($cerca,$uselike=0);
			if(count($lista)!=1) continue;
			$ret[]=$lista[0];
		}
		if($this->_verbose) { printf("ObjectMgr::search: end.<br/>\n"); }
		return $ret;
	}
	
	function objectById($id,$ignore_deleted=true) {
		$tipi = $this->getFactory()->getRegisteredTypes();
		$q = array();
		foreach($tipi as $tablename=>$classname) {
			$mydbe = $this->getInstance($classname); // 2011.04.04 eval("\$mydbe=new $classname;");
			if($classname=='DBEObject' || !is_a($mydbe,'DBEObject') || is_a($mydbe,"DBAssociation")) continue;
			$q[]="select '$classname' as classname,id,owner,group_id,permissions,creator,"
						."creation_date,last_modify,last_modify_date,"
						."deleted_by,deleted_date," // 2012.04.04
						."father_id,name,description"
					." from ".$this->buildTableName($mydbe)
					." where id='".DBEntity::hex2uuid($id)."'"
					.($ignore_deleted?" and deleted_date='0000-00-00 00:00:00'":'');
		}
		$searchString = implode($q, " union ");
		if($this->_verbose) { printf("query: $searchString<br/>\n"); }
		$lista = $this->select('DBEObject', "objects", $searchString);
		return count($lista)==1 ? $lista[0] : null;
	}
	function fullObjectById($id,$a_ignore_deleted=true) {
		$myobj = $this->objectById($id,$a_ignore_deleted);
		if($myobj===null) return null;
		eval("\$cerca=new ".$myobj->getValue('classname')."();");
		$cerca->setValue('id',$myobj->getValue('id'));
		$lista = $this->search($cerca,0,false,null,$a_ignore_deleted);
		if($this->_verbose) { printf("ObjectMgr.fullObjectById: lista=".count($lista)."<br/>\n"); }
		return count($lista)==1 ? $lista[0] : null;
	}
	/** Ricerca gli oggetti per nome, tipo wiki */
	function objectByName($name,$ignore_deleted=true) {
		$tipi = $this->getFactory()->getRegisteredTypes();
		$q = array();
		foreach($tipi as $tablename=>$classname) {
			$mydbe = $this->getInstance($classname); // 2011.04.04 eval("\$mydbe=new $classname;");
			if(!is_a($mydbe,'DBEObject') || is_a($mydbe,"DBAssociation")) continue;
			$q[]="select '$classname' as classname,id,owner,group_id,permissions,creator,"
						."creation_date,last_modify,last_modify_date,father_id,name,description"
					." from ".$this->buildTableName($mydbe)
					." where name='$name'"
					.($ignore_deleted?" and deleted_date='0000-00-00 00:00:00'":'');
		}
		$searchString = implode($q, " union ");
		if($this->_verbose) {
			printf("query: $searchString<br/>\n");
		}
		// 2012.05.07: start.
		return $this->select('DBEObject', "objects", $searchString);
		//$lista = $this->select('DBEObject', "objects", $searchString);
		//return count($lista)==1 ? $lista[0] : null;
		// 2012.05.07: end.
	}
	function fullObjectByName($name,$ignore_deleted=true) {
		// 2012.05.07: start.
		$lista = $this->objectByName($name,$ignore_deleted);
		$ret=array();
		foreach($lista as $myobj) {
			eval("\$cerca=new ".$myobj->getValue('classname')."();");
			$cerca->setValue('id',$myobj->getValue('id'));
			$lista = $this->search($cerca,$uselike=0,$ignore_deleted=$ignore_deleted);
			if(count($lista)==1) $ret[]=$lista[0];
		}
		return $ret;
/*		$myobj = $this->objectByName($name,$ignore_deleted);
		if($myobj===null) return null;
		eval("\$cerca=new ".$myobj->getValue('classname')."();");
		$cerca->setValue('id',$myobj->getValue('id'));
		$lista = $this->search($cerca,$uselike=0,$ignore_deleted=$ignore_deleted);
		return count($lista)==1 ? $lista[0] : null;
*/		// 2012.05.07: end.
	}
	// 2012.04.30: start.
	function login($login,$pwd) {
		$valori = array('login'=>$login, 'pwd'=>$pwd,);
		$cerca = new DBEUser(null,null,null,$attrs=$valori,null) ;
		$ris = $this->search($cerca, $uselike=0);
		
		$__utente=null;
		
		if(count($valori)==2 && $valori['login']>"" && $valori['pwd']>"" && count($ris)==1) {
			// User FOUND
			$__utente = $ris[0];
			$this->setDBEUser($__utente);
			$cerca = new DBEUserGroup();
			$cerca->readFKFrom($__utente);
			$lista=$this->search($cerca, $uselike=0);
			$lista_gruppi=array();
			foreach($lista as $g) { $lista_gruppi[]=$g->getValue('group_id'); }
			if(!in_array($__utente->getValue('group_id'), $lista_gruppi))
				$lista_gruppi[]=$__utente->getValue('group_id');
			$this->setUserGroupsList($lista_gruppi);
		}
		return $__utente;
	}
	// 2012.04.30: end.
}

/** *********************************** RRA Framework: end. *********************************** */


/** *********************************** RRA Contacts: start. *********************************** */
class DBECountry extends DBEntity {
	var $_typeName="DBECountry";
	public static $_mycolumns = array(
		'id'=>array('uuid','not null'),
		'Common_Name'=>array('varchar(255)','default null'),
		'Formal_Name'=>array('varchar(255)','default null'),
		'Type'=>array('varchar(255)','default null'),
		'Sub_Type'=>array('varchar(255)','default null'),
		'Sovereignty'=>array('varchar(255)','default null'),
		'Capital'=>array('varchar(255)','default null'),
		'ISO_4217_Currency_Code'=>array('varchar(255)','default null'),
		'ISO_4217_Currency_Name'=>array('varchar(255)','default null'),
		'ITU_T_Telephone_Code'=>array('varchar(255)','default null'),
		'ISO_3166_1_2_Letter_Code'=>array('varchar(255)','default null'),
		'ISO_3166_1_3_Letter_Code'=>array('varchar(255)','default null'),
		'ISO_3166_1_Number'=>array('varchar(255)','default null'),
		'IANA_Country_Code_TLD'=>array('varchar(255)','default null'),
	);
	function DBECountry($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEntity($tablename, $names, $values, $attrs, $keys, self::$_mycolumns);
	}
	function getTableName() { return "countrylist"; }
	var $_chiavi = array('id' => 'uuid');
	function getKeys() {
		return $this->_chiavi;
	}
	function getOrderBy() {
		return array("id");
	}
}
$dbschema_type_list[]="DBECountry";
class DBECompany extends DBEObject {
	var $_typeName="DBECompany";
	protected static $__mycolumns = null;
	
	function DBECompany($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);

		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['street']=array('varchar(255)','default null');
			self::$__mycolumns['zip']=array('varchar(255)','default null');
			self::$__mycolumns['city']=array('varchar(255)','default null');
			self::$__mycolumns['state']=array('varchar(255)','default null');
			self::$__mycolumns['fk_countrylist_id']=array('uuid','default null');
			self::$__mycolumns['phone']=array('varchar(255)','default null');
			self::$__mycolumns['fax']=array('varchar(255)','default null');
			self::$__mycolumns['email']=array('varchar(255)','default null');
			self::$__mycolumns['url']=array('varchar(255)','default null');
			self::$__mycolumns['p_iva']=array('varchar(16)','default null');
		}
		$this->_columns=&self::$__mycolumns;
	}
	function getTableName() { return "companies"; }
	
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			$this->_fk[] = new ForeignKey('fk_countrylist_id','countrylist','id');
		}
		return $this->_fk;
	}
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
	}
}
$dbschema_type_list[]="DBECompany";
class DBEPeople extends DBEObject {
	var $_typeName="DBEPeople";
	protected static $__mycolumns = null;
	function DBEPeople($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['street']=array('varchar(255)','default null');
			self::$__mycolumns['zip']=array('varchar(255)','default null');
			self::$__mycolumns['city']=array('varchar(255)','default null');
			self::$__mycolumns['state']=array('varchar(255)','default null');
			self::$__mycolumns['fk_countrylist_id']=array('uuid','default null');
			self::$__mycolumns['fk_companies_id']=array('uuid','default null');
			self::$__mycolumns['fk_users_id']=array('uuid','default null');
			self::$__mycolumns['phone']=array('varchar(255)','default null');
			self::$__mycolumns['office_phone']=array('varchar(255)','default null');
			self::$__mycolumns['mobile']=array('varchar(255)','default null');
			self::$__mycolumns['fax']=array('varchar(255)','default null');
			self::$__mycolumns['email']=array('varchar(255)','default null');
			self::$__mycolumns['url']=array('varchar(255)','default null');
			self::$__mycolumns['codice_fiscale']=array('varchar(20)','default null');
			self::$__mycolumns['p_iva']=array('varchar(16)','default null');
		}
		$this->_columns=&self::$__mycolumns;
	}
	function getTableName() {
		return "people";
	}
	
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			$this->_fk[] = new ForeignKey('fk_countrylist_id','countrylist','id');
			$this->_fk[] = new ForeignKey('fk_companies_id','companies','id');
			$this->_fk[] = new ForeignKey('fk_users_id','users','id');
		}
		return $this->_fk;
	}
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
	}
}
$dbschema_type_list[]="DBEPeople";
/** *********************************** RRA Contacts: end. *********************************** */

/** *********************************** CMS: start. *********************************** */
class DBEEvent extends DBEObject {
	var $_typeName="DBEEvent";
	function getTableName() { return "events"; }
	protected static $__mycolumns = null;
	function DBEEvent($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['fk_obj_id']=array('uuid','default null');
			
			self::$__mycolumns['start_date']=array('datetime',"not null default '0000-00-00 00:00:00'");
			self::$__mycolumns['end_date']=array('datetime',"not null default '0000-00-00 00:00:00'");
			self::$__mycolumns['all_day']=array('char(1)',"not null default '1'"); // Bool - An all day event?
			
			self::$__mycolumns['url']=array('varchar(255)',"default null"); // An Url
// 	RIDONDANTE		self::$__mycolumns['private']=array('int',"default null"); // Bool - Is it private?
			
			self::$__mycolumns['alarm']=array('char(1)',"default '0'"); // Bool - Signal an alarm before?
			self::$__mycolumns['alarm_minute']=array('int',"default 0"); // Num. time unit
			self::$__mycolumns['alarm_unit']=array('char(1)',"default '0'"); // Time unit 0-2 => minutes, hours, days
			self::$__mycolumns['before_event']=array('char(1)',"default '0'"); // 0=before event starts 1=after
//   flagEmailTo INTEGER, -- Bool - Manda notifica della sveglia a: IND.EMAIL
//    emailTo TEXT, -- Indirizzo email al quale notificare
			
			self::$__mycolumns['category']=array('varchar(255)',"default ''"); // Event category
			
			// Recurrence
			self::$__mycolumns['recurrence']=array('char(1)',"default '0'"); // Bool - Recurrence active?
			self::$__mycolumns['recurrence_type']=array('char(1)',"default '0'"); // 0=Daily, 1=Weekly, 2=monthly, 3=yearly
			// 0: daily
			self::$__mycolumns['daily_every_x']=array('int',"default 0"); // every_x_days
//  giorno_ogni_tot_giorni INTEGER, -- Ogni quanti giorni ripetere l'evento
			// 1: weekly
			self::$__mycolumns['weekly_every_x']=array('int',"default 0"); // every x weeks
			self::$__mycolumns['weekly_day_of_the_week']=array('char(1)',"default '0'"); // 0=monday ... 6=sunday
			// 2: monthly
			self::$__mycolumns['monthly_every_x']=array('int',"default 0"); // every x months
			//  1) n-th day of the month
			self::$__mycolumns['monthly_day_of_the_month']=array('int',"default 0"); // 0=do not, -5...-1,1 ... 31
			//  2) n-th week on monday
			self::$__mycolumns['monthly_week_number']=array('int',"default 0"); // 0=do not, 1...5
			self::$__mycolumns['monthly_week_day']=array('char(1)',"default '0'"); // 0=monday ... 6=sunday
			// 3: Yearly
			//  1) every day XX of month MM
			self::$__mycolumns['yearly_month_number']=array('int',"default 0"); // 0=do not, 1...12
			self::$__mycolumns['yearly_month_day']=array('int',"default 0"); // 0=do not, 1...31
			//  2) every first monday of june
			self::$__mycolumns['yearly_month_number']=array('int',"default 0"); // 0=do not, 1...12
			self::$__mycolumns['yearly_week_number']=array('int',"default 0"); // 0=do not 1...5
			self::$__mycolumns['yearly_week_day']=array('char(1)',"default '0'"); // 0=monday ... 6=sunday
			//  3) every n-th day of the year
			self::$__mycolumns['yearly_day_of_the_year']=array('int',"default 0"); // 0=do not, 1...366
			// Recurrence range
			self::$__mycolumns['recurrence_times']=array('int',"default 0"); // 0=always 1...N times
			// Recurrence until <date>
 			self::$__mycolumns['recurrence_end_date']=array('datetime',"not null default '0000-00-00 00:00:00'"); // 0=always 1...N times
//  -- Eccezioni alle regole di ricorrenza
//  eccezioni TEXT, -- Non applicarle in queste date, 'data1|data2|...|dataN'
		}
		$this->_columns=&self::$__mycolumns;
	}
	
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
//			$this->_fk[] = new ForeignKey('fk_obj_id','pages','id');
//			$this->_fk[] = new ForeignKey('father_id','pages','id');
			
			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','folders','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','people','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','projects','id');
		}
		return $this->_fk;
	}
}
$dbschema_type_list[]="DBEEvent";

class DBEFile extends DBEObject {
	var $_typeName="DBEFile";
	protected static $__mycolumns = null;
	
	var $dest_directory;
	
	function DBEFile($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $dest_directory=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['fk_obj_id']=array('uuid','default null');
			self::$__mycolumns['path']=array('text','default null');
			self::$__mycolumns['filename']=array('text','not null');
			self::$__mycolumns['checksum']=array('char(40)','default null');
			self::$__mycolumns['mime']=array('varchar(255)','default null');
			self::$__mycolumns['alt_link']=array('varchar(255)',"not null default ''",);
		}
		$this->_columns=&self::$__mycolumns;
		
		$this->dest_directory=$dest_directory==null ? $GLOBALS['files_directory'] : $dest_directory;
	}
	
	function getTableName() { return "files"; }
	
	function getOrderBy() {
		return array("path","filename");
	}
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			$this->_fk[] = new ForeignKey('father_id','folders','id');
			
			$this->_fk[] = new ForeignKey('fk_obj_id','pages','id');
			$this->_fk[] = new ForeignKey('father_id','pages','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','news','id');
			$this->_fk[] = new ForeignKey('father_id','news','id');
		}
		return $this->_fk;
	}
	
	function generaFilename($aId=null, $aFilename=null) {
		$nomefile = $aFilename==null?$this->getValue('filename'):$aFilename;
		$id=$aId==null?$this->getValue('id'):$aId;
		$prefisso = 'r_'.$id.'_';
		if(strpos($nomefile,$prefisso)!==false)
			$nomefile=str_replace($prefisso,"",$nomefile);
		return $prefisso.$nomefile;
	}
	function generaObjectPath($a_dbe=null) {
		$_dbe = $a_dbe!=null ? $a_dbe : $this;
		$dest_path = $_dbe->getValue('path')>'' ? $_dbe->getValue('path') : '';
		$father_id = $_dbe->getValue('father_id');
		if($father_id>0) $dest_path = $father_id.($dest_path>''?'/':'').$dest_path;
		return $dest_path;
	}
	function getFullpath($a_dbe=null) {
		$mydbe = $a_dbe!=null ? $a_dbe : $this;
		$dest_path = $mydbe->generaObjectPath();
		$dest_dir=realpath($GLOBALS['root_directory'].'/'.$mydbe->dest_directory);
		if($dest_path>'') $dest_dir.="/$dest_path";
		$ret = "$dest_dir/".$mydbe->getValue('filename');
		return $ret;
	}
	
	// Image management: start.
	function getThumbnailFilename() { return $this->getValue('filename')."_thumb.jpg"; }
	function isImage() { $_mime = $this->getValue('mime'); return $_mime>'' && substr($_mime,0,5)=='image'; }
	function createThumbnail($fullpath, $pix_width=100,$pix_height=100) {
		$gis = getimagesize($fullpath);
		$type = $gis[2];
		$imorig=null;
         if(!function_exists('imagecreatefromjpeg')) {
             echo "<h1>";
             echo "RUN:<br/>";
             echo "sudo aptitude install php5-gd</br>";
             echo "sudo /etc/init.d/apache2 restart<br/>";
             echo "</h1>";
         }
		switch($type) {
			case "1": $imorig = imagecreatefromgif($fullpath); break;
			case "2": $imorig = imagecreatefromjpeg($fullpath);break;
			case "3": $imorig = imagecreatefrompng($fullpath); break;
			default:  $imorig = imagecreatefromjpeg($fullpath);
		}
		$w = imagesx($imorig);
		$h = imagesy($imorig);
		$max_pixel = $w>$h ? $w : $h;
		$scale = $max_pixel / ($w>$h ? $pix_width : $pix_height);
		$pix_width = intval($w / $scale);
		$pix_height = intval($h / $scale);
		$im = imagecreatetruecolor($pix_width,$pix_height);
		if(imagecopyresampled($im,$imorig , 0,0,0,0,$pix_width,$pix_height,$w,$h)) {
			if(imagejpeg($im, $fullpath."_thumb.jpg")) {
				return $fullpath."_thumb.jpg";
			}
		}
		return "";
	}
	function deleteThumbnail($fullpath) {
		unlink($fullpath."_thumb.jpg");
	}
	// Image management: end.
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
		
		// Eredita la 'radice' dal padre
		$father_id = $this->getValue('father_id');
		if($father_id>0) {
			$query="select fk_obj_id from ". $dbmgr->buildTableName($this)." where id=".$this->getValue('father_id');
			$tmp = $dbmgr->select("DBE",$this->getTableName(),$query);
			if(count($tmp)==1) {
				$this->setValue('fk_obj_id', $tmp[0]->getValue('fk_obj_id'));
			}
		}
		
		// Aggiungo il prefisso al nome del file
		if($this->getValue('filename')>'') {
			$dest_path = $this->generaObjectPath();
			$from_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
			$dest_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
			if($dest_path>'') $dest_dir.="/$dest_path";
			if(!file_exists($dest_dir)) mkdir($dest_dir, 0755);
			// con basename() ottengo solo il nome del file senza il path relativo nel quale e' stato caricato
			$nuovo_filename = $this->generaFilename($this->getValue('id'), basename($this->getValue('filename')));
			rename($from_dir."/".$this->getValue('filename'), $dest_dir."/".$nuovo_filename);
			if(!($this->getValue('name')>'')) $this->setValue('name',basename($this->getValue('filename')));
			$this->setValue('filename', $nuovo_filename);
		}
		// Checksum
		$_fullpath = $this->getFullpath();
		if(file_exists($_fullpath)) {
			$newchecksum = sha1_file($_fullpath);
			$this->setValue('checksum',$newchecksum);
		} else {
			$this->setValue('checksum',"File '".$this->getValue('filename')."' not found!");
		}
		// Mime type
		if(file_exists($_fullpath)) {
			if(function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				if(!$finfo) {
					if(function_exists('mime_content_type'))
						$this->setValue('mime',mime_content_type($_fullpath));
					else
						$this->setValue('mime','text/plain');
					return;
				}
				$this->setValue('mime',finfo_file($finfo,$_fullpath));
				finfo_close($finfo);
			} elseif(function_exists('mime_content_type'))
				$this->setValue('mime',mime_content_type($_fullpath));
			else
				$this->setValue('mime','text/plain');
		} else {
			$this->setValue('mime','text/plain');
		}
		// Image
		if($this->isImage())
			$this->createThumbnail($_fullpath);
	}
	function _before_update(&$dbmgr) {
		parent::_before_update($dbmgr);
		
		// Eredita la 'radice' dal padre
		$father_id = $this->getValue('father_id');
		if($father_id>0) {
			$query="select fk_obj_id from ". $dbmgr->buildTableName($this)." where id=".$this->getValue('father_id');
			$tmp = $dbmgr->select("DBE",$this->getTableName(),$query);
			if(count($tmp)==1) {
				$this->setValue('fk_obj_id', $tmp[0]->getValue('fk_obj_id'));
			}
		}
		
		// Controllo se ho già un file salvato
		eval("\$cerca = new ".get_class($this)."();");
		$cerca->setValue('id', $this->getValue('id'));
		$tmp=$dbmgr->search($cerca,$uselike=0);
		$myself=$tmp[0];
		if($this->getValue('filename')>'' && $myself->getValue('filename')!=$this->getValue('filename')) {
			// Filename diversi ==> elimino il vecchio
			$dest_path = $myself->generaObjectPath();
			$dest_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
			if($dest_path>'') $dest_dir.="/$dest_path";
			$dest_file = $dest_dir."/".$myself->generaFilename();
			if(!file_exists($dest_file)) {
				// Do nothing
			} else {
				unlink($dest_file);
				// Image
				if($this->isImage())
					$this->deleteThumbnail($dest_file);
			}
		}
		
		// Aggiungo il prefisso al nome del file
		if($this->getValue('filename')>'') {
			$from_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
			$dest_path = $this->generaObjectPath();
			$dest_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
			if($dest_path>'') $dest_dir.="/$dest_path";
			if(!file_exists($dest_dir)) mkdir($dest_dir, 0755);
			$nuovo_filename = $this->generaFilename($this->getValue('id'), basename($this->getValue('filename')));
			rename("$from_dir/".$this->getValue('filename'),"$dest_dir/$nuovo_filename");
			$this->setValue('filename', $nuovo_filename);
		} else if($myself->getValue('path')!=$this->getValue('path')) {
			$from_path = $myself->generaObjectPath();
			$from_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
			if($from_path>'') $from_dir.="/$from_path";
			$dest_path = $this->generaObjectPath();
			$dest_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
			if($dest_path>'') $dest_dir.="/$dest_path";
			if(!file_exists($dest_dir)) mkdir($dest_dir, 0755);
			rename("$from_dir/".$myself->getValue('filename'),"$dest_dir/".$myself->getValue('filename'));
			// TODO controllare se funziona
			$this->setValue('filename', $myself->getValue('filename'));
		} else {
			// TODO controllare se funziona
			$this->setValue('filename', $myself->getValue('filename'));
		}
		// Checksum
		$_fullpath = $this->getFullpath();
		if(file_exists($_fullpath)) {
			$newchecksum = sha1_file($_fullpath);
			$this->setValue('checksum',$newchecksum);
		} else {
			$this->setValue('checksum',"File '".$this->getValue('filename')."' not found!");
		}
		// Mime type
		if(file_exists($_fullpath)) {
			if(function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				if(!$finfo) {
					if(function_exists('mime_content_type'))
						$this->setValue('mime',mime_content_type($_fullpath));
					else
						$this->setValue('mime','text/plain');
					return;
				}
				$this->setValue('mime',finfo_file($finfo,$_fullpath));
				finfo_close($finfo);
			} elseif(function_exists('mime_content_type'))
				$this->setValue('mime',mime_content_type($_fullpath));
			else
				$this->setValue('mime','text/plain');
		} else {
			$this->setValue('mime','text/plain');
		}
		// Image
		if($this->isImage())
			$this->createThumbnail($_fullpath);
	}
	function _before_delete(&$dbmgr) {
		// Has it been marked deleted before?
		$is_deleted = $this->isDeleted();
		parent::_before_delete($dbmgr);
		// If it has been marked deleted, then now is a REAL delete, so remove the file
		if($is_deleted) {
			// Controllo se ho già un file salvato
			$cerca = new DBEFile();
			$cerca->setValue('id', $this->getValue('id'));
			// BUGFIX 2012.04.04: start.
			$tmp=$dbmgr->search($cerca,0,false,null,false);
// 			$tmp=$dbmgr->search($cerca,$uselike=0);
			// BUGFIX 2012.04.04: end.
			if(count($tmp)>0) {
				$myself=$tmp[0];
				if($myself->getValue('filename')>'') {
					// ==> elimino il file
					$dest_path = $myself->generaObjectPath();
					$dest_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
					if($dest_path>'') $dest_dir.="/$dest_path";
					unlink($dest_dir."/".$myself->generaFilename());
					
					// Image
					if($this->isImage())
						$this->deleteThumbnail($dest_dir."/".$myself->generaFilename());
				}
			}
		}
	}
}
$dbschema_type_list[]="DBEFile";

class DBEFolder extends DBEObject {
	var $_typeName="DBEFolder";
	function getTableName() {
		return "folders";
	}
	protected static $__mycolumns = null;
	function DBEFolder($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $dest_directory=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['fk_obj_id']=array('uuid','default null');
			self::$__mycolumns['childs_sort_order']=array('text','default null');
		}
		$this->_columns=&self::$__mycolumns;
	}
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','people','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','projects','id');
		}
		return $this->_fk;
	}
	
	function setDefaultValues(&$dbmgr) {
		parent::setDefaultValues($dbmgr);
		
		if($this->getValue('father_id')===null) {
		} else {
			$cerca = new DBEFolder();
			$cerca->setValue('id',$this->getValue('father_id'));
			$lista = $dbmgr->search($cerca,$uselike=0);
			if(count($lista)==1) {
				$father = $lista[0];
				$this->setValue('fk_obj_id',$father->getValue('fk_obj_id'));
			}
		}
	}
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
		
		// Eredita la 'radice' dal padre
		$father_id = $this->getValue('father_id');
		if($father_id>0) {
			$query="select fk_obj_id from ". $dbmgr->buildTableName($this)." where id='".str_replace(' ','\0',sprintf("%-16s",$this->getValue('father_id')))."'";
			$tmp = $dbmgr->select("DBE",$this->getTableName(),$query);
			if(count($tmp)==1) {
				$this->setValue('fk_obj_id', $tmp[0]->getValue('fk_obj_id'));
			}
		}
	}
	function _before_update(&$dbmgr) {
		parent::_before_update($dbmgr);
		
		// Eredita la 'radice' dal padre
		$father_id = $this->getValue('father_id');
		if($father_id>0) {
			$query="select fk_obj_id from ". $dbmgr->buildTableName($this)." where id='".str_replace(' ','\0',sprintf("%-16s",$this->getValue('father_id')))."'";
			$tmp = $dbmgr->select("DBE",$this->getTableName(),$query);
			if(count($tmp)==1) {
				$this->setValue('fk_obj_id', $tmp[0]->getValue('fk_obj_id'));
			}
		}
	}
}
$dbschema_type_list[]="DBEFolder";

class DBELink extends DBEObject {
	var $_typeName="DBELink";
	function getTableName() { return "links"; }
	protected static $__mycolumns = null;
	function DBELink($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['href']=array('varchar(255)','not null');
			self::$__mycolumns['target']=array('varchar(255)',"default '_blank'");
			self::$__mycolumns['fk_obj_id']=array('uuid','default null');
		}
		$this->_columns=&self::$__mycolumns;
	}
	
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','folders','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','people','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','projects','id');

			$this->_fk[] = new ForeignKey('fk_obj_id','pages','id');
			$this->_fk[] = new ForeignKey('father_id','pages','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','news','id');
			$this->_fk[] = new ForeignKey('father_id','news','id');
		}
		return $this->_fk;
	}
	
}
$dbschema_type_list[]="DBELink";


class DBENote extends DBEObject {
	var $_typeName="DBENote";
	function getTableName() {
		return "notes";
	}
	protected static $__mycolumns = null;
	function DBENote($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['fk_obj_id']=array('uuid','default null');
		}
		$this->_columns=&self::$__mycolumns;
	}
	
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','folders','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','people','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','projects','id');
		}
		return $this->_fk;
	}
	
}
$dbschema_type_list[]="DBENote";

class DBEPage extends DBEObject {
	var $_typeName="DBEPage";
	function getTableName() { return "pages"; }
	protected static $__mycolumns = null;
	function DBEPage($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['html']=array('text','default null');
			self::$__mycolumns['fk_obj_id']=array('uuid','default null');
			self::$__mycolumns['language']=array('varchar(5)',"default 'en_us'");
		}
		$this->_columns=&self::$__mycolumns;
	}
	
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			
			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','folders','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','people','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','projects','id');
		}
		return $this->_fk;
	}
	
}
$dbschema_type_list[]="DBEPage";

class DBENews extends DBEPage {
	var $_typeName="DBENews";
	function getTableName() { return "news"; }
	protected static $__mycolumns = null;
	function DBENews($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEPage($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEPage::$__mycolumns;
// 			self::$__mycolumns['html']=array('text','default null');
		}
		$this->_columns=&self::$__mycolumns;
	}
	
	function getFK() {
		return parent::getFK();
	}
}
$dbschema_type_list[]="DBENews";
/** *********************************** CMS: end. *********************************** */


/** *********************************** RRA Projects: start. *********************************** */
class DBEProject extends DBEObject {
	var $_typeName="DBEProject";
	function getTableName() {
		return "projects";
	}
	function DBEProject($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys, DBEObject::$_mycolumns);
		
// 		$this->_columns['p_iva']=array('varchar(16)');
	}
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
// 			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
		}
		return $this->_fk;
	}
	/**
	 * @TODO da finire?
	 */
	function _before_delete(&$dbmgr) {
		parent::_before_delete($dbmgr);
		
		// Cancello i legami con le compagnie
		// Cancello i legami con le persone
		// Cancello i legami con i progetti
	}
}
$dbschema_type_list[]="DBEProject";
/**
 * Definisce i ruoli assunti dalle persone nei singoli progetti
 */
class DBEProjectCompanyRole extends DBEObject {
	var $_typeName="DBEProjectCompanyRole";
	protected static $__mycolumns = null;
	function DBEProjectCompanyRole($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);

		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['order_position']=array('int','default 0',);
		}
		$this->_columns=&self::$__mycolumns;
	}
	function getTableName() {
		return "projects_companies_roles";
	}
	protected static $__chiavi = array('id' => 'uuid');
	function getKeys() { return self::$__chiavi; }
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
// 			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
		}
		return $this->_fk;
	}
	function getOrderBy() { return array("order_position","id"); }
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
		
		$query="select max(order_position) as order_position from ". $dbmgr->buildTableName($this);
		$tmp = $dbmgr->select("DBE",$this->getTableName(),$query);
		if(count($tmp)==1) {
			$this->setValue('order_position', intval($tmp[0]->getValue('order_position')) + 1);
		} else {
			$this->setValue('order_position', 1);
		}
	}
}
$dbschema_type_list[]="DBEProjectCompanyRole";
/**
 * Associa le persone ai progetti con un certo ruolo
 */
class DBEProjectCompany extends DBAssociation {
	var $_typeName="DBEProjectCompany";
	function getTableName() { return "projects_companies"; }
	protected static $__mycolumns = array(
			'project_id'=>array('uuid',"not null default ''"),
			'company_id'=>array('uuid',"not null default ''"),
			'projects_companies_role_id'=>array('uuid',"not null default ''"),
			);
	function DBEProjectCompany($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBAssociation($tablename, $names, $values, $attrs, $keys, self::$__mycolumns);
	}
	
	// Statica
	var $_chiavi = array('project_id' => 'uuid', 'company_id'=>'uuid', 'projects_companies_role_id'=>'uuid');
	function getKeys() { return $this->_chiavi; }
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			$this->_fk[] = new ForeignKey('project_id','projects','id'); // From
			$this->_fk[] = new ForeignKey('company_id','companies','id'); // To
			$this->_fk[] = new ForeignKey('projects_companies_role_id','projects_companies_roles','id'); // Attribute
		}
		return $this->_fk;
	}
}
$dbschema_type_list[]="DBEProjectCompany";
/**
 * Definisce i ruoli assunti dalle persone nei simgoli progetti
 */
class DBEProjectPeopleRole extends DBAssociation {
	var $_typeName="DBEProjectPeopleRole";
	protected static $__mycolumns = null;
	function getTableName() {
		return "projects_people_roles";
	}
	function DBEProjectPeopleRole($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBAssociation($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['order_position']=array('int','default 0');
		}
		$this->_columns=&self::$__mycolumns;
	}
	protected static $_chiavi = array('id' => 'uuid');
	function getKeys() {
		return self::$_chiavi;
	}
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
// 			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
		}
		return $this->_fk;
	}
	function getOrderBy() { return array("order_position","id"); }
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
		
		$query="select max(order_position) as order_position from ". $dbmgr->buildTableName($this);
		$tmp = $dbmgr->select("DBE",$this->getTableName(),$query);
		if(count($tmp)==1) {
			$this->setValue('order_position', intval($tmp[0]->getValue('order_position')) + 1);
		} else {
			$this->setValue('order_position', 1);
		}
	}
}
$dbschema_type_list[]="DBEProjectPeopleRole";
/**
 * Associa le persone ai progetti con un certo ruolo
 */
class DBEProjectPeople extends DBAssociation {
	var $_typeName="DBEProjectPeople";
	function getTableName() { return "projects_people"; }
	protected static $__mycolumns = array(
				'project_id'=>array('uuid',"not null default ''"),
				'people_id'=>array('uuid',"not null default ''"),
				'projects_people_role_id'=>array('uuid',"not null default ''"),
			);
	function DBEProjectPeople($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBAssociation($tablename, $names, $values, $attrs, $keys, self::$__mycolumns);
	}
	
	// Statica
	var $_chiavi = array('project_id' => 'uuid', 'people_id'=>'uuid', 'projects_people_role_id'=>'uuid');
	function getKeys() { return $this->_chiavi; }
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			$this->_fk[] = new ForeignKey('project_id','projects','id'); // From
			$this->_fk[] = new ForeignKey('people_id','people','id'); // To
			$this->_fk[] = new ForeignKey('projects_people_role_id','projects_people_roles','id'); // Attribute
		}
		return $this->_fk;
	}
}
$dbschema_type_list[]="DBEProjectPeople";
/**
 * Definisce i ruoli assunti dalle persone nei simgoli progetti
 */
class DBEProjectProjectRole extends DBEObject {
	var $_typeName="DBEProjectProjectRole";
	function getTableName() {
		return "projects_projects_roles";
	}
	protected static $__mycolumns = null;
	function DBEProjectProjectRole($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		if(self::$__mycolumns===null) {
			self::$__mycolumns=DBEObject::$_mycolumns;
			self::$__mycolumns['order_position']=array('int',"default 0");
		}
		$this->_columns=&self::$__mycolumns;
	}
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
// 			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
		}
		return $this->_fk;
	}
	function getOrderBy() { return array("order_position","id"); }
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
		
		$query="select max(order_position) as order_position from ". $dbmgr->buildTableName($this);
		$tmp = $dbmgr->select("DBE",$this->getTableName(),$query);
		if(count($tmp)==1) {
			$this->setValue('order_position', intval($tmp[0]->getValue('order_position')) + 1);
		} else {
			$this->setValue('order_position', 1);
		}
	}
}
$dbschema_type_list[]="DBEProjectProjectRole";
/**
 * Associa le persone ai progetti con un certo ruolo
 */
class DBEProjectProject extends DBAssociation {
	var $_typeName="DBEProjectProject";
	function getTableName() { return "projects_projects"; }
	protected static $__mycolumns = array(
				'project_id'=>array('uuid',"not null default ''"),
				'project2_id'=>array('uuid',"not null default ''"),
				'projects_projects_role_id'=>array('uuid',"not null default ''"),
			);
	function DBEProjectProject($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBAssociation($tablename, $names, $values, $attrs, $keys, self::$__mycolumns);
	}
	
	// Statica
	var $_chiavi = array('project_id' => 'uuid', 'project2_id'=>'uuid', 'projects_projects_role_id'=>'uuid');
	function getKeys() { return $this->_chiavi; }
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			$this->_fk[] = new ForeignKey('project_id','projects','id'); // From
			$this->_fk[] = new ForeignKey('project2_id','projects','id'); // To
			$this->_fk[] = new ForeignKey('projects_projects_role_id','projects_projects_roles','id'); // Attribute
		}
		return $this->_fk;
	}
}
$dbschema_type_list[]="DBEProjectProject";

class DBETimetrack extends DBEObject {
	var $_typeName="DBETimetrack";
	function getTableName() {
		return "timetracks";
	}
	function DBETimetrack($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		$this->_columns['fk_obj_id']=array('uuid','default null');
		$this->_columns['fk_progetto']=array('uuid','default null');
		
		$this->_columns['dalle_ore']=array('datetime',"not null default '0000-00-00 00:00:00'");
		$this->_columns['alle_ore']=array('datetime',"not null default '0000-00-00 00:00:00'");
		$this->_columns['ore_intervento']=array('datetime',"not null default '0000-00-00 00:00:00'");
		$this->_columns['ore_viaggio']=array('datetime',"not null default '0000-00-00 00:00:00'");
		$this->_columns['km_viaggio']=array('int','not null default 0');
		$this->_columns['luogo_di_intervento']=array('int','not null default 0');
		$this->_columns['stato']=array('int','not null default 0');
		$this->_columns['costo_per_ora']=array('float','not null default 0');
		$this->_columns['costo_valuta']=array('varchar(255)',"default null");
	}
	
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			$this->_fk[] = new ForeignKey('fk_obj_id','projects','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','folders','id');
			$this->_fk[] = new ForeignKey('fk_obj_id','todo','id');
			
			$this->_fk[] = new ForeignKey('fk_progetto','projects','id');
		}
		return $this->_fk;
	}
	
	function getOrderBy() { return array('fk_progetto','stato desc','dalle_ore desc','fk_obj_id','name'); }
	
	function getFKCGIConditionFromMaster(&$dbe_master) {
		$ret=array();
		$ret[] = parent::getFKCGIConditionFromMaster($dbe_master);
		// Recupero la fk verso i progetti
		$fks = $dbe_master->getFKForTable('projects');
		$myfks = $this->getFKForTable('projects');
		for($m=0; $m<count($fks); $m++) {
			$master_value = $dbe_master->getValue($fks[$m]->colonna_fk);
			if($master_value!=null) {
				for($f=0; $f<count($myfks); $f++) {
					// Controllo che non sia già presente un'altra clausola in $ret
					$trovata = false;
					$stringa_nome = "field_" . $myfks[$f]->colonna_fk;
					$lunghezza_nome = strlen("field_" . $myfks[$f]->colonna_fk);
					for($i=0; !$trovata && $i<count($ret); $i++) {
						$trovata = !(strpos($ret[$i],$stringa_nome)===false);
					}
					// SE trovata => non aggiungere?
					if(!$trovata)
					    $ret[] = "field_" . $myfks[$f]->colonna_fk . "=" . $master_value;
				}
			}
		}
		return join($ret, "&");
	}
	
}
$dbschema_type_list[]="DBETimetrack";

class DBETodo extends DBEObject {
	var $_typeName="DBETodo";
	function getTableName() {
		return "todo";
	}
	function DBETodo($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		$this->_columns['priority']=array('int','not null default 0');
		$this->_columns['data_segnalazione']=array('datetime',"not null default '0000-00-00 00:00:00'");
		$this->_columns['fk_segnalato_da']=array('uuid','default null');
		$this->_columns['fk_cliente']=array('uuid','default null');
		$this->_columns['fk_progetto']=array('uuid','default null');
		$this->_columns['fk_funzionalita']=array('uuid','default null');
		$this->_columns['fk_tipo']=array('uuid','default null');
		$this->_columns['stato']=array('int','not null default 0');
		$this->_columns['descrizione']=array('text','not null');
		$this->_columns['intervento']=array('text','not null');
		$this->_columns['data_chiusura']=array('datetime',"not null default '0000-00-00 00:00:00'");
	}
	
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
			$this->_fk[] = new ForeignKey('fk_segnalato_da','people','id');
			$this->_fk[] = new ForeignKey('fk_cliente','companies','id');
			$this->_fk[] = new ForeignKey('fk_progetto','projects','id');
			
			$this->_fk[] = new ForeignKey('father_id','folders','id');
			$this->_fk[] = new ForeignKey('father_id','todo','id');
			
			$this->_fk[] = new ForeignKey('fk_tipo','todo_tipo','id');
		}
		return $this->_fk;
	}
	function getOrderBy() { return array('priority desc','data_segnalazione desc','fk_progetto','name'); }
	
	function getFKCGIConditionFromMaster(&$dbe_master) {
		$ret=array();
		$ret[] = parent::getFKCGIConditionFromMaster($dbe_master);
		// Recupero la fk verso i progetti
		$fks = $dbe_master->getFKForTable('projects');
		$myfks = $this->getFKForTable('projects');
		if(count($fks)==1 && count($myfks)==1) {
			$master_value = $dbe_master->getValue($fks[0]->colonna_fk);
			if($master_value!=null) {
				// Controllo che non sia già presente un'altra clausola in $ret
				$trovata = false;
				$stringa_nome = "field_" . $myfks[0]->colonna_fk;
				$lunghezza_nome = strlen("field_" . $myfks[0]->colonna_fk);
				for($i=0; !$trovata && $i<count($ret); $i++)
					$trovata = substr($ret[$i],0,$lunghezza_nome)==$stringa_nome;
				// SE trovata => non aggiungere?
				if(!$trovata)
				    $ret[] = "field_" . $myfks[0]->colonna_fk . "=" . $master_value;
			}
		}
		return join($ret, "&");
	}
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
		
		$data_segnalazione = $this->getValue('data_segnalazione');
		if($data_segnalazione==null || $data_segnalazione=="" || $data_segnalazione=="00:00" || $data_segnalazione=="0000/00/00 00:00") {
			$this->setValue('data_segnalazione', $this->_getTodayString());
		}
		
		$this->_RULE_controllo_chiusura();
	}
	
	function _before_update(&$dbmgr) {
		parent::_before_update($dbmgr);
		
		// Controllo stato=100%
		$this->_RULE_controllo_chiusura();
		// SE stato<100 ==> data_chiusura=null
		$this->_RULE_controllo_riapertura();
	}
	
	/**
	 * SE data_chiusura non valorizzata E stato=100% ==> data_chiusura=oggi
	 */
	function _RULE_controllo_chiusura() {
		$data_chiusura = $this->getValue('data_chiusura');
		if($data_chiusura==null || $data_chiusura=="" || $data_chiusura=="0000/00/00 00:00") {
			$stato = $this->getValue('stato');
			if(is_numeric($stato) && intval($stato)>=100) {
					$this->setValue('data_chiusura', $this->_getTodayString());
					$this->setValue('stato', 100);
			}
		}
	}
	/**
	 * SE data_chiusura valorizzata E stato<100% => data_chiusura=null
	 */
	function _RULE_controllo_riapertura() {
		$data_chiusura = $this->getValue('data_chiusura');
		if($data_chiusura==null || $data_chiusura=="" || $data_chiusura=="0000/00/00 00:00") {
		} else {
			$stato = $this->getValue('stato');
			if(is_numeric($stato) && intval($stato)<100) {
					$this->setValue('data_chiusura', '');
			}
		}
	}
}
$dbschema_type_list[]="DBETodo";
/**
 * Todo::Tipo
 */
class DBETodoTipo extends DBEObject {
	var $_typeName="DBETodoTipo";
	function getTableName() {
		return "todo_tipo";
	}
	function DBETodoTipo($tablename=null, $names=null, $values=null, $attrs=null, $keys=null) {
		$this->DBEObject($tablename, $names, $values, $attrs, $keys);
		
		$this->_columns['order_position']=array('int','default 0');
	}
	function getFK() {
		if($this->_fk==null) {
			$this->_fk=array();
		}
		if(count($this->_fk)==0) {
			parent::getFK();
// 			$this->_fk[] = new ForeignKey('fk_obj_id','companies','id');
		}
		return $this->_fk;
	}
	function getOrderBy() { return array("order_position","id"); }
	
	function _before_insert(&$dbmgr) {
		parent::_before_insert($dbmgr);
		
		$query="select max(order_position) as order_position from ". $dbmgr->buildTableName($this);
		$tmp = $dbmgr->select("DBE",$this->getTableName(),$query);
		if(count($tmp)==1) {
			$this->setValue('order_position', intval($tmp[0]->getValue('order_position')) + 1);
		} else {
			$this->setValue('order_position', 1);
		}
	}
}
$dbschema_type_list[]="DBETodoTipo";
/** *********************************** RRA Projects: end. *********************************** */


/** *********************************** DBLayer Cpp test classes: start. *********************************** */
class DBETestDBLayer extends DBEntity {
    var $_typeName='DBETestDBLayer';
    public static $_mycolumns = array(
                'id'=>array('uuid','not null'),
                'nome'=>array('varchar(255)'),
                'descrizione'=>array('varchar(2000)'),
                'abilitato'=>array('char(1)'),
                'data_creazione'=>array('datetime'),
                'prezzo'=>array('float'),
                'data_disponibilita'=>array('datetime'),
            );
    function DBETestDBLayer($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=null) {
        $this->DBEntity($tablename, $names, $values, $attrs, $keys, $columns!==null ? $columns : self::$_mycolumns);
    }
    function getSchemaName() { return "test"; }
    function getTableName() { return "test_dblayer"; }
    
    // Statica
    var $_chiavi = array('id' => 'uuid');
    function getKeys() { return $this->_chiavi; }
    function getOrderBy() { return array("nome"); }
    
    function _before_insert(&$dbmgr) {
        $myid = $dbmgr->getNextUuid($this);
        $this->setValue('id', $myid);
    }
}
$dbschema_type_list[]='DBETestDBLayer';
class DBESocieta extends DBEntity {
    var $_typeName='DBESocieta';
    public static $_mycolumns = array(
                'id'=>array('uuid','not null'),
                'ragione_sociale'=>array('text'),
                'indirizzo'=>array('text'),
                'cap'=>array('varchar(6)'),
                'nazione'=>array('text'),
                'telefono'=>array('text'),
                'fax'=>array('text'),
                'email'=>array('text'),
                'note'=>array('text'),
                'website'=>array('text'),
                'citta'=>array('text'),
                'provincia'=>array('text'),
                'partita_iva'=>array('text'),
                'tipo'=>array('text'),
                'data_creazione'=>array('datetime'),
            );
    function DBESocieta($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=null) {
        $this->DBEntity($tablename, $names, $values, $attrs, $keys, $columns!==null ? $columns : self::$_mycolumns);
    }
    function getSchemaName() { return "test"; }
    function getTableName() { return "societa"; }
    
    // Statica
    var $_chiavi = array('id' => 'uuid');
    function getKeys() { return $this->_chiavi; }
    function getOrderBy() { return array("ragione_sociale"); }
    
    function _before_insert(&$dbmgr) {
        $myid = $dbmgr->getNextUuid($this);
        $this->setValue('id', $myid);
    }
}
$dbschema_type_list[]='DBESocieta';
/** *********************************** DBLayer Cpp test classes: end. *********************************** */


/** *********************************** DBEFactory *********************************** */

class MyDBEFactory extends DBEFactory {
	function MyDBEFactory($verbose = 0) {
		$this->DBEFactory($verbose);
		
		global $dbschema_type_list;
		foreach($dbschema_type_list as $mytype)
			$this->register($mytype);
	}
}

?>
