<?php

class FBanned extends FObject {
	function FBanned( $nome='', $azione='', $metodo="POST" ) {
		parent::FObject( $nome, $azione, $metodo );
		$_mydbe=$this->getDBE();
		
/*		$this->addField( '', -1, 'fk_obj_id',
									new FKObjectField( 'fk_obj_id', "Linked to", 'Collegata a', $aSize=50, $aValore=null, $aClasseCss='formtable',
											$mydbe=$_mydbe, $myFK = $_mydbe->getFKDefinition('fk_obj_id'), $description_columns=array('name'), $destform=null, $viewmode='select'  ) );*/
		
		$this->addField( '', -1, 'ban_ip', new FString( 'ban_ip', "IP", 'Banned IP', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ) );
		$this->addField( '', -1, 'redirected_to', new FString( 'redirected_to', "Redirect to", 'Redirect to...', $aSize=50, $aLength=255, $aValore='http://adf.ly/XdZw', $aClasseCss='formtable' ) );
		$this->addField( '', -1, 'give_reason', new FString( 'give_reason', "Given reason", 'Displayed reason to the banned IP', $aSize=50, $aLength=255, $aValore='Your IP has been blocked.<br/>See http://adf.ly/XdZw for more details.', $aClasseCss='formtable' ) );
	}
	function getDetailIcon() { return "icons/banned_16x16.png"; }
	function getDetailTitle() { return "Banned"; }
	function getDetailColumnNames() { return array('creation_date','creator',
// 		'father_id','name',
		'ban_ip','description',
// 		'fk_obj_id',
		'redirected_to','give_reason',
		'owner','group_id','permissions',
// 		'owner','creator','creation_date','last_modify','last_modify_date',
	); }
	function getViewColumnNames() { return array(
		'ban_ip','description',
		); }
	function getFilterForm() {
		$ret = new FBannedFilter;
		$ret->setValue('from_creation_date',getTodayString(false));
//		$ret->setValue('ban_ip','127.0.0.1');
		return $ret;
	}
	//function getFilterFields() { return array('creation_date','ban_ip','description',); }
	function getListTitle() { return "Banlist"; }
	function getListColumnNames() { return array('creation_date','ban_ip','description','redirected_to','give_reason',); }
	function getDBE() {
		return new DBEBanned();
	}
}
$formschema_type_list[]="FBanned";

/**
 * Classe di filtro per i TODO
 */
class FBannedFilter extends FBanned {
	function getDetailTitle() { return "Banned Filter"; }
	function FBannedFilter( $nome='', $azione='', $metodo="POST" ) {
		parent::FBanned( $nome, $azione, $metodo );
		
		$this->addField( '', -1, 'from_creation_date', new FDateTime( 'from_creation_date', "Created >=", $aDescription='Data creazione', $aValore='', $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ) );
		$this->addField( '', -1, 'to_creation_date', new FDateTime( 'to_creation_date', "Created <", $aDescription='Data creazione', $aValore='', $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ) );
//		$this->addField( '', -1, 'last_modify_date', new FDateTime( 'last_modify_date', "Modified on", $aDescription='Data modifica', $aValore='', $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ) );

		$this->addField( '', -1, 'ban_ip', new FString( 'ban_ip', "IP", 'Banned IP', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ) );
		$this->addField( '', -1, 'description', new FTextArea( 'description', "Description", 'Descrizione del gruppo.', 255, '', $aClasseCss='formtable', 50, 5) );
	}
	function getFilterFields() { return array('from_creation_date','to_creation_date','ban_ip','description',); }
}
/** COMMENTATO: non va inserita una form di ricerca tra le form dei tipi documento. */
$formschema_type_list[]="FBannedFilter";

?>
