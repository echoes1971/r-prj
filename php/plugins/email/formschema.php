<?php

class FMail extends FFile {
	function FMail( $nome='', $azione='', $metodo="POST" ) {
		parent::FFile( $nome, $azione, $metodo );
		$_mydbe=$this->getDBE();
		
		$this->addField( 'email', -1, 'msgid', new FString( 'msgid',"Message ID","Message ID", $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ) );
		$this->addField( 'email', -1, 'subject', new FString( 'subject', "Subject", 'Subject', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ) );
		$this->addField( 'email', -1, 'msgfrom', new FString( 'msgfrom', "From", 'From', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ) );
		$this->addField( 'email', -1, 'msgto', new FString( 'msgto', "To", 'To', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ) );
		$this->addField( 'email', -1, 'msgcc', new FString( 'msgcc', "CC", 'CC', $aSize=50, $aLength=255, $aValore='', $aClasseCss='formtable' ) );
		$this->addField( 'email', -1, 'msgdate', new FDateTime( 'msgdate', "Date", $aDescription='Date', $aValore=getTodayString(), $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=true ) );
		$this->addField( 'email', -1, 'msgbody', new FTextArea( 'msgbody', "Body", 'Message body', 255, '', $aClasseCss='formtable', 50, 5) );
		
		// Add folder as my own master form
		$this->addMaster("FFolder");
	}
	function getDetailIcon() { return "icons/email_16x16.png"; }
	function getDetailTitle() { return "Email"; }
	function getDetailColumnNames() { return array(
		'father_id',
// 		'name','description',
		'path','filename','checksum','mime','alt_link',//'fk_obj_id',
		'owner','group_id','permissions',
		'msgdate','msgfrom','msgto','msgcc','subject','msgbody',
// 		'owner','creator','creation_date','last_modify','last_modify_date',
	); }
	function getViewColumnNames() { return array(
//		'ban_ip','description',
		); }
	function getFilterForm() {
		$ret = new FMailFilter;
//		$ret->setValue('from_creation_date',getTodayString(false));
//		$ret->setValue('ban_ip','127.0.0.1');
		return $ret;
	}
	//function getFilterFields() { return array('creation_date','ban_ip','description',); }
	function getListTitle() { return "Emails"; }
	function getListColumnNames() { return array(
 		'father_id',
		'msgdate','msgfrom','msgto','msgcc','subject',); }
	function getDecodeGroupNames() {
		$tmp = parent::getDecodeGroupNames();
		$tmp["email"]="Email";
		return $tmp;
	}
	function getDetailReadOnlyColumnNames() { return array_merge( parent::getDetailReadOnlyColumnNames(), array('msgid','msgdate','msgfrom','msgto','msgcc','subject','msgbody',) ); }
	function getDBE() {
		return new DBEMail();
	}
}
$formschema_type_list[]="FMail";

/**
 * Filter class for emails
 */
class FMailFilter extends FMail {
	function getDetailTitle() { return "Email Filter"; }
	function FMailFilter( $nome='', $azione='', $metodo="POST" ) {
		parent::FMail( $nome, $azione, $metodo );
		
		$this->addField( '', -1, 'from_msgdate', new FDateTime( 'from_msgdate', "Date >=", $aDescription='Date', $aValore='', $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ) );
		$this->addField( '', -1, 'to_msgdate', new FDateTime( 'to_msgdate', "Date <", $aDescription='Date', $aValore='', $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ) );
//		$this->addField( '', -1, 'last_modify_date', new FDateTime( 'last_modify_date', "Modified on", $aDescription='Data modifica', $aValore='', $aClasseCss='formtable', $aVisualizzaData=true, $aVisualizzaOra=false ) );

		$this->removeMaster("FFolder");
	}
	function getFilterFields() { return array('from_msgdate','to_msgdate','msgfrom','msgto','msgcc','subject',); }
}
/** COMMENTATO: non va inserita una form di ricerca tra le form dei tipi documento. */
$formschema_type_list[]="FMailFilter";

?>
