<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: formschema.php $
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

require_once(ROOT_FOLDER . "config.php");
require_once(ROOT_FOLDER . "formulator/formulator.php");

$oggi_array = getdate(); //localtime(time(),true);
$oggi = $oggi_array['year'] . "/" . ( strlen($oggi_array['mon'])<2 ? "0" : "" ) . $oggi_array['mon'] . "/" . ( strlen($oggi_array['mday'])<2 ? "0" : "" ) . $oggi_array['mday'] . " " . ( strlen($oggi_array['hours'])<2 ? "0" : "" ) . $oggi_array['hours'] . ":" . ( strlen($oggi_array['minutes'])<2 ? "0" : "" ) . $oggi_array['minutes'];

global $root_directory;

$formschema_type_list=array();


/** *********************************** RRA Framework: inizio. *********************************** */
class FUser extends FMasterDetail {
	function FUser( $nome='', $azione='', $metodo="POST" ) {
		parent::FMasterDetail( $nome, $azione, $metodo);
		
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'id', new FNumber( 'id', "ID", '', $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'login', new FString( 'login', "Login", 'Inserire la login', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'pwd', new FPassword( 'pwd', "Password", 'Inserire la login', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$array_valori=array(''=>'-','md5'=>'MD5','sha1'=>'SHA1');
		$this->addField('', -1, 'pwd_salt', new FList( 'pwd_salt', "Password encrypting", 'Password encrypt saalt', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori, $altezza=1, $multiselezione=false ));
		$this->addField('', -1, 'fullname', new FString( 'fullname', "Full Name", 'Inserire la login', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		
		$this->addField('', -1, 'group_id',
									new FKField( 'group_id', "Group", 'Active group.', $aSize=50, $aValore='-4', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('group_id'), $description_columns=array('name'), $destform="FGroup", $viewmode='select'  ));
		
		$this->addDetail("FUserGroupAssociation");
		$this->addDetail("FPeople","1");
	}
	function getDetailIcon() { return "icons/user.png"; }
	function getDetailTitle() { return "User"; }
	function getDetailColumnNames() { return array('login','pwd','pwd_salt','fullname','group_id'); }
	function getFilterForm() { return new FUser; }
	function getFilterFields() { return array('login','fullname','group_id'); }
	function getListTitle() { return "Users"; }
	function getListColumnNames() { return array('id','login','fullname','group_id'); }
	function getPagePrefix() { return "dbe"; }
	function getDBE() { return new DBEUser(); }
	function getShortDescription($dbmgr = NULL) { return $this->getValue('fullname'); }
	function getValues() {
		$ret = array();
		foreach ( $this->getFieldNames() as $nomeCampo ) {
			$ret[ $nomeCampo ] = $this->fields[ $nomeCampo ]->getValue();
		}
		return $ret;
	}

}
$formschema_type_list[]="FUser";
class FGroup extends FMasterDetail {
	function FGroup( $nome='', $azione='', $metodo="POST" ) {
		parent::FMasterDetail( $nome, $azione, $metodo);
		
		$this->addField('', -1, 'id', new FNumber( 'id', "ID", '', $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'name', new FString( 'name', "Name", 'Nome del gruppo.', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'description', new FTextArea( 'description', "Description", 'Descrizione del gruppo.', 255, '', $aClasseCss='formtable', 50, 5));
		
		$this->addDetail("FUserGroupAssociation");
	}
	function getDetailIcon() { return "icons/group_16x16.gif"; }
	function getDetailTitle() { return "Group"; }
	function getDetailColumnNames() { return array('name', 'description'); }
	function getFilterForm() { return new FGroup; }
	function getFilterFields() { return array('name','description'); }
	function getListTitle() { return "Groups"; }
	function getListColumnNames() { return array('id','name','description'); }
	function getPagePrefix() { return "dbe"; }
	function getDBE() { return new DBEGroup(); }
	function getShortDescription($dbmgr = NULL) { return $this->getValue('name'); }
}
$formschema_type_list[]="FGroup";
class FUserGroupAssociation extends FAssociation {
	function FUserGroupAssociation($nome='', $azione='', $metodo="POST") {
		$this->FAssociation(new DBEUserGroup(), null, null, $nome, $azione, $metodo);
	}
	function getDetailTitle() { return "Association User-Group"; }
	function getFromForm() { return FForm::getInstance("FUser"); } // 2011.04.04 new FUser(); }
	function getToForm() { return FForm::getInstance("FGroup"); } // 2011.04.04 new FGroup(); }
}
$formschema_type_list[]="FUserGroupAssociation";
class FLog extends FMasterDetail {
	function FLog( $nome='', $azione='', $metodo="POST" ) {
		parent::FMasterDetail( $nome, $azione, $metodo);
		
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'ip', new FString( 'ip', "IP", '', $aSize=16, $aLength=16, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'data', new FDateTime( 'data', "Date", $aDescription='', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ));
		$this->addField('', -1, 'ora', new FDateTime( 'ora', "Hour", $aDescription='', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=false, $aVisualizzaOra=true ));
		$this->addField('', -1, 'count', new FNumber( 'count', "Count", '', $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'url', new FString( 'url', "URL", '', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'note', new FString( 'note', "Note", '', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'note2', new FTextArea( 'note2', "Note 2", '', 255, '', $aClasseCss='formtable', 50, 5));
		
		// Valori di defaults per la ricerca
		$this->setValue('data',getTodayString($with_time=false,$date_separator="-"));
	}
	function getDetailIcon() { return "icons/text-x-log.png"; }
	function getDetailTitle() { return "Log"; }
	function getDetailColumnNames() { return array('data','ora','ip','count','url','note','note2'); }
	function getDetailReadOnlyColumnNames() { return array('data','ora','ip',); }
	function getFilterForm() { return FForm::getInstance("FLogFilter"); } // 2011.04.04 new FLogFilter; }
	function getFilterFields() { return array('ip','from_data','to_data','count','url','note'); }
	function getListTitle() { return "Log"; }
	function getListColumnNames() { return array('data','ora','count','ip','url','note'); }
	function getPagePrefix() { return "dbe"; }
	function getDBE() { return new DBELog(); }
	function getShortDescription($dbmgr = NULL) { return $this->getValue('ip'); }
	/**
	 * Returns possible actions on one instance of the managed data type (dbe).
	 * Returned array is like:
	 *	action_code => array(
	 *		'label'=>'Action label',
	 *		'page'=>'list_action_page_do.php',
	 *		'icon'=>'actionIcon.jpg',
	 *		'desc'=>'Short action description',
	 *		'js'=>'javascript function',
	 *		'onclick'=>'javascript code')
	 */
// 	function getActions() { return array(); }
	/**
	 * Returns possible actions on a list of the managed data type (dbe).
	 * Returned array is like:
	 *	action_code => array(
	 *		'label'=>'Action label',
	 *		'page'=>'list_action_page_do.php',
	 *		'icon'=>'actionIcon.jpg',
	 *		'desc'=>'Short action description',
	 *		'js'=>'javascript function',
	 *		'onclick'=>'javascript code')
	 */
	function getListActions() { return array(
			'optimize' => array(
				'label'=>'Optimize',
				'page'=>'log_optimize_do.php',
// 				'icon'=>'mng/icone/SaveAll16.gif',
				'desc'=>'Optimize the table'),
			'autocomplete' => array(
				'label'=>'Auto Complete',
				'page'=>'log_autocomplete_do.php',
				'icon'=>null,
				'desc'=>'Auto complete empty URL field with known addresses'),
			'autofill' => array(
				'label'=>'Auto Fill',
				'page'=>'log_autofill_do.php',
				'icon'=>null,
				'desc'=>'Fills empty url fields with non-empty values in the log table'),
		); }
}
$formschema_type_list[]="FLog";
/**
 * Classe di filtro per i TODO
 */
class FLogFilter extends FLog {
	function getDetailTitle() { return "Log Filter"; }
	function FLogFilter( $nome='', $azione='', $metodo="POST" ) {
		parent::FLog( $nome, $azione, $metodo);
		
		$this->addField('', -1, 'from_ip', new FString( 'from_ip', "IP >=", '', $aSize=16, $aLength=16, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'to_ip', new FString( 'to_ip', "IP <=", '', $aSize=16, $aLength=16, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'from_data', new FDateTime( 'from_data', "Date >=", $aDescription='Date from', $aValore='', $aClasseCss='formtable', $aVisualizzaData=TRUE, $aVisualizzaOra=false ));
		$this->addField('', -1, 'to_data', new FDateTime( 'to_data', "Date <=", $aDescription='Date to', $aValore='', $aClasseCss='formtable', $aVisualizzaData=TRUE, $aVisualizzaOra=false ));
		
		// Valori di defaults per la ricerca
		$this->setValue('from_data',getTodayString($with_time=false,$date_separator="-"));
		$this->setValue('to_data',getTodayString($with_time=false,$date_separator="-"));
	}
	function getFilterFields() { return array('from_ip','to_ip','from_data','to_data','url','note'); }
}
$formschema_type_list[]="FLogFilter";

class FObject extends FMasterDetail {
	function FObject( $nome='', $azione='', $metodo="POST" ) {
		parent::FMasterDetail( $nome, $azione, $metodo);
		
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'id', new FUuid( 'id', "ID", '', $aValore='', $aClasseCss='formtable' ));
		$this->addField('_permission', -1, 'owner',
									new FKField( 'owner', "Owner", 'Proprietario.', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('owner'), $description_columns=array('login'), $destform="FUser", $viewmode='select'  ));
		$this->addField('_permission', -1, 'group_id',
									new FKField( 'group_id', "Group", 'Active group.', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('group_id'), $description_columns=array('name'), $destform="FGroup", $viewmode='select'  ));
		$this->addField('_permission', -1, 'permissions', new FPermissions( 'permissions', "Permissions", 'Unix style', $aSize=9, $aLength=9, $aValore='rwx------', $aClasseCss='formtable' ));

		$this->addField('', -1, 'creator',
									new FKField( 'creator', "Created by", 'Creatore', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('creator'), $description_columns=array('login'), $destform="FUser", $viewmode='readonly'  ));
		$this->addField('', -1, 'creation_date', new FDateTimeReadOnly( 'creation_date', "Created on", $aDescription='Creato il', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=TRUE, $aVisualizzaOra=TRUE ));
		$this->addField('', -1, 'last_modify',
									new FKField( 'last_modify', "Modified by", 'Modificato da', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('last_modify'), $description_columns=array('login'), $destform="FUser", $viewmode='readonly'  ));
		$this->addField('', -1, 'last_modify_date', new FDateTimeReadOnly( 'last_modify_date', "Modified on", $aDescription='Ultima modifica', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=TRUE, $aVisualizzaOra=TRUE ));
		$this->addField('', -1, 'deleted_by',
									new FKField( 'deleted_by', "Deleted by", 'Deleted by', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('deleted_by'), $description_columns=array('login'), $destform="FUser", $viewmode='readonly'  ));
		$this->addField('', -1, 'deleted_date', new FDateTimeReadOnly( 'deleted_date', "Deleted on", $aDescription='Deleted on', $aValore='0000-00-00 00:00:00', $aClasseCss='formtable', $aVisualizzaData=TRUE, $aVisualizzaOra=TRUE ));
		
		$this->addField('', -1, 'father_id',
									new FKObjectField( 'father_id', "Parent", 'Padre', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('father_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		
		$this->addField('', -1, 'name', new FString( 'name', "Name", 'Nome del gruppo.', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'description', new FTextArea( 'description', "Description", 'Descrizione del gruppo.', 255, '', $aClasseCss='formtable', 50, 5));
	}
	function getDetailTitle() { return "Object"; }
	function getDetailColumnNames() { return array(
		'id',
		'name','description',
		'owner','group_id','permissions',
// 		'creator','creation_date','last_modify','last_modify_date',
		); }
	function getDetailReadOnlyColumnNames() { return array(
		'id',
		'creator','creation_date','last_modify','last_modify_date',
		); }
/*	function getViewColumnNames() { return array(
		'name','description',
		); }*/
	function getListTitle() { return "Objects"; }
	function getListColumnNames() { return array('id','name','description'); }
	function getDecodeGroupNames() { return array("_permission"=>"Permissions",); }
	function getPagePrefix() { return "dbe"; }
	function getDBE() { return new DBEObject(); }
	function getShortDescription($dbmgr = NULL) { return $this->getValue('name'); }
	function getActions() { return array(
		'reload'=>array(
				'label'=>'Reload',
				'page'=>'obj_reload_do.php',
				'icon'=>'icons/reload.png',
				'desc'=>'Reload'),
/*		'save'=>array(
				'label'=>'Save',
				'page'=>'obj_save_do.php',
				'icon'=>'icons/filesave.png',
				'desc'=>'Save'),*/
		);
	}
	
}
$formschema_type_list[]="FObject";
/** *********************************** RRA Framework: fine. *********************************** */


/** *********************************** RRA Contacts: inizio. *********************************** */

class FCompany extends FObject {
	function FCompany( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		
		$_mydbe = $this->getDBE();
		
		$this->addField('', -1, 'p_iva', new FString( 'p_iva', "P.IVA", 'Partita IVA', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		
		$this->addField('phone', -1, 'phone', new FString( 'phone', "Phone", 'Telefono', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('phone', -1, 'fax', new FString( 'fax', "Fax", 'Fax', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		
		$this->addField('web', -1, 'email', new FString( 'email', "Email", 'Email', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('web', -1, 'url', new FString( 'url', "URL", 'Sito web', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		
		$this->addField('address', -1, 'street', new FString( 'street', "Street", 'Via', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('address', -1, 'zip', new FString( 'zip', "ZIP", 'CAP', $aSize=10, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('address', -1, 'city', new FString( 'city', "City", 'Città.', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('address', -1, 'state', new FString( 'state', "State", 'Provincia.', $aSize=20, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('address', -1, 'fk_countrylist_id',
									new FKField( 'fk_countrylist_id', "Country", 'Nazione.', $aSize=50, $aValore=82, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_countrylist_id'), $description_columns=array('Common_Name'), $destform=null, $viewmode='select'  ));
		
		$this->addDetail("FFolder");
		$this->addDetail("FProjectCompany");
		$this->addDetail("FPeople");
		$this->addDetail("FPage");
		$this->addDetail("FLink");
		$this->addDetail("FNote");
	}
	function getDetailIcon() { return "icons/company_16x16.gif"; }
	function getDetailTitle() { return "Company"; }
	function getDetailColumnNames() { return array('name','p_iva',
			'street','zip','city','state','fk_countrylist_id',
			'phone','fax',
			'email','url',
			'owner','group_id','permissions',
			); }
	function getFilterForm() { return new FCompany; }
	function getFilterFields() { return array('name','p_iva','zip','city','fk_countrylist_id',); }
	function getListTitle() { return "Companies"; }
	function getListColumnNames() { return array(
		'name','phone',
// 		'email',
		'city','fk_countrylist_id');
	}
	function getDecodeGroupNames() { return array_merge( parent::getDecodeGroupNames(),
										array("phone"=>"Phone", "address"=>"Address", "web"=>"Web" )); }
	function getDBE() { return new DBECompany(); }
}
$formschema_type_list[]="FCompany";
class FPeople extends FObject {
	function FPeople( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		
		$_mydbe = $this->getDBE();
		
		$this->addField('', -1, 'fk_users_id',
									new FKField( 'fk_users_id', "User", 'Utente.', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_users_id'), $description_columns=array('login'), $destform="FUser", $viewmode='select'  ));
		$this->addField('', -1, 'fk_companies_id',
									new FKField( 'fk_companies_id', "Company", 'Organizzazione.', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_companies_id'), $description_columns=array('name','state'), $destform="FCompany", $viewmode='select'  ));
		$this->addField('', -1, 'codice_fiscale', new FString( 'codice_fiscale', "Codice Fiscale", 'Codice Fiscale', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'p_iva', new FString( 'p_iva', "P.IVA", 'Partita IVA', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		
		$this->addField('phone', -1, 'office_phone', new FString( 'office_phone', "Office Phone", 'Telefono Ufficio', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('phone', -1, 'mobile', new FString( 'mobile', "Mobile", 'Cellulare', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('phone', -1, 'phone', new FString( 'phone', "Phone", 'Telefono', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('phone', -1, 'fax', new FString( 'fax', "Fax", 'Fax', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		
		$this->addField('address', -1, 'street', new FString( 'street', "Street", 'Via', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('address', -1, 'zip', new FString( 'zip', "ZIP", 'CAP', $aSize=10, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('address', -1, 'city', new FString( 'city', "City", 'Città.', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('address', -1, 'state', new FString( 'state', "State", 'Provincia.', $aSize=20, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('address', -1, 'fk_countrylist_id',
									new FKField( 'fk_countrylist_id', "Country", 'Nazione.', $aSize=50, $aValore=82, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_countrylist_id'), $description_columns=array('Common_Name'), $destform=null, $viewmode='select'  ));
		
		$this->addField('web', -1, 'email', new FString( 'email', "Email", 'Email', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('web', -1, 'url', new FString( 'url', "URL", 'Sito web', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		
		$this->addDetail("FFolder");
		$this->addDetail("FProjectPeople");
		$this->addDetail("FPage");
		$this->addDetail("FLink");
		$this->addDetail("FNote");
	}
	function getDetailIcon() { return "icons/people.png"; }
	function getDetailTitle() { return "Person"; }
	function getDetailColumnNames() {
		return array('name','fk_users_id','fk_companies_id','codice_fiscale','p_iva',
			'street','zip','city','state','fk_countrylist_id',
			'phone','office_phone','mobile','fax',
			'email','url',
			'owner','group_id','permissions',
			); }
	function getFilterForm() { $ret = new FPeople; $ret->setValue('fk_countrylist_id',null); return $ret; }
	function getFilterFields() { return array('name','zip','city','fk_countrylist_id','fk_companies_id',); }
	function getListTitle() { return "People"; }
	function getListColumnNames() { return array('name','phone','email','fk_companies_id'); }
	function getDecodeGroupNames() { return array_merge( parent::getDecodeGroupNames(),
										array("phone"=>"Phone", "address"=>"Address", "web"=>"Web" )); }
	function getDBE() {
		return new DBEPeople();
	}
}
$formschema_type_list[]="FPeople";

/** *********************************** RRA Contacts: fine. *********************************** */


/** *********************************** CMS: start. *********************************** */
class FEvent extends FObject {
	function FEvent( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'fk_obj_id',
									new FKObjectField( 'fk_obj_id', "Linked to", 'Collegata a', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_obj_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		
		$this->addField('event', -1, 'start_date', new FDateTime( 'start_date', "Start", $aDescription='Start', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=true ));
		$this->addField('event', -1, 'end_date', new FDateTime( 'end_date', "End", $aDescription='End', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=true ));
		$array_valori = array('0'=>'No', '1'=>'Yes',);
		$this->addField('event', -1, 'all_day', new FList( 'all_day', "All day", 'All day event', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori, $altezza=1, $multiselezione=false ));

		$this->addField('event', -1, 'url', new FString( 'url', "Url", 'Url', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('event', -1, 'category', new FString( 'category', "Category", 'Category', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
// 		$this->addField('event', -1, 'private', new FList( 'private', "Private", 'Private event', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori, $altezza=1, $multiselezione=false ));

		// TODO how to implement an alarm?
// 		$this->addField('alarm', -1, 'alarm', new FList( 'alarm', "Alarm", 'Alarm', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori, $altezza=1, $multiselezione=false ));
// 		$array_valori_unit = array('0'=>'No', '1'=>'Yes',);
// 		$this->addField('alarm', -1, 'alarm_unit', new FList( 'alarm_unit', "Alarm unit", 'Alarm unit', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori_unit, $altezza=1, $multiselezione=false ));
// 		$this->addField('alarm', -1, 'alarm_minute', new FNumber('alarm_minute', "Time units", 'Num. time units', $aValore='', $aClasseCss='formtable' ));
// 		$array_valori_before_event = array('0'=>'Before', '1'=>'After',);
// 		$this->addField('alarm', -1, 'before_event', new FList( 'before_event', "Alarm", 'Alarm before or after event', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori_before_event, $altezza=1, $multiselezione=false ));

		// TODO recurrence
// 		$this->addField('recurrence', -1, 'recurrence', new FList( 'recurrence', "Recurrent", 'Recurrent event', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori, $altezza=1, $multiselezione=false ));
// 		$array_valori_recurrence_type = array('0'=>'Daily', '1'=>'Weekly', '2'=>'Monthly', '3'=>'Yearly',);
// 		$this->addField('recurrence', -1, 'recurrence_type', new FList( 'recurrence_type', "Type", 'Recurrence type', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori_recurrence_type, $altezza=1, $multiselezione=false ));

		$this->addDetail("FPeople"); // FIXME while creating, results associated with ALL the people in the table
		$this->addDetail("FLink");
		$this->addDetail("FFile");
	}
	function getDetailIcon() { return "icons/event_16x16.png"; }
	function getDetailTitle() { return "Event"; }
	function getDetailColumnNames() { return array('creation_date','creator','father_id','name','description','fk_obj_id',
		'owner','group_id','permissions',
		'start_date', 'end_date', 'all_day', 'url', 'category',
		'alarm', 'alarm_unit', 'alarm_minute', 'before_event', 
		'recurrence', 'recurrence_type', 
// 		'owner','creator','creation_date','last_modify','last_modify_date',
	); }
	function getFilterForm() { return FForm::getInstance("FEventFilter"); } // 2011.04.04 new FLogFilter; }
	function getFilterFields() { return array('name','father_id'); }
	function getListTitle() { return "Events"; }
	function getListColumnNames() { return array('start_date', 'end_date', 'name','father_id'); }
	function getDecodeGroupNames() { return array_merge( parent::getDecodeGroupNames(),
										array("event"=>"Event","recurrence"=>"Recurrence","alarm"=>"Alarm",)
										); }
	function getDBE() { return new DBEEvent(); }
}
$formschema_type_list[]="FEvent";
/**
 * Classe di filtro per i TODO
 */
class FEventFilter extends FEvent {
	function getDetailTitle() { return "Event Filter"; }
	function FEventFilter( $nome='', $azione='', $metodo="POST" ) {
		parent::FEvent( $nome, $azione, $metodo);
		
		$this->addField('', -1, 'from_start_date', new FDateTime( 'from_start_date', "Date >=", $aDescription='Date from', $aValore='', $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ));
		$this->addField('', -1, 'to_start_date', new FDateTime( 'to_start_date', "Date <=", $aDescription='Date to', $aValore='', $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ));

		// Valori di defaults per la ricerca
		$this->setValue('from_start_date',getTodayString($with_time=false,$date_separator="-"));
// 		$this->setValue('to_start_date',getTodayString($with_time=false,$date_separator="-"));
	}
	function getFilterFields() {
		return array(
				'name','from_start_date','to_start_date',
		);
	}
}
$formschema_type_list[]="FEventFilter";

class FFile extends FObject {
	function FFile( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'fk_obj_id',
									new FKObjectField( 'fk_obj_id', "Linked to", 'Collegata a', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_obj_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		
		$this->addField('file', -1, 'path', new FString( 'path', "Path", 'Percorso', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('file', -1, 'filename', new FFileField( 'filename', "File name", 'Nome del file', $GLOBALS[ 'files_directory' ], $aSize=20, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('file', -1, 'checksum', new FString( 'checksum', "Checksum", 'Checksum SHA1', $aSize=40, $aLength=40, $aValore='', $aClasseCss='formtable' ));
		$this->addField('file', -1, 'mime', new FString( 'mime', 'Mime type', 'Mime type', $aSize=40, $aLength=40, $aValore='', $aClasseCss='formtable' ));
		$this->addField('file', -1, 'alt_link', new FString( 'alt_link', "Alternative Link", 'Link alternativo', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		
		// Valori di defaults per la ricerca
		$this->setValue('father_id',0);
	}
	function getDetailIcon() { return "icons/file_16x16.gif"; }
	function getDetailTitle() { return "File"; }
	function getDetailColumnNames() { return array(
		'father_id','name','description','path','filename','checksum','mime','alt_link',//'fk_obj_id',
		'owner','group_id','permissions',
// 		'creator','creation_date','last_modify','last_modify_date',
	); }
	function getDetailReadOnlyColumnNames() { return array_merge( parent::getDetailReadOnlyColumnNames(), array('checksum'), array('mime')); }
	function getFilterForm() { return new FFile; }
	function getFilterFields() { return array('father_id','name','description'); } //,'fk_obj_id',); }
	function getListTitle() { return "Files"; }
	function getListColumnNames() { return array('name','father_id','mime','path','filename'); } //,'fk_obj_id'); }
	function getDecodeGroupNames() {
		$tmp = parent::getDecodeGroupNames();
		$tmp["file"]="File";
		return $tmp;
	}
	function getDBE() { return new DBEFile(); }
	
	/**
	 * @par alternative_link link alternativo per permettere download da linkbucks :-) 2010.09.27
	 */
	// FForm::render_view(&$dbmgr, $nome_field = null)
	//function render_view($with_thumbnail=true,$alternative_link='') {
	function render_view(&$dbmgr, $nome_field = null) {
		$with_thumbnail=true;
		$alternative_link='';
		$__alt_link = $this->getValue('alt_link');
		if($__alt_link===null || $__alt_link=='') {
			$__alt_link = $alternative_link;
		}
		return $this->getField('filename')->render_view($with_thumbnail,$__alt_link);
	}
	
	function isImage() { $_mime = $this->getValue('mime'); return $_mime>'' && substr($_mime,0,5)=='image'; }

	function getActions() {
		$ret = parent::getActions();
		$ret['download'] = array('Download','download.png');
		return $ret;
	}
}
$formschema_type_list[]="FFile";

class FFolder extends FObject {
	function FFolder( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'fk_obj_id',
			new FKObjectField( 'fk_obj_id', "Linked to", 'Collegata a', $aSize=50, $aValore=null, $aClasseCss='formtable',
			$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_obj_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		$this->addField('', -1, 'childs_sort_order', new FChildSort( 'childs_sort_order', "Sort order", 'Ordinamento dei figli diretti', 255, $aLength=40, $aValore='', $aClasseCss='formtable', $listaValori=array(), $altezza=10, $multiselezione=true));
		
		$this->addDetail("FFolder");
		$this->addDetail("FPage");
		$this->addDetail("FNews");
		$this->addDetail("FLink");
		$this->addDetail("FNote");
		$this->addDetail("FEvent");
		$this->addDetail("FFile");
		$this->addDetail("FTodo");
		$this->addDetail("FTimetrack");
	}
	function getDetailIcon() { return "icons/folder_16x16.gif"; }
	function getDetailTitle() { return "Folder"; }
	function getDetailColumnNames() { return array('id','father_id','name','description','fk_obj_id','childs_sort_order',
		'owner','group_id','permissions',
// 		'owner','creator','creation_date','last_modify','last_modify_date',
	); }
	function getViewColumnNames() { return array(
// 		'name',
// 		'description',
		); }
	function getFilterForm() { return new FFolder; }
	function getFilterFields() { return array('name','fk_obj_id','father_id'); }
	function getListTitle() { return "Folders"; }
	function getListColumnNames() { return array('name','description','father_id','fk_obj_id'); }
	function getDBE() { return new DBEFolder(); }
}
$formschema_type_list[]="FFolder";

class FLink extends FObject {
	function FLink( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'fk_obj_id',
									new FKObjectField( 'fk_obj_id', "Linked to", 'Collegata a', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_obj_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		$this->addField('link', -1, 'href', new FString( 'href', "Href", 'Url destinazione', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
		$this->addField('link', -1, 'target', new FString( 'target', "Target", 'Frame target.', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
	}
	function getDetailIcon() { return $this->isInternal() ? "icons/list-disc_16x16.png" : "icons/link_16x16.gif"; }
	function getDetailTitle() { return "Link"; }
	function getDetailColumnNames() { return array('creation_date','creator','father_id','name','description','href','target','fk_obj_id',
		'owner','group_id','permissions',
// 		'owner','creator','creation_date','last_modify','last_modify_date',
	); }
	function getFilterForm() { $ret = new FLink; $ret->setValue('fk_obj_id',0); return $ret; }
	function getFilterFields() { return array('fk_obj_id','name','href',); }
	function getListTitle() { return "Links"; }
	function getListColumnNames() { return array('creation_date','fk_obj_id','name','href'); }
	function getDecodeGroupNames() { return array_merge( parent::getDecodeGroupNames(),
										array("link"=>"Link",)); }
	function getDBE() { return new DBELink(); }
	
	function render_view(&$dbmgr, $nome_field=null) {
		$ret = '';
		if ( $nome_field!==null ) {
			$_field = $this->getField( $nome_field);
			$ret .= $_field->render_view();
		} else {
			$_target = $this->getValue('target');
			if($_target>'') $_target="target=\"$_target\"";
			$ret = "<a href=\"".$this->getValue('href')."\" $_target>".$this->getValue('name')."</a>";
		}
		return $ret;
	}
	
	function isInternal() { return $this->getValue('href')>'' && strpos($this->getValue('href'),'http://')===false && strpos($this->getValue('href'),'https://')===false; }
}
$formschema_type_list[]="FLink";

class FNote extends FObject {
	function FNote( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'fk_obj_id',
									new FKObjectField( 'fk_obj_id', "Linked to", 'Collegata a', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_obj_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
											
		$this->getField('description')->width=60;
		$this->getField('description')->height=20;
	}
	function getDetailIcon() { return "icons/note_16x16.gif"; }
	function getDetailTitle() { return "Note"; }
	function getDetailColumnNames() { return array('creation_date','creator','father_id','name','description','fk_obj_id',
		'owner','group_id','permissions',
// 		'owner','creator','creation_date','last_modify','last_modify_date',
	); }
	function getViewColumnNames() { return array(
		'name','description',
		); }
	function getFilterForm() { $ret = new FNote; $ret->setValue('fk_obj_id',0); return $ret; }
	function getFilterFields() { return array('name','fk_obj_id',); }
	function getListTitle() { return "Notes"; }
	function getListColumnNames() { return array('creation_date','name','fk_obj_id'); }
	function getDBE() { return new DBENote(); }
}
$formschema_type_list[]="FNote";

class FPage extends FObject {
	function FPage( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'fk_obj_id',
									new FKObjectField( 'fk_obj_id', "Linked to", 'Collegata a', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_obj_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		$this->addField('', -1, 'html', new FHtml( 'html', "Html", 'Contenuto html.', 255, '', 'formtable', 120, 100));
		$this->addField('', -1, 'language', new FLanguage( 'language', "Language", 'Page language', $aSize=50, $aLength=255, $aValore='en_US', $aClasseCss='formtable'));
		
		$this->addDetail("FPage");
		$this->addDetail("FLink");
		$this->addDetail("FFile");
		$this->addDetail("FEvent");
	}
	function getDetailIcon() { return "icons/page_16x16.gif"; }
	function getDetailTitle() { return "Page"; }
	function getDetailColumnNames() { return array('creation_date','creator','father_id','name','description','language','html','fk_obj_id',
		'owner','group_id','permissions',
// 		'owner','creator','creation_date','last_modify','last_modify_date',
	); }
	function getFilterForm() { $ret = new FPage; $ret->setValue('fk_obj_id',0); return $ret; }
	function getFilterFields() { return array('name','fk_obj_id','father_id'); }
	function getListTitle() { return "Pages"; }
	function getListColumnNames() { return array('creation_date','name','fk_obj_id'); }
	function getDecodeGroupNames() { return array_merge( parent::getDecodeGroupNames(),
										array("html"=>"Html",)); }
	function getDBE() { return new DBEPage(); }
}
$formschema_type_list[]="FPage";

class FNews extends FPage {
	function FNews( $nome='', $azione='', $metodo="POST" ) {
		parent::FPage( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
// 		$this->addField('html', -1, 'html', new FHtml( 'html', "Html", 'Contenuto html.', 255, '', $aClasseCss='formtable', 50, 5));
		$this->removeDetail( "FPage");
		$this->addDetail("FNews");
// 		$this->addDetail("FEvent");
	}
	function getDetailIcon() { return "icons/news.png"; }
	function getDetailTitle() { return "News"; }
	function getDetailColumnNames() { return array('creation_date','creator','father_id','name','description','html','fk_obj_id',
		'owner','group_id','permissions',
// 		'owner','creator','creation_date','last_modify','last_modify_date',
	); }
	function getViewColumnNames() { return array(
		'creation_date','creator','name','description','html'
		); }
	function getFilterForm() { $ret = new FNews; $ret->setValue('fk_obj_id',0); return $ret; }
	function getFilterFields() { return array('name','fk_obj_id','father_id',); }
	function getListTitle() { return "News"; }
	function getListColumnNames() { return array('creation_date','name','fk_obj_id'); }
	function getDecodeGroupNames() { return array_merge( parent::getDecodeGroupNames(),
										array("html"=>"Html",)); }
	function getDBE() { return new DBENews(); }
}
$formschema_type_list[]="FNews";

/** *********************************** CMS: end. *********************************** */


/** *********************************** RRA Projects: inizio. *********************************** */
class FProject extends FObject {
	function FProject( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		
		$this->addDetail("FFolder");
		$this->addDetail("FProjectProject");
		$this->addDetail("FProjectCompany");
		$this->addDetail("FProjectPeople");
		$this->addDetail("FTodo");
		$this->addDetail("FTimetrack");
		$this->addDetail("FPage");
		$this->addDetail("FLink");
		$this->addDetail("FNote");
		$this->addDetail("FEvent");
	}
	function getDetailIcon() { return "icons/project_16x16.gif"; }
	function getDetailTitle() { return "Project"; }
	function getDetailColumnNames() { return array('name','description',
		'creator','creation_date', // 'owner','last_modify','last_modify_date',
		'owner','group_id','permissions',
	); }
	function getViewColumnNames() { return array('name','description',); }
	function getListTitle() { return "Projects"; }
	function getListColumnNames() { return array('name','description'); }
	function getDBE() { return new DBEProject(); }
}
$formschema_type_list[]="FProject";

class FProjectCompany extends FAssociation {
	function getDetailTitle() { return "Association Project-Company"; }
	function FProjectCompany($nome='', $azione='', $metodo="POST") {
		$this->FAssociation(new DBEProjectCompany(),null,null,$nome, $azione, $metodo);
		
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'projects_companies_role_id',
									new FKField( 'projects_companies_role_id', "Role", 'Ruolo.', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('projects_companies_role_id'), $description_columns=array('projects_companies_role_id',), $destform="FCompany", $viewmode='select'  ));
	}
	function getDBE() { return new DBEProjectCompany(); }
	function getFromForm() { return FForm::getInstance("FProject"); } // 2011.04.04 new FProject(); }
	function getToForm() { return FForm::getInstance("FCompany"); } // 2011.04.04 new FCompany(); }
}
$formschema_type_list[]="FProjectCompany";
class FProjectCompanyRole extends FObject {
	function FProjectCompanyRole( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		
		$this->addField('', -1, 'order_position', new FString( 'order_position', "Order", 'Ordine', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
	}
	function getDetailTitle() { return "Project-Company Role"; }
	function getDetailColumnNames() { return array('name','description',
// 		'creator','creation_date', // 'owner','last_modify','last_modify_date',
		'order_position',
	); }
	function getListTitle() { return "Project-Company Roles"; }
	function getListColumnNames() { return array('order_position','name','description'); }
	function getDBE() { return new DBEProjectCompanyRole(); }
}
$formschema_type_list[]="FProjectCompanyRole";

class FProjectPeople extends FAssociation {
	function getDetailTitle() { return "Association Project-People"; }
	function FProjectPeople($nome='', $azione='', $metodo="POST") {
		$this->FAssociation(new DBEProjectPeople(),null,null,$nome, $azione, $metodo);
		
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'projects_people_role_id',
									new FKField( 'projects_people_role_id', "Role", 'Ruolo.', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('projects_people_role_id'), $description_columns=array('projects_people_role_id',), $destform="FPeople", $viewmode='select'  ));
	}
	function getDBE() { return new DBEProjectPeople(); }
	function getFromForm() { return FForm::getInstance("FProject"); } // 2011.04.04 new FProject(); }
	function getToForm() { return FForm::getInstance("FPeople"); } // 2011.04.04 new FPeople(); }
}
$formschema_type_list[]="FProjectPeople";
class FProjectPeopleRole extends FObject {
	function FProjectPeopleRole( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		
		$this->addField('', -1, 'order_position', new FString( 'order_position', "Order", 'Ordine', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
	}
	function getDetailTitle() { return "Project-People Role"; }
	function getDetailColumnNames() { return array('name','description','order_position',); }
	function getListTitle() { return "Project-People Roles"; }
	function getListColumnNames() { return array('order_position','name','description'); }
	function getDBE() { return new DBEProjectPeopleRole(); }
}
$formschema_type_list[]="FProjectPeopleRole";

class FProjectProject extends FAssociation {
	function FProjectProject($nome='', $azione='', $metodo="POST") {
		$this->FAssociation(new DBEProjectProject(),null,null,$nome, $azione, $metodo);
		
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'projects_projects_role_id',
									new FKField( 'projects_projects_role_id', "Role", 'Ruolo.', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('projects_projects_role_id'), $description_columns=array('projects_projects_role_id',), $destform="FProject", $viewmode='select'  ));
	}
	function getDetailTitle() { return "Association Project-Project"; }
	function getDBE() { return new DBEProjectProject(); }
	function getFromForm() { return FForm::getInstance("FProject"); } // 2011.04.04 new FProject(); }
	function getToForm() { return FForm::getInstance("FProject"); } // 2011.04.04 new FProject(); }
}
$formschema_type_list[]="FProjectProject";
class FProjectProjectRole extends FObject {
	function FProjectProjectRole( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		
		$this->addField('', -1, 'order_position', new FString( 'order_position', "Order", 'Ordine', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
	}
	function getDetailTitle() { return "Project-Project Role"; }
	function getDetailColumnNames() { return array('name','description','order_position',); }
	function getListTitle() { return "Project-Project Roles"; }
	function getListColumnNames() { return array('order_position','name','description'); }
	function getDBE() { return new DBEProjectProjectRole(); }
}
$formschema_type_list[]="FProjectCompanyRole";

class FTimetrack extends FObject {
	function FTimetrack( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'fk_obj_id',
									new FKObjectField( 'fk_obj_id', "Linked to", 'Collegata a', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_obj_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		$this->addField('', -1, 'fk_progetto',
									new FKObjectField( 'fk_progetto', "Project", 'Progetto', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_progetto'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		$this->addField('', -1, 'dalle_ore', new FDateTime( 'dalle_ore', "From", $aDescription='Dalle Ore', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=true ));
		$this->addField('', -1, 'alle_ore', new FDateTime( 'alle_ore', "To", $aDescription='Alle Ore', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=true ));
		$this->addField('', -1, 'ore_intervento', new FDateTime( 'ore_intervento', "Hours of Work", $aDescription='Ore Intervento', $aValore='', $aClasseCss='formtable', $aVisualizzaData=false, $aVisualizzaOra=true ));
		$this->addField('', -1, 'ore_viaggio', new FDateTime( 'ore_viaggio', "Hours of Travel", $aDescription='Ore Viaggio', $aValore='', $aClasseCss='formtable', $aVisualizzaData=false, $aVisualizzaOra=true ));
		$this->addField('', -1, 'km_viaggio', new FNumber('km_viaggio', "KM Travels", 'KM Viaggio', $aValore='', $aClasseCss='formtable' ));
		$array_valori = array(''=>'','0'=>'Office', '1'=>'From office (via ssh/telnet/ecc.)', '2'=>'On site', );
		$this->addField('', -1, 'luogo_di_intervento', new FList( 'luogo_di_intervento', "Luogo di intervento", 'Luogo di intervento', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori, $altezza=1, $multiselezione=false ));
		$array_valori = array(''=>'', '0'=>'da fatturare', '1'=>'non fatturare', '2'=>'fatturato', '3'=>'assistenza',);
		$this->addField('', -1, 'stato', new FList( 'stato', "State", 'Stato', $aSize=255, $aLength=40, $aValore='0', $aClasseCss='formtable', $listaValori=$array_valori, $altezza=1, $multiselezione=false ));
		$this->addField('', -1, 'costo_per_ora', new FNumber('costo_per_ora', "Cost per hour", 'Cost per hour', $aValore=0, $aClasseCss='formtable' ));
		$this->addField('', -1, 'costo_valuta', new FString( 'costo_valuta', "Currency", 'Currency', $aSize=50, $aLength=255, $aValore='euro', $aClasseCss='formtable' ));
	}
	function getDetailIcon() { return "icons/timetrack_16x16.gif"; }
	function getDetailTitle() { return "Timetrack"; }
	function getDetailColumnNames() { return array('creation_date','creator','fk_progetto','father_id','fk_obj_id','name','description',
// 		'creator','creation_date','last_modify','last_modify_date',
		'fk_progetto','dalle_ore','alle_ore','ore_intervento','ore_viaggio','km_viaggio',
		'luogo_di_intervento','stato','costo_per_ora','costo_valuta',
		'owner','group_id','permissions',
	); }
	function getViewColumnNames() { return array(
// 		'fk_progetto','father_id','fk_obj_id',
		'name','description',
		'fk_progetto','dalle_ore','alle_ore','ore_intervento','ore_viaggio','km_viaggio',
		'luogo_di_intervento','stato','costo_per_ora','costo_valuta',
		); }
	function getFilterForm() { return new FTimetrack; /*Filter;*/ }
	function getFilterFields() { return array('fk_progetto','name','fk_obj_id','luogo_di_intervento','stato',); }
	function getListTitle() { return "Timetracks"; }
	function getListColumnNames() { return array('fk_progetto','stato','dalle_ore','name','ore_intervento','fk_obj_id',); }
//	function getListColumnNames() { return array('fk_progetto','stato','creation_date','fk_obj_id','name','ore_intervento',); }
	function getDBE() { return new DBETimetrack(); }
}
$formschema_type_list[]="FTimetrack";

class FTodo extends FObject {
	function FTodo( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$_mydbe=$this->getDBE();
		
		$this->addField('', -1, 'priority', new FNumber('priority', "Priority", 'Priorita', $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'data_segnalazione', new FDateTime( 'data_segnalazione', "Reported on", $aDescription='Data segnalazione', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=true));
		$this->addField('', -1, 'fk_segnalato_da',
									new FKField( 'fk_segnalato_da', "Reporter", 'Segnalato da', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_segnalato_da'), $description_columns=array('name',), $destform="FPeople", $viewmode='select'  ));
		$this->addField('', -1, 'fk_cliente',
									new FKField( 'fk_cliente', "Customer", 'Cliente', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_cliente'), $description_columns=array('name',), $destform="FCompany", $viewmode='select'  ));
		$this->addField('', -1, 'fk_progetto',
									new FKObjectField( 'fk_progetto', "Project", 'Progetto', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_progetto'), $description_columns=array('name'), $destform=null, $viewmode='select'  ));
		$this->addField('', -1, 'fk_tipo',
									new FKField( 'fk_tipo', "Kind", 'Tipo', $aSize=50, $aValore='', $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_tipo'), $description_columns=array('name',), $destform="FTodoTipo", $viewmode='select'  ));
		$this->addField('', -1, 'stato', new FPercent('stato', "State (%)", 'Stato di avanzamento %', $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'descrizione', new FTextArea( 'descrizione', "Description", 'Descrizione del todo.', 255, '', $aClasseCss='formtable', 50, 5));
		$this->addField('', -1, 'intervento', new FTextArea( 'intervento', "Resolution", 'Intervento eseguito.', 255, '', $aClasseCss='formtable', 50, 5));
		$this->addField('', -1, 'data_chiusura', new FDateTimeReadOnly( 'data_chiusura', "Closed on", $aDescription='Data chiusura', $aValore='', $aClasseCss='formtable', $aVisualizzaData=TRUE, $aVisualizzaOra=TRUE ));
		
		$this->addDetail("FTodo");
		$this->addDetail("FTimetrack");
	}
	function getDetailIcon() { return "icons/task_16x16.gif"; }
	function getDetailTitle() { return "Todo"; }
	function getDetailColumnNames() { return array('name',
// 		'creator','creation_date','last_modify','last_modify_date','description','fk_obj_id',
		'priority','data_segnalazione','fk_segnalato_da','fk_cliente','fk_progetto','father_id','fk_tipo','stato','descrizione','intervento','data_chiusura',
		'owner','group_id','permissions',
	); }
	function getFilterForm() { return new FTodoFilter; }
	function getFilterFields() { return array('name','priority','data_segnalazione','fk_segnalato_da','fk_cliente','fk_progetto','father_id','fk_tipo','stato','stato','data_chiusura',); }
	function getListTitle() { return "Todos"; }
	function getListColumnNames() { return array(
		'priority','data_segnalazione',
// 		'fk_segnalato_da',
		'fk_cliente','fk_progetto','father_id','name','fk_tipo','stato',
		'data_chiusura',
	); }
	function getDBE() { return new DBETodo(); }
	function getShortDescription($dbmgr=null) {
		$ret='';
		$father = & $this->getField('father_id');
		$father_string = !($dbmgr===null) && is_a($father,'FKField') ? $father->render_view($dbmgr) : $father->render_view();
		$stato = & $this->getField('stato');
		$stato_string = $dbmgr!==null && is_a($stato,'FKField') ? $stato->render_view($dbmgr) : $stato->render_view();
		$ret .= $stato_string>""?"$stato_string - " : '';
		$ret .= $this->getValue('name');
		$ret .= $father_string>""?" ($father_string)" : '';
		return $ret;
	}
	function getActions() {
		$ret = parent::getActions();
		$ret['start_timetracking'] = array('Start Time Tracking','play.png');
		$ret['stop_timetracking'] = array('Stop Time Tracking','stop.png');
		return $ret;
	}
}
$formschema_type_list[]="FTodo";

/**
 * Classe di filtro per i TODO
 */
class FTodoFilter extends FTodo {
	function getDetailTitle() { return "Todo Filter"; }
	function FTodoFilter( $nome='', $azione='', $metodo="POST" ) {
		parent::FTodo( $nome, $azione, $metodo);
		
		$this->addField('', -1, 'from_data_chiusura', new FDateTime( 'from_data_chiusura', "Closed on >=", $aDescription='Data chiusura', $aValore='', $aClasseCss='formtable', $aVisualizzaData=TRUE, $aVisualizzaOra=false ));
		$this->addField('', -1, 'to_data_chiusura', new FDateTime( 'to_data_chiusura', "Closed on <=", $aDescription='Data chiusura', $aValore='', $aClasseCss='formtable', $aVisualizzaData=TRUE, $aVisualizzaOra=false ));

		$this->addField('', -1, 'from_stato', new FPercent('from_stato', "State (%) >=", 'Stato di avanzamento %', $aValore='', $aClasseCss='formtable' ));
		$this->addField('', -1, 'to_stato', new FPercent('to_stato', "State (%) <=", 'Stato di avanzamento %', $aValore='99', $aClasseCss='formtable' ));
	}
	function getFilterFields() {
		return array('name','priority','data_segnalazione','fk_segnalato_da','fk_cliente','fk_progetto','father_id','fk_tipo','from_stato','to_stato',);
	}
}
/** COMMENTATO: non va inserita una form di ricerca tra le form dei tipi documento. */
$formschema_type_list[]="FTodoFilter";

class FTodoTipo extends FObject {
	function FTodoTipo( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo);
		$this->addField('', -1, 'order_position', new FString( 'order_position', "Order", 'Ordine', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ));
	}
	function getDetailTitle() { return "Todo::Kind"; }
	function getDetailColumnNames() { return array('name','description','order_position',); }
	function getListTitle() { return "Todo::Kind"; }
	function getListColumnNames() { return array('order_position','name','description'); }
	function getDBE() { return new DBETodoTipo(); }
}
$formschema_type_list[]="FTodoTipo";
/** *********************************** RRA Projects: fine. *********************************** */


/** *********************************** Form Factory *********************************** */
class MyFormFactory extends FormFactory {
	function MyFormFactory( $verbose = 0 ) {
		$this->FormFactory( $verbose);
		
		global $formschema_type_list;
		
		foreach( $formschema_type_list as $mytype ) {
			$this->register($mytype);
		}
	}
}

?>
