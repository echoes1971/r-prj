<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: formulator.php $
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

require_once(ROOT_FOLDER."config.php");
require_once(ROOT_FOLDER."db/dblayer.php");

// Widgets Library
require_once(ROOT_FOLDER."formulator/widgets.php");

$VECCHIO_CALENDARIO = false;
if(!$VECCHIO_CALENDARIO) {
	require_once("jscalendar-1.0/calendar.php");
	$lang = array_key_exists('lang',$_GET) ? $_GET['lang'] : null;
	if($lang==null)	$lang = array_key_exists('lang',$_REQUEST) ? $_REQUEST['lang'] : null;
	if($lang==null)	$lang = 'en';
	global $calendar; $calendar = new DHTML_Calendar(ROOT_FOLDER.'formulator/jscalendar-1.0/', $lang, 'calendar-win2k-2', false);
	global $calendar_writtenLoadFiles; $calendar_writtenLoadFiles = false;
}

/** Formulator Field: classe base. */
class FField {
	
	var $aNomeCampo;
	var $_title='';
	var $_description;
	var $_size;
	var $aValore='';
	var $aClasseCss=null;
	var $myform;
	var $isArray;
	/**
	 * Tipo di dato: s=stringa, n=numero, d=data
	 * Default=stringa
	 */
	var $tipo;
	/**
	 * @param aNomeCampo field name
	 * @param aTitle field title (label)
	 * @param aDescription field description
	 * @param aSize field size
	 * @param aLength field length
	 * @param aValore field value
	 * @param aClasseCss css class
	 * @param myform riferimento all'istanza di form a cui appartiene
	 * @param mytipo s=stringa, n=numero, d=data
	 */
	function FField($aNomeCampo, $aTitle, $aDescription, $aSize, $aLength=20, $aValore='', $aClasseCss=null, $myform=null, $mytipo='s') {
		$this->aNomeCampo = $aNomeCampo;
		$this->_title = $aTitle;
		$this->_description = $aDescription;
		$this->_size = $aSize;
		$this->_length = $aLength;
		$this->aValore = $aValore;
		$this->aClasseCss = $aClasseCss;
		$this->myform=$myform;
		$this->tipo = $mytipo;

		$this->isArray='';
	}
	
	function render() {
		return campoGenerico('text', $this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, $this->_length);
	}
	function render_view() {
		return campoGenerico_view($tipoCampo='text', $this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss);
	}
	function render_hidden() {
		return campoGenerico_hidden($tipoCampo='text', $this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss);
	}
	function render_readonly() {
		return $this->render_view() . $this->render_hidden();
	}
	
	/** Add or remove [] at the end of the field name: useful for list of values, i.e. massive actions */
	function setIsArray($isArray=true) {
		$this->isArray = $isArray ? '[]' : '';
	}
	
	/**
	 * @par $only_not_empty bool reads only not-empty values from session
	 * @par $at_index reads the n-th index in an array of values
	 */
	function readValueFromRequest($aRequest, $only_not_empty=false, $at_index=-1) {
		$this->readValueFromArray($aRequest,"field_",$only_not_empty,$at_index);
	}
	function readValueFromArray(&$aArray,$prefix="field_", $only_not_empty=false, $at_index=-1) {
		if(array_key_exists($prefix . $this->aNomeCampo,$aArray)) {
			$this->aValore = $at_index<0 ? $aArray[$prefix.$this->aNomeCampo] : $aArray[$prefix.$this->aNomeCampo][$at_index];
		} elseif(!$only_not_empty) {
			$this->aValore=null;
		}
	}
	function writeValueToArray(&$aArray,$prefix="field_") {
		$aArray[$prefix . $this->aNomeCampo]=$this->aValore=='00:00' ? null : $this->aValore;
	}
	
	function getValue() {	return $this->aValore;	}
	function setValue($v) {	$this->aValore = $v;	}
	
	function getTitle() { return $this->_title; }
	function getDescription() { return $this->_description; }

	function setForm(&$myform) { $this->myform = $myform; }
	function getForm() { return $this->myform; }

}

/** Base Class for all the forms */
class FForm {
	var $nome;
	var $azione;
	var $metodo;
	var $enctype; // multipart/form-data
	var $fields;
	var $groups;
	
	function FForm($nome='', $azione='', $metodo="POST", $enctype='') {
		$this->fields = array(); $this->groups = array();
		$this->nome = $nome; $this->azione = $azione; $this->metodo = $metodo; $this->enctype=$enctype;
	}
	
	//************** OVERRIDE: start.
	//************** overridable functions
	function getDetailIcon() { return ""; }
	function getDetailTitle() { return ""; }
	/** Ritorna i nomi dei campi visibili */
	function getDetailColumnNames() { return array(); }
	/** Ritorna i nomi dei campi in read-only */
	function getDetailReadOnlyColumnNames() { return array(); }
	/** Ritorna i campi per la sola visualizzazione della form */
	function getViewColumnNames() { return $this->getDetailColumnNames(); }
	function getFilterForm() { return null; }
	function getFilterFields() { return array(); }
	/** Ritorna i nomi dei campi in read-only */
	function getFilterReadOnlyColumnNames() { return array(); }
	function getListTitle() { return ""; }
	/** Ritorna i nomi di fields da visualizzare in una lista */
	function getListColumnNames() { return array(); }
	/** Ritorna i nomi di fields editabili in lista */
	function getListEditableColumnNames() { return array(); }
	/** Ritorna un array con nome_gruppo=>'Nome Gruppo' */
	function getDecodeGroupNames() { return array(); }
	function getPagePrefix() { return ""; }
	/** Ritorna la dbe associata alla form */
	function getDBE() { return null; }
	/** Ritorna il codice che identifica la descrizione. */
	function getCodice() { return ""; }
	/** Ritorna una breve descrizione dei valori contenuti. */
	function getShortDescription($dbmgr=null) { return ""; }
	
	/** Ritorna il nome del controller della form. */
// 	function getController() { return ""; } // RRA 2011.04.18: unused up to now
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
	function getActions() { return array(); }
	/**
	 * Returns possible actions on a list of the managed data type (dbe).
	 * Returned array is like:
	 *	action_code => array(
	 *		'label'=>'Action label',
	 *		'page'=>'list_action_page_do.php',
	 *		'icon'=>'actionIcon.jpg',
	 *		'desc'=>'Short action description')
	 *		'js'=>'javascript function',
	 *		'onclick'=>'javascript code')
	 */
	function getListActions() { return array(); }
	//************** OVERRIDE: end.
	
	/**
	 * 2011.40.04
	 * Utility static function for the schema classes returning filterforms, to forms, from forms, etc.
	 */
	static function getInstance($aClassname, $nome='', $azione='', $metodo="POST") {
		$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
		if($formulator!==null)
			return $formulator->getInstance($aClassname,$nome,$azione,$metodo);
		else
			eval("return new $aClassname(\$nome, \$azione , \$metodo);");
	}
	
	function getAction() { return $this->azione; }
	function getMethod() { return $this->metodo; }
	function getName() { return $this->nome; }
	function getEnctype() { return $this->enctype; }
	
	function addField($nomeGruppo='', $ordine=-1, $nomeField, &$aField) {
		$aField->setForm($this);
		$this->fields[$nomeField] = $aField;
		$this->addToGroup($nomeGruppo, $ordine, $nomeField);
		
		if(is_a($aField, 'FFileField'))
			$this->enctype='multipart/form-data';
	}
	
	function getFieldNames() { return array_keys($this->fields); }
	function getField($fieldName) {
		return array_key_exists($fieldName,$this->fields) ? $this->fields[$fieldName] : null;
	}
	
	function decodeGroupName($group_name) {
		$tmp = $this->getDecodeGroupNames();
		return array_key_exists($group_name,$tmp) ? $tmp[$group_name] : $group_name;
	}
	function getGroupNames() { return array_keys($this->groups); }
	function getGroup($nomeGruppo) {
		if(array_key_exists($nomeGruppo,$this->groups)) {
			return $this->groups[$nomeGruppo];
		}
	}
	function getGroupSize($nomeGruppo) {
		return count($this->getGroup($nomeGruppo));
	}
	function setGroup($nomeGruppo, $gruppo) {
		$this->groups[$nomeGruppo] = $gruppo;
	}
	/**
	 * Un gruppo e' un array ordinato di nomi di campo.
	 */
	function addToGroup($nomeGruppo='', $ordine=-1, $nomeField) {
		$gruppo = $this->getGroup($nomeGruppo);
		if($gruppo==null) {	$gruppo=array();	}
		if($ordine<0) {	$ordine = count($gruppo);	}
		$gruppo[$ordine] = $nomeField;
		$this->setGroup($nomeGruppo, $gruppo);
	}
	
	/**
	 * @par $at_index legge all'indice index in un array di valori
	 */
	function readValuesFromRequest($aRequest, $only_not_empty=false, $at_index=-1) {
		foreach($this->getFieldNames() as $nomeCampo) {
			$this->fields[$nomeCampo]->readValueFromRequest($aRequest,$only_not_empty,$at_index);
		}
	}
	function readValuesFromArray($aSession,$prefix="field_", $only_not_empty=false, $at_index=-1) {
		$this->readValuesFromRequest($aSession,$only_not_empty,$at_index);
	}
	function writeValuesToArray(&$aSession,$prefix="field_") {
		foreach($this->getFieldNames() as $nomeCampo) {
			$this->fields[$nomeCampo]->writeValueToArray($aSession);
		}
	}
	/** Ritorna solo i valori dei campi indicati per il filtro */
	function getFilterValues() {
		$ret = array();
		foreach($this->getFilterFields() as $nomeCampo) {
			$ret[$nomeCampo] = $this->fields[$nomeCampo]->getValue();
		}
		return $ret;
	}
	function getValues() {
		$ret = array();
		foreach($this->getFieldNames() as $nomeCampo) {
			$ret[$nomeCampo] = $this->fields[$nomeCampo]->getValue();
		}
		return $ret;
	}
	function setValues($valori) {
		foreach(array_keys($valori) as $chiave) {
			$campo = null;
			if(array_key_exists($chiave,$this->fields)) { $campo = $this->fields[$chiave]; }
			if($campo!=null) {
				$this->fields[$chiave]->setValue($valori[$chiave]);
			}
		}
	}
	function getValue($fieldName) { $tmp = $this->getField($fieldName); return $tmp->getValue(); }
	function setValue($fieldName,$aValue) { $this->fields[$fieldName]->setValue($aValue); }
	
	function render_view(&$dbmgr,$nome_field=null) {
		$ret = '';
		if($nome_field!==null) {
			$_field = $this->getField($nome_field);
			$ret .= $_field->render_view();
		} else {
			/** TODO metodo generico di render_view di una form */
			$viewColumns = $this->getViewColumnNames();
			$ret.="<div id=\"form".$this->nome."\" class=\"formView\">";
			foreach($this->getGroupNames() as $nome_gruppo) {
				if($nome_gruppo=='_permission') continue;
				$_mygroup=$this->getGroup($nome_gruppo);
				$myrows="";
				foreach($_mygroup as $col) {
					if(!in_array($col,$viewColumns)) continue;
					$myfield = $this->getField($col);
					if($myfield===null) {
						echo "$col not found.";
						continue;
					}
					$content=is_a($myfield,'FKField') ? $myfield->render_view($dbmgr) : $myfield->render_view();
					if(!($content>'')) continue;
					$myrows.="<div class=\"formView_row\">";
					$myrows.="<div class=\"formView_title\">".$myfield->getTitle()."</div>";
					$myrows.="<div class=\"formView_value\">$content</div>";
					$myrows.="</div>";
				}
				if(!($myrows>'')) continue;
				$decodedGroupName = $this->decodeGroupName($nome_gruppo);
				if($decodedGroupName>"") {
					$ret.="<div class=\"formView_row\">";
					$ret.="<div class=\"formView_group_left\">&nbsp;</div>";
					$ret.="<div class=\"formView_group\">$decodedGroupName</div>";
					$ret.="</div>";
// 				} else {
// 					$ret.="<div class=\"formView_group\">---</div>";
				}
				$ret.=$myrows;
			}
			$ret.="</div>";
		}
		return $ret;
	}
	
	/**  */
	function to_string() {
		$ret = "Name: " . $this->nome . "\n";
		$ret .= "Action: " . $this->azione . "\n";
		$ret .= "Method: " . $this->metodo . "\n";
		$gruppi = $this->getGroupNames();
		$ret .= "Groups: " . $gruppi . "\n";
		foreach($gruppi as $nomeGruppo) {
			$ret .= "\tGroup: " . $nomeGruppo . "\n";
			$mygruppo = $this->getGroup($nomeGruppo);
			for($i=0; $i<count($mygruppo); $i++) {
				$myfield = $this->getField($mygruppo[$i]);
				$ret .= "\t\t" . $mygruppo[$i] . " => '" . $myfield->getValue() . "'\n";
			}
		}
		return $ret;
	}
}

// class FFilter extends FForm {
// 	function FFilter($nome='', $azione='', $metodo="POST") {
// 		$this->FForm($nome, $azione, $metodo);
// 	}
// }

class FMasterDetail extends FForm {
	var $detailForms;
	var $masterForms; // 2011.09.07: masters to attach to
	function FMasterDetail($nome='', $azione='', $metodo="POST") {
		$this->FForm($nome, $azione, $metodo);
		$this->detailForms=array();
	}
	
	/**
	 * Adds a detail form
	 * @param aDetail form instance OR string with the FForm class name
	 * @param cardinality 1=only one child,  n=n-childs
	 */
	function addDetail($aDetail, $cardinality="n") {
		$this->detailForms[] = $aDetail;
	}
	/** FIXME a better way to do this? */
	function removeDetail($aDetail) {
		$tmp=array();
		foreach($this->detailForms as $detail) {
			if($detail==$aDetail) continue;
			$tmp[]=$detail;
		}
		$this->detailForms=$tmp;
	}
	function getDetailForms() { return $this->detailForms; }
	function setDetailForms($d) { $this->detailForms=$d; }
	function getDetailFormsCount() { return count($this->detailForms); }
	function getDetail($i) {
		if(is_string($this->detailForms[$i])) {
			if(array_key_exists('formulator',$_SESSION) && $_SESSION['formulator']!==null) {
				$myformulator = $_SESSION['formulator'];
				return $myformulator->getInstance($this->detailForms[$i]);
			}
			eval("\$ret = new ".$this->detailForms[$i]."();");
			return $ret;
		} else
			return $this->detailForms[$i];
	}
	
	/**
	 * Adds a master form
	 * @param aMaster istanza di form OPPURE stringa col nome della classe FForm da istanziare
	 * @param cardinality 1=solo un figlio n=n-figli
	 */
	function addMaster($aMaster, $cardinality="n") {
		$this->masterForms[] = $aMaster;
	}
	/** FIXME a better way to do this? */
	function removeMaster($aMaster) {
		$tmp=array();
		foreach($this->masterForms as $m) {
			if($m==$aMaster) continue;
			$tmp[]=$m;
		}
		$this->masterForms=$tmp;
	}
	function getMasterFormsCount() { return $this->masterForms ? count($this->masterForms) : 0; }
	function getMasterName($i) { return $this->masterForms[$i]; }
	function getMaster($i) {
		if(is_string($this->masterForms[$i])) {
			if(array_key_exists('formulator',$_SESSION) && $_SESSION['formulator']!==null) {
				$myformulator = $_SESSION['formulator'];
				return $myformulator->getInstance($this->masterForms[$i]);
			}
			eval("\$ret = new ".$this->masterForms[$i]."();");
			return $ret;
		} else
			return $this->masterForms[$i];
	}
}

/**
 * Classe base per le form responsabili della mappatura di una DBE che rappresenta una associazione N-M su DB
 * Questa associazione può presentare N-attributi, renderizzabili tramite fields
 */
class FAssociation extends FForm {
	var $dbeassociation;
	var $from_form;
	var $to_form;
	function FAssociation($dbeassociation, $from_form, $to_form, $nome='', $azione='', $metodo="POST") {
		parent::FForm($nome, $azione, $metodo);
		
		$this->dbeassociation = $dbeassociation;
		$this->from_form=$from_form;
		$this->to_form=$to_form;
	}
	function getDBE() { return $this->dbeassociation; }
	function getFromForm() { return $this->from_form; }
	function getToForm() {
		return $this->to_form;
	}
	
}

/**
 * Returns the correct class for the given dbe name
 */
class FormFactory {
	var $verbose;
	var $classname2type;
	var $dbename2type;
	var $fieldslist; // 2011.05.16
	var $master_details;
	function FormFactory($verbose = 0) {
		$this->verbose=$verbose;
		$this->classname2type=array("default"=>"FForm",);
		$this->dbename2type=array("default"=>"FForm",);
		
		$this->fieldslist=array(); // 2011.05.16
		$this->master_details=array();
	}
	
	function register($aClassName, $aClassName2=null) {
		eval("\$istanza = new $aClassName();");
		$mydbe = $istanza->getDBE();
		$this->classname2type[$aClassName] = $aClassName2===null ? $aClassName : $aClassName2;
		$this->dbename2type[$mydbe->getTypeName()] = $aClassName2===null ? $aClassName : $aClassName2;
		
		// 2011.05.16
		foreach($istanza->getFieldNames() as $k) {
			if(!in_array($k,array_keys($this->fieldslist)))
				$this->fieldslist[$k] = array();
			$_field_kind = get_class($istanza->getField($k));
			if(!in_array($_field_kind, array_keys($this->fieldslist[$k])))
				$this->fieldslist[$k][$_field_kind]=array();
			$_fieldlist = $this->fieldslist[$k][$_field_kind];
			if(!in_array($aClassName,$_fieldlist)) {
				$_fieldlist[count($_fieldlist)]=$aClassName;
				$this->fieldslist[$k][$_field_kind] = $_fieldlist;
			}
		}
		// Some forms have a master list: let's write this on master_details.
		if(is_a($istanza,'FMasterDetail')) {
			for($m=0; $m<$istanza->getMasterFormsCount(); $m++) {
				$masterName = $istanza->getMasterName($m);
				if(!array_key_exists($masterName,$this->master_details))
					$this->master_details[$masterName] = array();
				$this->master_details[$masterName][]=$aClassName;
			}
		}
	}
	
	function getAllClassnames() {
		return array_keys($this->classname2type);
	}
	
	function getInstance($aClassname, $nome='', $azione='', $metodo="POST") {
		$ret=null;
		$_aClassname = $aClassname;
		if(array_key_exists($aClassname,$this->classname2type)) {
			eval("\$ret = new ".$this->classname2type[$aClassname]."(\$nome, \$azione , \$metodo);");
			$_aClassname=$this->classname2type[$aClassname];
// 			return $ret;
		} elseif(array_search($aClassname, array_values($this->classname2type))) {
			eval("\$ret = new $aClassname(\$nome, \$azione , \$metodo);");
// 			return $ret;
		} elseif($aClassname=="FForm" || $aClassname=="FMasterDetail" || $aClassname=="FAssociation") {
			eval("\$ret = new $aClassname(\$nome, \$azione , \$metodo);");
// 			return $ret;
		} else {
			user_error("FormFactory::getInstance:  NOT found $aClassname");
			$ret = new FForm($nome, $azione, $metodo);
		}
		if(array_key_exists($_aClassname,$this->master_details)) {
			foreach($this->master_details[$_aClassname] as $a_detail_formname) {
				$ret->addDetail($a_detail_formname);
			}
		}
		return $ret;
	}
	
	function getInstanceByDBEName($aDBEName, $nome='', $azione='', $metodo="POST") {
		$ret = null;
		$aClassname="FForm";
		if(array_key_exists($aDBEName,$this->dbename2type)) {
			eval("\$ret = new ".$this->dbename2type[$aDBEName]."(\$nome, \$azione , \$metodo);");
			$aClassname = $this->dbename2type[$aDBEName];
// 			return $ret;
		} else {
			$ret = new FForm($nome, $azione, $metodo);
		}
		if(array_key_exists($aClassname,$this->master_details)) {
			foreach($this->master_details[$aClassname] as $a_detail_formname) {
//			foreach($this->master_details[$masterName] as $a_detail_formname) {
				$ret->addDetail($a_detail_formname);
			}
		}
		return $ret;
	}
	
	function getFormNameByDBEName($aDBEName) {
		$ret = null;
		if(array_key_exists($aDBEName,$this->dbename2type)) {
			$ret = $this->dbename2type[$aDBEName];
		} else {
			$ret = "FForm";
		}
		return $ret;
	}
}

//***************************** Standard Fields

class FNumber extends FField {
	function FNumber($aNomeCampo, $aTitle, $aDescription, $aValore='', $aClasseCss=null, $myform=null) {
		$this->FField($aNomeCampo, $aTitle, $aDescription, 0, 0, $aValore, $aClasseCss, $myform, 'n');
	}
}
class FPercent extends FNumber {
	function render() {
		return parent::render()."&nbsp;%";
	}
	function render_view() {
		$ret = parent::render_view();
		if(strlen($ret)==0)
			$ret='00';
		elseif(strlen($ret)==1)
			$ret="0$ret";
		if(strlen($ret)==2)
			$ret= "&nbsp;$ret";
		return $ret."&nbsp;%";
	}
}
class FString extends FField {
}
class FLanguage extends FString {
}
class FUuid extends FString {
/*	function render_view() {
		return trim($this->aValore);
	}
	function render_hidden() {
		return campoGenerico_hidden($tipoCampo='text', $this->aNomeCampo, trim($this->aValore), $this->aClasseCss);
	}*/
}
class FPassword extends FString {
	var $old_pwd;

	function readValueFromArray(&$aArray,$prefix="field_", $only_not_empty=false, $at_index=-1) {
		if(array_key_exists($prefix.'new_'.$this->aNomeCampo,$aArray)
			&& array_key_exists($prefix.'new2_'.$this->aNomeCampo,$aArray)
			&& $aArray[$prefix.'new_'.$this->aNomeCampo]>''
			&& $aArray[$prefix.'new_'.$this->aNomeCampo]==$aArray[$prefix.'new2_'.$this->aNomeCampo]) {
			$this->aValore = $aArray[$prefix.'new_'.$this->aNomeCampo];
		} elseif(!$only_not_empty) {
			$this->aValore=null;
		}
		// Old Password
		if(array_key_exists($prefix.'old_'.$this->aNomeCampo,$aArray)) {
			$this->old_pwd = $aArray[$prefix.'old_'.$this->aNomeCampo];
		} elseif(!$only_not_empty) {
			$this->old_pwd=null;
		}
	}
	function render() {
		$ret  = campoGenerico_hidden('text', $this->aNomeCampo.$this->isArray, $this->aValore);
		$ret .= campoGenerico($tipoCampo='password', 'old_'.$this->aNomeCampo.$this->isArray, '', $this->aClasseCss, $this->_size, $this->_length)." Old<br/>";
		$ret .= campoGenerico($tipoCampo='password', 'new_'.$this->aNomeCampo.$this->isArray, '', $this->aClasseCss, $this->_size, $this->_length)." New<br/>";
		$ret .= campoGenerico($tipoCampo='password', 'new2_'.$this->aNomeCampo.$this->isArray, '', $this->aClasseCss, $this->_size, $this->_length)." Retype new";
		//$ret = campoGenerico($tipoCampo='password', $this->aNomeCampo, $this->aValore, $this->aClasseCss, $this->_size, $this->_length);
		return $ret;
	}
	function render_view() {
		$ret = "********";
		return $ret;
	}
}
/**
 * Campo per gestire le permissions delle tabelle che estendono objects
 * TODO renderer apposito
 */
class FPermissions extends FString {
	function render() {
		return campoPermissions($this->aNomeCampo, $this->aValore, $this->aClasseCss, false);
	}
	function render_view() {
		return campoPermissions($this->aNomeCampo, $this->aValore, $this->aClasseCss, true);
	}
}

class FFileField extends FField {
	var $dest_directory;
	/**
	 * @param aNomeCampo field name
	 * @param aTitle field title (label)
	 * @param aDescription field description
	 * @param dest_directory destination directory (dove memorizzare i files)
	 * @param aSize field size
	 * @param aLength field length
	 * @param aValore field value
	 * @param aClasseCss css class
	 */
	function FFileField($aNomeCampo, $aTitle, $aDescription, $dest_directory='', $aSize=1, $aLength=20, $aValore='', $aClasseCss=null) {
		$this->FField($aNomeCampo, $aTitle, $aDescription, $aSize, $aLength, $aValore, $aClasseCss);
		
		$this->dest_directory=$dest_directory;
	}
	
	function generaFilename() {
		$dbe = $this->myform->getDBE();
		$dbe->setValuesDictionary($this->myform->getValues());
		return $dbe->generaFilename();
	}
	
	function render_thumbnail($alternative_link='') {
		$download_link = $alternative_link>'' ? $alternative_link : $GLOBALS['files_downloader']."?field_id=".$this->myform->getValue('id');
		return "<a title=\"".$this->myform->getValue('name')."\" href='".$download_link."' target='_download_'>"
			."<img alt=\"".$this->myform->getValue('name')."\" src=\"".$GLOBALS['files_downloader']."?field_id=".$this->myform->getValue('id')."&view_thumb=y\" border=\"0\" />"
// 			."".$this->myform->getValue('name').""
			."</a>";
	}
	function render_image() {
		return "<img alt=\"".$this->myform->getValue('name')."\" src=\"".$GLOBALS['files_downloader']."?field_id=".$this->myform->getValue('id')."\" border=\"0\" />";
	}
	/**
	 * @par alternative_link link alternativo per permettere download da linkbucks :-) 2010.09.27
	 */
	function render_view($with_thumbnail=true,$alternative_link='') {
		$path_relativo="";
		if($this->myform->getValue('path')>'') {
			$path_relativo=$this->myform->getValue('path')."/";
		}
		if($this->myform->getValue('father_id')>0) {
			$path_relativo=$this->myform->getValue('father_id')."/$path_relativo";
		}
		$download_link = $alternative_link>'' ? $alternative_link : $GLOBALS['files_downloader']."?field_id=".$this->myform->getValue('id');
		
		$dbe = $this->myform->getDBE();
		$dbe->setValuesDictionary($this->myform->getValues());
		if($with_thumbnail && is_a($dbe,'DBEFile') && $dbe->isImage()) {
			return $this->render_thumbnail($download_link);
		}
		return "<a href='$download_link' target='_download_'>".$this->myform->getValue('name')."</a>";
	}
	
	function render(&$dbmgr=null) {
		return $this->render_view(). '&nbsp;' . campoGenerico($tipoCampo='file', $this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, $this->_length);
	}

	function readValueFromArray(&$aArray,$prefix="field_", $only_not_empty=false, $at_index=-1) {
		parent::readValueFromArray($aArray,$prefix);
// 		echo "root_directory: " . $GLOBALS['root_directory'] . "<br>\n";
// 		echo "this->dest_directory: " . $this->dest_directory . "<br>\n";
		$dest_dir=realpath($GLOBALS['root_directory'].'/'.$this->dest_directory);
//         echo "dest_dir: $dest_dir<br>\n";

		$nome_campo = $prefix . $this->aNomeCampo;
		if(array_key_exists($nome_campo,$_FILES) && is_uploaded_file($_FILES[$nome_campo]['tmp_name'])) {
			if(!move_uploaded_file($_FILES[$nome_campo]['tmp_name'], $dest_dir."/".$_FILES[$nome_campo]['name'])) {
				user_error("<p>Errore nel caricamento del file!</p>");
                user_error("<!--\n");
                user_error($_SERVER["SCRIPT_FILENAME"]);
                user_error($GLOBALS['root_directory']);
                user_error($this->dest_directory);
                user_error("From: ".$_FILES[$nome_campo]['tmp_name']."\n");
                user_error("To: ".$dest_dir."/".$_FILES[$nome_campo]['name']."\n");
//                 echo("From: ".$_FILES[$nome_campo]['tmp_name']."\n");
//                 echo("To: ".$dest_dir."/".$_FILES[$nome_campo]['name']."\n");
                user_error("-->\n");
				$this->aValore=null;
			} else
				$this->aValore=$_FILES[$nome_campo]['name'];
		}
	}
}

class FList extends FField {
	var $listaValori;
	var $altezza;
	var $multiselezione;
	/**
	 * listaValori = {  k=>v  }
	 * altezza = numero di elementi visualizzati della select
	 * multiselezione
	*/
	function FList($aNomeCampo, $aTitle, $aDescription, $aSize, $aLength=20, $aValore='', $aClasseCss=null, $listaValori, $altezza, $multiselezione) {
		$this->aNomeCampo = $aNomeCampo;
		$this->_title = $aTitle;
		$this->_description = $aDescription;
		$this->_size = $aSize;
		$this->_length = $aLength;
		$this->aValore = $aValore;
		$this->aClasseCss = $aClasseCss;
		
		$this->listaValori = $listaValori;
		$this->altezza = $altezza;
		$this->multiselezione = $multiselezione;
	}
	
	function render() {
		return campoLista($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, $this->_length, $this->listaValori, $this->altezza, $this->multiselezione);
	}
	function render_view() {
		return campoLista_view($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, $this->_length, $this->listaValori, $this->altezza, $this->multiselezione);
	}
}
class FCheckBox extends FField {
	var $listaValori;
	var $altezza;
	var $multiselezione;
	var $stringa_separatrice;
	/**
	 * listaValori = {  k=>v  }
	 * altezza = numero di elementi visualizzati della select
	 * multiselezione
	 * stringa_separatrice: nel caso della multiselezione, viene effettuata una implosione dell'array di stringhe con qyesta stringa come separatore
	*/
	function FCheckBox($aNomeCampo, $aTitle, $aDescription, $aSize, $aLength=20, $aValore='', $aClasseCss=null, $listaValori, $multiselezione=false, $stringa_separatrice="||", $tipo_campo='s') {
		$this->aNomeCampo = $aNomeCampo;
		$this->_title = $aTitle;
		$this->_description = $aDescription;
		$this->_size = $aSize;
		$this->_length = $aLength;
		$this->aValore = $aValore;
		$this->aClasseCss = $aClasseCss;
		
		$this->listaValori = $listaValori;
		$this->multiselezione = $multiselezione;
		$this->stringa_separatrice = $stringa_separatrice;

		$this->tipo = $tipo_campo;
	}
	
	function setValue($v) {
		if(($this->multiselezione===true || $this->multiselezione==1) && is_array($v)) {
			$this->aValore = implode($v, $this->stringa_separatrice);
		} else {
			$this->aValore = $v;
		}
	}
	function readValueFromRequest($aRequest, $only_not_empty=false, $at_index=-1) {
		if(array_key_exists('field_' . $this->aNomeCampo,$aRequest)) {
			$this->setValue(
				$at_index<0 ? $aRequest['field_' . $this->aNomeCampo] : $aRequest['field_' . $this->aNomeCampo][$at_index]
			);
		}
	}
	
	function render() {
		return campoCheckBox($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, $this->_length, $this->listaValori, $this->multiselezione, $this->stringa_separatrice, $this->tipo);
	}
	function render_view() {
		return campoCheckBox_view($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, $this->_length, $this->listaValori, $this->multiselezione, $this->stringa_separatrice, $this->tipo);
	}
}
class FTextArea extends FField {
	var $width;
	var $height;
	var $basicFormatting;
	
	/**
	Parameters:
	- $basic_formatting:	supporto per una formattazione di base, al momento solo gli 'a capo'
	*/
	function FTextArea($aNomeCampo, $aTitle, $aDescription,$aSize, $aValore='YYYY-MM-DD HH:mm', $aClasseCss=null, $width=null, $height=null, $basicFormatting=true) {
		$this->aNomeCampo = $aNomeCampo;
		$this->_title = $aTitle;
		$this->_description = $aDescription;
		$this->_size = $aSize;
		$this->aValore = $aValore;
		$this->aClasseCss = $aClasseCss;
		
		$this->width = $width;
		$this->height = $height;
		$this->basicFormatting = $basicFormatting;
	}
	
	function render() {
		return campoTextArea($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, $this->width, $this->height);
	}
	
	function render_view() {
		return campoTextArea_view($tipoCampo='text', $this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->basicFormatting);
	}
}
class FHtml extends FTextArea {
	function FHtml($aNomeCampo, $aTitle, $aDescription,$aSize, $aValore='YYYY-MM-DD HH:mm', $aClasseCss=null, $width=null, $height=null, $basicFormatting=true) {
		$this->FTextArea($aNomeCampo, $aTitle, $aDescription,$aSize, $aValore, $aClasseCss, $width===null ? 50 : $width, $height===null ? 100 : $height, $basicFormatting);
	}
	function render() {
		return parent::render()
				."<br/>"
				."<font size=\"1\">"
				."Quick tags:<br/>"
				." [[img|&lt;name or uuid&gt;|desc]]: link to internal image.<br/>"
				." [[ext|&lt;external url&gt;|desc]]: link to external url.<br/>"
				." [[&lt;name or uuid&gt;|desc]]: link to internal object.<br/>"
				."</font>";
	}
	function render_view($view_page='main.php',$downloadPage='download.php') {
		return campoHtml_view($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss,$view_page,$downloadPage);
	}
	function render_hidden() {
		return campoGenerico_hidden($tipoCampo='text', $this->aNomeCampo.$this->isArray, htmlentities($this->aValore), $this->aClasseCss);
	}
}
class FDateTime extends FField {
	var $aVisualizzaData=TRUE;
	var $aVisualizzaOra=TRUE;
	
	function FDateTime($aNomeCampo, $aTitle, $aDescription, $aValore, $aClasseCss=null, $aVisualizzaData=TRUE, $aVisualizzaOra=TRUE) {
		$this->aNomeCampo = $aNomeCampo;
		$this->_title = $aTitle;
		$this->_description = $aDescription;
		$this->aValore = $aValore;
		$this->aClasseCss = $aClasseCss;
		$this->tipo='d';
		
		$this->aVisualizzaData = $aVisualizzaData;
		$this->aVisualizzaOra = $aVisualizzaOra;
	}
	
	function render() {
		return campoData($this->aNomeCampo.$this->isArray, $this->getValue(), $this->aClasseCss, $this->aVisualizzaData, $this->aVisualizzaOra);
	}
	
	function render_view() {
		return campoData_view($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->aVisualizzaData, $this->aVisualizzaOra);
	}
	
	function render_hidden() {
		return campoData_hidden($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->aVisualizzaData, $this->aVisualizzaOra);
	}
	
	function readValueFromRequest($aRequest, $only_not_empty=false, $at_index=-1) {
		$tmpdata = '';
		if(array_key_exists('subfield_date_'.$this->aNomeCampo,$aRequest)) {
			$tmpdata = $at_index<0 ? $aRequest['subfield_date_'.$this->aNomeCampo] : $aRequest['subfield_date_'.$this->aNomeCampo][$at_index];
		} elseif(array_key_exists('field_'.$this->aNomeCampo,$aRequest)) {
			// Because of compatibility with DBEntity::getCGIKeysCondition
			$tmpdata = $at_index<0 ? $aRequest['field_'.$this->aNomeCampo] : $aRequest['field_'.$this->aNomeCampo][$at_index];
		} else {
			$anno = '';
			if(array_key_exists('subfield_year_'.$this->aNomeCampo,$aRequest)) {
				$anno = $at_index<0 ? $aRequest['subfield_year_'.$this->aNomeCampo] : $aRequest['subfield_year_'.$this->aNomeCampo][$at_index];
			}
			$mese='';
			if(array_key_exists('subfield_month_'.$this->aNomeCampo,$aRequest)) {
				$mese = $at_index<0 ? $aRequest['subfield_month_'.$this->aNomeCampo] : $aRequest['subfield_month_'.$this->aNomeCampo][$at_index];
			}
			if(strlen($mese)==1) { $mese = "0$mese"; }
			$giorno = '';
			if(array_key_exists('subfield_day_'.$this->aNomeCampo,$aRequest)) {
				$giorno = $at_index<0 ? $aRequest['subfield_day_'.$this->aNomeCampo] : $aRequest['subfield_day_'.$this->aNomeCampo][$at_index];
			}
			if(strlen($giorno)==1) { $giorno = "0$giorno"; }
			if(strlen($anno)>0 && strlen($mese)>0 && strlen($giorno)>0) {
				$tmpdata = "$anno/$mese/$giorno";
			}
		}
		$tmpora = '';
		$ore = '';
		if(array_key_exists('subfield_hour_'.$this->aNomeCampo,$aRequest)) {
			$ore = $at_index<0 ? $aRequest['subfield_hour_'.$this->aNomeCampo] : $aRequest['subfield_hour_'.$this->aNomeCampo][$at_index];
		}
		$minuti = '';
		if(array_key_exists('subfield_minute_'.$this->aNomeCampo,$aRequest)) {
			$minuti = $at_index<0 ? $aRequest['subfield_minute_'.$this->aNomeCampo] : $aRequest['subfield_minute_'.$this->aNomeCampo][$at_index];
		}
		$secondi = '';
		if(array_key_exists('subfield_seconds_'.$this->aNomeCampo,$aRequest)) {
			$secondi = $at_index<0 ? $aRequest['subfield_seconds_'.$this->aNomeCampo] : $aRequest['subfield_seconds_'.$this->aNomeCampo][$at_index];
		}
		if(strlen($ore)>0 && strlen($minuti)>0 && strlen($secondi)>0) {
			$tmpora = "$ore:$minuti:$secondi";
		}
		if($this->aVisualizzaData && $tmpdata!='') {
			$this->aValore = $tmpdata;
			if($this->aVisualizzaOra && $tmpora!='') {
				$this->aValore = $this->aValore . " " . $tmpora;
			}
		} else {
			if($this->aVisualizzaOra)
				$this->aValore = $tmpora;
// 2011.03.14: inizio.
// 			else
// 				$this->aValore = '';
// 2011.03.14: fine.
		}
	}
}
class FDateTimeReadOnly extends FDateTime {
	function render() {
		return $this->render_readonly();
	}
}
class FKField extends FField {
	var $myFK;
	var $viewmode;
	var $destform;
	var $description_glue;
	var $altezza;
	var $multiselezione;
	/**
	 * @param aNomeCampo field name
	 * @param aTitle field title (label)
	 * @param aDescription field description
	 * @param aSize field size
	 * @param aValore field value
	 * @param aClasseCss css class
	 * @param mydbe the dbentity representing the table row
	 * @param myFK the foreign key definition
	 * @param description_columns list of columns on the destination table to join for a textual description
	 * @param destform class name of the destination form
	 * @param viewmode { 'select','readonly' }
	 */
	function FKField($aNomeCampo, $aTitle, $aDescription,$aSize, $aValore=null, $aClasseCss=null,
									$mydbe=null, $myFK=null, $description_columns=array(), $destform=null, $viewmode='select') {
		$this->aNomeCampo = $aNomeCampo;
		$this->_title = $aTitle;
		$this->_description = $aDescription;
		$this->_size = $aSize;
		$this->aValore = $aValore;
		$this->aClasseCss = $aClasseCss;
		
		$this->mydbe=$mydbe;
		$this->myFK=$myFK;
		$this->viewmode=$viewmode;
		$this->destform= $destform;
		$this->description_columns=$description_columns;
		
		$this->description_glue= " - ";
		$this->altezza=1;
		$this->multiselezione=false;
	}
	
	function render_distinct(&$dbmgr) {
		$myquery = "select distinct ".$this->myFK->colonna_fk." from ".$dbmgr->buildTableName($this->mydbe); //." order by ".implode(",",$this->mydbe->getOrderBy());
		$lista = $dbmgr->select($this->mydbe->getTypeName(),$this->mydbe->getTableName(),$myquery);
		$valori=array(); $valori['']='';
		$backup_valore = $this->aValore;
		foreach($lista as $dbe) {
			$dbeFactory = $dbmgr->getFactory();
			$cerca = $dbeFactory->getInstanceByTableName($this->myFK->tabella_riferita);
			$cerca->setValue($this->myFK->colonna_riferita, $dbe->getValue($this->myFK->colonna_fk));
			$lista = $dbmgr->search($cerca, $cerca->getOrderBy());
			if(count($lista)!=1) continue;
			$mydbe=$lista[0];
			if($mydbe==null) continue;
			$description_array=array(); foreach($this->description_columns as $chiave) { $description_array[]=$mydbe->getValue($chiave); }
			$link_desc=implode($this->description_glue, $description_array);
			$valori[$dbe->getValue($this->myFK->colonna_fk)] = $link_desc;
		}
		$this->aValore = $backup_valore;
		return campoLista($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, 50, $valori, $this->altezza, $this->multiselezione);
	}
	
	function render(&$dbmgr=null) {
		if($this->viewmode=='select') {
			$dbeFactory = $dbmgr->getFactory();
			$cerca = $dbeFactory->getInstanceByTableName($this->myFK->tabella_riferita);
			$lista = $dbmgr->search($cerca, $cerca->getOrderBy());
			$lista_valori = array();
			$lista_valori[""]="";
			foreach($lista as $dbe) {
				$chiavi = is_array($dbe->getKeys()) ? array_keys($dbe->getKeys()) : array();
				$chiave_array=array();
				$description_array=array();
				foreach($chiavi as $chiave) { $chiave_array[]=$dbe->getValue($chiave); }
				foreach($this->description_columns as $chiave) { $description_array[]=$dbe->getValue($chiave); }
				$lista_valori[implode("_",$chiave_array)] = implode($this->description_glue, $description_array);
			}
			$link = $this->renderLink($dbmgr);
			return campoLista($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, 50, $lista_valori, $this->altezza, $this->multiselezione)
						. $link;
		} else if($this->viewmode=='distinct') {
			return $this->render_distinct($dbmgr);
		} else if($this->viewmode=='readonly') {
			$link="";
			if($this->destform>"") {
				$link_desc="";
				$dbeFactory = $dbmgr->getFactory();
				$cerca = $dbeFactory->getInstanceByTableName($this->myFK->tabella_riferita);
				$cerca->setValue($this->myFK->colonna_riferita, $this->aValore);
				$lista = $dbmgr->search($cerca, $cerca->getOrderBy());
				if(count($lista)==1) {
					$mydbe=$lista[0];
					$mydestform = null;
					if(array_key_exists('formulator',$_SESSION) && $_SESSION['formulator']!==null) {
						$myformulator = $_SESSION['formulator'];
						$mydestform =  $myformulator->getInstance($this->destform);
					} else
						eval("\$mydestform = new ".$this->destform.";");
					$description_array=array(); foreach($this->description_columns as $chiave) { $description_array[]=$mydbe->getValue($chiave); }
					$link=$mydestform->getPagePrefix()."_modify.php?dbetype=".$mydbe->getTypeName()."&formtype=".$this->destform."&".$mydbe->getCGIKeysCondition();
					$link_desc=implode($this->description_glue, $description_array);
				}
				$link = $link>"" ? "<a href=\"$link\">$link_desc</a>" : "";
			}
			return $this->render_hidden() . $link;
		} else {
			return "FKField::render: mode ".$this->viewmode." not yet implemented!";
		}
	}
	/**
	 * @par mode modify o edit
	 */
	function renderLink(&$dbmgr, $mode="modify") {
		$link="";
		if($this->destform>"") {
			$link_desc="";
			$dbeFactory = $dbmgr->getFactory();
			$cerca = $dbeFactory->getInstanceByTableName($this->myFK->tabella_riferita);
			$cerca->setValue($this->myFK->colonna_riferita, $this->aValore);
			$lista = $dbmgr->search($cerca, $cerca->getOrderBy());
			if(count($lista)==1) {
				$mydbe=$lista[0];
				$mydestform = null;
				if(array_key_exists('formulator',$_SESSION) && $_SESSION['formulator']!==null) {
					$myformulator = $_SESSION['formulator'];
					$mydestform =  $myformulator->getInstance($this->destform);
				} else
					eval("\$mydestform = new ".$this->destform.";");
				$description_array=array(); foreach($this->description_columns as $chiave) { $description_array[]=$mydbe->getValue($chiave); }
				$link=$mydestform->getPagePrefix()."_$mode.php?dbetype=".$mydbe->getTypeName()."&formtype=".$this->destform."&".$mydbe->getCGIKeysCondition();
				$link_desc=implode($this->description_glue, $description_array);
			}
			$link = $link>"" ? "<a href=\"$link\"><img title=\"$link_desc\" alt=\"$link_desc\" border=\"0\" src=\"".getSkinFile("mng/icone/Edit16.gif")."\"/></a>" : "";
		}
		return $link;
	}
	
	function render_view(&$dbmgr=null) {
		$dbeFactory = $dbmgr->getFactory();
		$cerca = $dbeFactory->getInstanceByTableName($this->myFK->tabella_riferita);
		$lista = $dbmgr->search($cerca, $cerca->getOrderBy());
		$lista_valori = array();
		$lista_valori[""]="";
		$link="";
		$link_desc="";
		foreach($lista as $dbe) {
			$chiavi=is_array($dbe->getKeys()) ? array_keys($dbe->getKeys()) : array();
			$chiave_array=array();
			$description_array=array();
			foreach($chiavi as $chiave) { $chiave_array[]=$dbe->getValue($chiave); }
			foreach($this->description_columns as $chiave) { $description_array[]=$dbe->getValue($chiave); }
			$lista_valori[implode("_",$chiave_array)]=implode($this->description_glue, $description_array);
		}
		return campoLista_view($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, 50, $lista_valori, $this->altezza, $this->multiselezione);
	}
	
	function render_readonly(&$dbmgr=null) {
		return $this->render_view($dbmgr) . $this->render_hidden($dbmgr);
	}
}
/**
 * A Foreign Key pointing to multiple destinations
 * @param destform se null ==> cerca tra tutte le FObject, nella form specificata altrimenti
 */
class FKObjectField extends FKField {
	function FKObjectField($aNomeCampo, $aTitle, $aDescription,$aSize, $aValore=null, $aClasseCss=null,
									$mydbe=null, $myFK=null, $description_columns=array(), $destform=null, $viewmode='select') {
		$this->FKField($aNomeCampo, $aTitle, $aDescription,$aSize, $aValore, $aClasseCss,
									$mydbe, $myFK, $description_columns, $destform, $viewmode);
	}
	
	function _searchObject(&$dbmgr) {
		$mydbe=null;
		$mydestform=null;
		$dbeFactory = $dbmgr->getFactory();
		global $formulator; // FIXME: non mi piace global
		$classi = $this->destform==null ? $formulator->getAllClassnames() : array($this->destform);
		if($this->myFK!=null)
			foreach($classi as $nomeclasse) {
				if($nomeclasse=='default') continue;
				$mydestform = null;
				if(array_key_exists('formulator',$_SESSION) && $_SESSION['formulator']!==null) {
					$myformulator = $_SESSION['formulator'];
					$mydestform =  $myformulator->getInstance($nomeclasse);
				} else
					eval("\$mydestform = new ".$nomeclasse."();");
				if(!is_subclass_of($mydestform,"FObject") && !is_subclass_of($mydestform,"fobject")) continue;
				$cerca = $mydestform->getDBE();
				$cerca->setValue($this->myFK->colonna_riferita, $this->aValore);
				$lista = $dbmgr->search($cerca, $cerca->getOrderBy());
				if(count($lista)==1) {
					if($lista[0]->getColumnType($this->myFK->colonna_riferita)=='uuid') {
						if($lista[0]->getValue($this->myFK->colonna_riferita)==$cerca->getValue($this->myFK->colonna_riferita)) {
							$mydbe=$lista[0];
							break;
						}
					} else {
						if($lista[0]->getValue($this->myFK->colonna_riferita)==$cerca->getValue($this->myFK->colonna_riferita)) {
							$mydbe=$lista[0];
							break;
						}
					}
				}
			}
		return array($mydbe, $mydestform, isset($nomeclasse)?$nomeclasse:'');
	}
	
	function render_distinct(&$dbmgr) {
		$myquery = "select distinct ".$this->myFK->colonna_fk." from ".$dbmgr->buildTableName($this->mydbe); //." order by ".implode(",",$this->mydbe->getOrderBy());
		$lista = $dbmgr->select($this->mydbe->getTypeName(),$this->mydbe->getTableName(),$myquery);
		$valori=array(); $valori['']='&lt;All&gt;'; $valori['00']='&lt;Unbound&gt;';
		$backup_valore = $this->aValore;
		foreach($lista as $dbe) {
			$this->aValore=$dbe->getValue($this->myFK->colonna_fk);
			$tmp = $this->_searchObject($dbmgr);
			$mydbe=$tmp[0]; $mydestform=$tmp[1]; $nomeclasse=$tmp[2];
			if($mydbe==null) continue;
			$description_array=array(); foreach($this->description_columns as $chiave) { $description_array[]=$mydbe->getValue($chiave); }
			$link_desc=implode($this->description_glue, $description_array);
			$valori[$dbe->getValue($this->myFK->colonna_fk)] = $link_desc;
		}
		$this->aValore = $backup_valore;
		return campoLista($this->aNomeCampo.$this->isArray, $this->aValore, $this->aClasseCss, $this->_size, 50, $valori, $this->altezza, $this->multiselezione);
	}
	
	function render(&$dbmgr=null) {
		if($this->viewmode=='distinct') {
			return $this->render_distinct($dbmgr);
		}
		$tmp=$this->_searchObject($dbmgr);
		$mydbe=$tmp[0]; $mydestform=$tmp[1]; $nomeclasse=$tmp[2];
		if($mydbe==null) return '';
		$description_array=array(); foreach($this->description_columns as $chiave) { $description_array[]=$mydbe->getValue($chiave); }
		$link=$mydestform->getPagePrefix()."_modify.php?dbetype=".$mydbe->getTypeName()."&formtype=".$nomeclasse."&".$mydbe->getCGIKeysCondition();
		$link_desc=implode($this->description_glue, $description_array);
		$ret = "<div id=\"".$this->aNomeCampo."_link\" style=\"display:inline;\">";
		$ret.= $link>"" ? "<a href=\"$link\">$link_desc</a>" : "";
		$ret.="</div>";
		$ret.="<img  title=\"Link/Unlink\"  alt=\"Link/Unlink\" src=\"".getSkinFile("mng/icone/link_selector.gif")."\" ";
		$ret.="onclick=\"javascript:window.open('popup_objtree.php?fieldName=".$this->aNomeCampo."','Select','toolbar=no,menubar=no,resizable=yes,scrollbars=yes,width=200,height=300,top=10,left=10');\" ";
		$ret.="border=\"0\" ";
		$ret.="onmouseover=\"javascript:this.style.cursor='pointer'\" ";
		$ret.="onmouseout=\"javascript:this.style.cursor='normal'\"/>";
		return $this->render_hidden($dbmgr).$ret;
	}
	
	function render_view(&$dbmgr=null, $showlink=false) {
		$tmp=$this->_searchObject($dbmgr);
		$mydbe=$tmp[0]; $mydestform=$tmp[1]; $nomeclasse=$tmp[2];
		if($mydbe==null) return '';
		$description_array=array(); foreach($this->description_columns as $chiave) { $description_array[]=$mydbe->getValue($chiave); }
		$link=$mydestform->getPagePrefix()."_view.php?dbetype=".$mydbe->getTypeName()."&formtype=".$nomeclasse."&".$mydbe->getCGIKeysCondition();
		$link_desc=implode($this->description_glue, $description_array);
		$ret = $showlink ? "<a href=\"$link\">$link_desc</a>" : $link_desc;
		return $ret;
	}
}
class FChildSort extends FList {
	function render(&$dbmgr=null) {
		$current_obj_id = $this->getForm()->getValue('id');
		if($current_obj_id===0 || $current_obj_id==='') return ""; // Nothing to sort :-P
		// 2012.02.25: start.
		$childs = array();
// 		$search = new DBEObject();
// 		$search->setValue('father_id',$current_obj_id);
// 		$childs = $dbmgr->search($search,$uselike=0);
		// 2012.02.25: end.
		$mydbe = $this->getForm()->getDBE();
		$mydbe->setValuesDictionary($this->getForm()->getValues());
		for($i=0; $i<$this->getForm()->getDetailFormsCount(); $i++) {
			$childForm = $this->getForm()->getDetail($i);
			$childDbe = $childForm->getDBE();
			$childDbe->readFKFrom($mydbe);
			$tmp = $dbmgr->search($childDbe,$uselike=0);
			foreach($tmp as $_linked_child) $childs[]=$_linked_child;
		}
		$listaValori = array();
		$childs_sort_order=preg_split("/,/",$this->aValore);
		foreach($childs_sort_order as $_oid) {
			for($_i=0; $_i<count($childs); $_i++) {
				if($childs[$_i]->getValue('id')!=$_oid) continue;
				$listaValori[$childs[$_i]->getValue('id')]=$childs[$_i]->getValue('name');
				array_splice($childs, $_i,1);
			}
		}
		foreach($childs as $_item) $listaValori[$_item->getValue('id')]=$_item->getValue('name');
		$this->aValore=implode(",",array_keys($listaValori));
		// Render
		$ret = "<div id=\"child_sort_$current_obj_id\" style=\"border: 0px solid #0000ff;\">".$this->render_hidden();
		$ret.= campoLista($this->aNomeCampo."_list", '',$this->aClasseCss, $this->_size, $this->_length, $listaValori, min($this->altezza,count($listaValori)), $this->multiselezione);
		$ret.="<br/>";
		$ret.="<script type=\"text/javascript\">";
		$ret.="function child_sort_{$current_obj_id}_up() {\n";
		$ret.=" var x=document.getElementsByName(\"field_{$this->aNomeCampo}_list\")[0];\n";
		$ret.=" idx = x.selectedIndex;\n";
		$ret.=" if(idx==0) return;\n";
		$ret.=" o1_value=x.options[idx-1].value;\n";
		$ret.=" o1_text=x.options[idx-1].text;\n";
		$ret.=" o2_value=x.options[idx].value;\n";
		$ret.=" o2_text=x.options[idx].text;\n";
		$ret.=" x.options[idx-1].value=o2_value;\n";
		$ret.=" x.options[idx-1].text=o2_text;\n";
		$ret.=" x.options[idx].value=o1_value;\n";
		$ret.=" x.options[idx].text=o1_text;\n";
		$ret.=" x.selectedIndex=idx-1;\n";
		$ret.=" child_sort_{$current_obj_id}_update_value();\n";
		$ret.="}\n";
		$ret.="function child_sort_{$current_obj_id}_down() {\n";
		$ret.=" var x=document.getElementsByName(\"field_{$this->aNomeCampo}_list\")[0];\n";
		$ret.=" idx = x.selectedIndex;\n";
		$ret.=" if(idx==x.length) return;\n";
		$ret.=" o1_value=x.options[idx].value;\n";
		$ret.=" o1_text=x.options[idx].text;\n";
		$ret.=" o2_value=x.options[idx+1].value;\n";
		$ret.=" o2_text=x.options[idx+1].text;\n";
		$ret.=" x.options[idx].value=o2_value;\n";
		$ret.=" x.options[idx].text=o2_text;\n";
		$ret.=" x.options[idx+1].value=o1_value;\n";
		$ret.=" x.options[idx+1].text=o1_text;\n";
		$ret.=" x.selectedIndex=idx+1;\n";
		$ret.=" child_sort_{$current_obj_id}_update_value();\n";
		$ret.="}\n";
		$ret.="function child_sort_{$current_obj_id}_update_value() {\n";
		$ret.=" var x=document.getElementsByName(\"field_{$this->aNomeCampo}\")[0];\n";
		$ret.=" var myselect=document.getElementsByName(\"field_{$this->aNomeCampo}_list\")[0];\n";
// 		$ret.=" alert(x.value);\n";
		$ret.=" var ids=[];\n";
		$ret.=" for(i=0;i<myselect.length;i++) {\n";
		$ret.="  ids.push(myselect.options[i].value);\n";
		$ret.=" }\n";
		$ret.=" x.value=ids.join(\",\");\n";
// 		$ret.=" alert(x.value);\n";
		$ret.="\n";
		$ret.="\n";
		$ret.="}\n";
		$ret.="</script>";
		$ret.="<div style=\"border: 0px solid #0000ff;\">";
		$ret.="<input type=\"image\" src=\"".getSkinFile("mng/icone/Up16.gif")."\" onclick=\"javascript:child_sort_{$current_obj_id}_up();return false\" />&nbsp;&nbsp;&nbsp;";
		$ret.="<input type=\"image\" src=\"".getSkinFile("mng/icone/Down16.gif")."\" onclick=\"javascript:child_sort_{$current_obj_id}_down();return false;\"/>";
		$ret.="</div>";
		$ret.="</div>";
		return $ret;
	}
	function render_hidden() {
		return campoGenerico_hidden($tipoCampo='text', $this->aNomeCampo.$this->isArray, htmlentities($this->aValore), $this->aClasseCss);
	}
}


/**
 * Render a generic field
 * @param tipoCampo
 * @param aNomeCampo
 * @param aValore
 * @param aClasseCss
 * @param size
 * @param length
 */
function campoGenerico($tipoCampo='text', $aNomeCampo='', $aValore='', $aClasseCss=null, $size=null, $length=null) {
	if($tipoCampo=='password') {
		$lung = strlen($aValore);
		$aValore = '';
		for($i=0;$i<$lung; $i++) { $aValore .= '*'; }
	}
	$ret = '';
	$ret .= "<input type=\"" . $tipoCampo . "\" id=\"field_" . $aNomeCampo . "\" name=\"field_" . $aNomeCampo . "\" value=\"" . $aValore . "\" ";
	if($aClasseCss!=null) { $ret .= "class=\"" . $aClasseCss . "\" "; }
	if($size!=null) { $ret .= "size=\"" . $size . "\" "; }
	if($length!=null) { $ret .= "maxlength=\"" . $length . "\" "; }
	$ret .= " />";
	return $ret;
}
function campoGenerico_view($tipoCampo='text', $aNomeCampo, $aValore='', $aClasseCss=null) {
	return $aValore;
}
function campoGenerico_hidden($tipoCampo='text', $aNomeCampo, $aValore='', $aClasseCss=null) {
	$ret = "<input type=\"hidden\" id=\"field_" . $aNomeCampo . "\" name=\"field_" . $aNomeCampo . "\" value=\"" . $aValore . "\" />";
	return $ret;
}

/** Render a textarea */
function campoTextArea($aNomeCampo, $aValore='', $aClasseCss, $size=null, $width=null, $height=null) {
	$ret="<textarea id=\"field_" . $aNomeCampo . "\" name=\"field_" . $aNomeCampo . "\" ";
	if($aClasseCss!=null) { $ret .= "class=\"" . $aClasseCss . "\" "; }
	if($size!=null) { $ret .= "size=\"" . $size . "\" "; }
	if($width!=null) { $ret .= "cols=\"" . $width . "\" "; }
	if($height!=null) { $ret .= "rows=\"" . $height . "\" "; }
	$ret .= ">" . $aValore . "</textarea>";
	return $ret;
}
function campoTextArea_view($tipoCampo='text', $aNomeCampo, $aValore='', $aClasseCss=null, $basicFormatting) {
	$_mytext = $aValore;
	if($basicFormatting==true or $basicFormatting==1) {
		$_mytext = str_replace("\n",'<br>',$aValore);
	}
	return $_mytext;
}
// VIM rimpiazza i link: s/http\(.\{-}\)<br\/>/\[\[ext\|http\1\]\]<br\/>/
function campoHtml_view($aNomeCampo, $aValore='', $aClasseCss=null, $viewPage='main.php', $downloadPage='download.php') {
	$ret=array();
	$righe = explode("\n",$aValore);
	foreach($righe as $r) {
		$indice1 = strpos($r,"[[");
		$indice2 = $indice1===false ? false : strpos($r,"]]",$indice1);
		if(($indice2-$indice1)>0) {
			while(($indice2-$indice1)>0) {
				$k = substr($r, $indice1+2, ($indice2-$indice1)-2);
				$__type='link';
				$__key=$k;
				$__title=$k;
				$__key_field='name';
				if(strpos($k,"|")!==false) {
					$__tmp=explode("|",$k);
					$__type=$__tmp[0];
					$__key=$__tmp[1];
					$__title=count($__tmp)>2 ? $__tmp[2] : $__tmp[1];
					if($__key>'') {
						$__key_field='field_id';
					} else {
						$__key= $__title;
					}
				}
				switch($__type) {
					case 'img':
						$r=str_replace("[[$k]]","<img title=\"$__title\" alt=\"$__title\" src=\"$downloadPage?name=$__key\" />",$r);
						break;
					case 'ext': // External Link
						$r=str_replace("[[$k]]","<a href=\"$__key\" target=\"_blank\">$__title</a>",$r);
						break;
					default: // Internal page
						$r=str_replace("[[$k]]","<a href=\"$viewPage?$__key_field=$__key\">$__title</a>",$r);
						break;
				}
				$indice1 = strpos($r,"[[");
				$indice2 = $indice1===false ? false : strpos($r,"]]",$indice1);
			}
			$ret[]=$r;
		} else {
			$ret[]=$r;
		}
	}
	return implode("\n", $ret);
}
/**	Renderizza una lista di valori predefiniti	*/
function campoLista($aNomeCampo, $aValore, $aClasseCss, $size, $length, $listaValori, $altezza, $multiselezione) {
	$ret = "<select name=\"field_" . $aNomeCampo . "\" size=\"" . $altezza . "\" class=\"$aClasseCss\">";
	foreach($listaValori as $k=>$v) {
		$ret .= "<option value=\"" . $k . "\" " . ($k==$aValore ? "selected" : "") . ">" . $v . "</option>";
	}
	$ret .="</select>";
	return $ret;
}
function campoLista_view($aNomeCampo, $aValore, $aClasseCss, $size, $length, $listaValori, $altezza, $multiselezione) {
	$ret = '';
	if(array_key_exists($aValore,$listaValori) && $listaValori[$aValore]!=null) { $ret = $listaValori[$aValore]; }
	return $ret;
}

/**	Renderizza una lista di valori predefiniti	*/
function campoCheckBox($aNomeCampo, $aValore, $aClasseCss, $size, $length, $listaValori, $multiselezione, $stringa_separatrice, $tipo_campo) {
	$tipo_input="radio";
	if($multiselezione===true || $multiselezione==1) {
		$tipo_input="checkbox";
		$aNomeCampo .= "[]";
		if(!is_array($aValore)) $aValore = explode($stringa_separatrice, $aValore);
	} else {
		if($tipo_campo=="s") $aValore = "$aValore";
	}
	$ret='';
	foreach($listaValori as $k=>$v) {
		$my_k = $tipo_campo=="s" ? "".$k : $k;
		$ret .= "<input type=\"" . $tipo_input . "\" name=\"field_" . $aNomeCampo . "\" value=\"" . $k . "\" ";
		if($aClasseCss!=null) { $ret .= "class=\"" . $aClasseCss . "\" "; }
		if($size!=null) { $ret .= "size=\"" . $size . "\" "; }
		if($length!=null) { $ret .= "maxlength=\"" . $length . "\" "; }
		if($multiselezione===true || $multiselezione==1) {
			foreach($aValore as $__k=>$tmpValore) {
				$__tmpValore = $tipo_campo=='s' ? "$tmpValore" : $tmpValore;
				if($my_k===$__tmpValore) {	$ret .= " checked "; break;	}
			}
		} else {
			if($my_k===$aValore) { $ret .= " checked "; }
		}
		$ret .= ">&nbsp;";
		$ret .= $v;
	}
	
	return $ret;
}
function campoCheckBox_view($aNomeCampo, $aValore, $aClasseCss, $size, $length, $listaValori, $multiselezione, $stringa_separatrice, $tipo_campo) {
	$ret = '';
	if($multiselezione===true || $multiselezione==1) {
		$aValore = explode($stringa_separatrice, $aValore);
		$tmp = array();
		foreach($aValore as $v) {
			if(array_key_exists($v, $listaValori) && $listaValori[$v]!=null)	{
				$tmp[] = $listaValori[$v];
			}
		}
		$ret = implode($tmp, " ");
	} else {
		if(array_key_exists($aValore,$listaValori) && $listaValori[$aValore]!=null) {
			$ret = $listaValori[$aValore];
		}
	}
	return $ret;
}

/**	Renderizza un campo data in modalita' hidden. */
function campoData_hidden($aNomeCampo, $aValore='YYYY-MM-DD HH:mm', $aClasseCss=null, $aVisualizzaData=TRUE, $aVisualizzaOra=TRUE) {
	if(strlen($aValore)==8) $aValore="YYYY-MM-DD $aValore";
	$_anno = substr($aValore, 0,4);	$_mese = substr($aValore, 5, 2);	$_giorno = substr($aValore, 8 , 2);
	$_ore = substr($aValore, 11,2);		$_minuti = substr($aValore, 14,2);  $_secondi=substr($aValore, 17,2);
	$ret = '';
	if($aVisualizzaData) {
		$ret .= "<input type=\"hidden\" name=\"subfield_day_".$aNomeCampo."\" value=\"$_giorno\"><input type=\"hidden\" name=\"subfield_month_".$aNomeCampo."\" value=\"$_mese\"><input type=\"hidden\" name=\"subfield_year_".$aNomeCampo."\" value=\"$_anno\">";
	}
	if($aVisualizzaOra) {
		$ret .= "<input type=\"hidden\" name=\"subfield_hour_".$aNomeCampo."\" value=\"$_ore\"><input type=\"hidden\" name=\"subfield_minute_".$aNomeCampo."\" value=\"$_minuti\"><input type=\"hidden\" name=\"subfield_seconds_".$aNomeCampo."\" value=\"$_secondi\">";
	}
	return $ret;
}
/**	Renderizza un campo data in modalita' vista. */
function campoData_view($aNomeCampo, $aValore='YYYY-MM-DD HH:mm', $aClasseCss=null, $aVisualizzaData=TRUE, $aVisualizzaOra=TRUE) {
	if(strlen($aValore)==8) $aValore="YYYY-MM-DD $aValore";
	$_anno = substr($aValore, 0,4);	$_mese = substr($aValore, 5, 2);	$_giorno = substr($aValore, 8 , 2);
	$_ore = substr($aValore, 11,2);	$_minuti = substr($aValore, 14,2);  $_secondi=substr($aValore, 17,2);
	
	$ret = '';
	if($aVisualizzaData) {
		if($_anno=='0000' && $_mese=='00' && $_giorno=='00') {
			$ret .= '--/--/----';
		} else
			$ret .= $_giorno . "/" . $_mese . "/" . $_anno;
		if($aVisualizzaOra) {
			$ret .= "&nbsp;";
		}
	}
	if($aVisualizzaOra) {
		if($_ore=='00' && $_minuti=='00' && $_secondi=='00')
			$ret .= "--:--:--";
		else
			$ret .= $_ore . ":" . $_minuti.":".$_secondi;
	}
	return $ret;
}
/** Renderizza un campo data nel formato: YYYY-MM-DD HH:mm */
function campoData($aNomeCampo, $aValore, $aClasseCss=null, $aVisualizzaData=TRUE, $aVisualizzaOra=TRUE) {
	if(strlen($aValore)==8) $aValore="YYYY-MM-DD $aValore";
	$_anno = substr($aValore, 0,4);	$_mese = substr($aValore, 5,2);	$_giorno = substr($aValore, 8,2);
	$_ore = substr($aValore, 11,2);	$_minuti = substr($aValore, 14,2);  $_secondi=substr($aValore, 17,2);
	$ret = '';
	
	global $VECCHIO_CALENDARIO;
	global $calendar;
	global $calendar_writtenLoadFiles;
	
	$formato_data = ""; $miovalore="";
	if($aVisualizzaData) { $formato_data.="%Y-%m-%d"; $miovalore.=substr($aValore,0,10); }
	$_oggi = strtotime('now');
	
	if($aVisualizzaData) {
		if(!$VECCHIO_CALENDARIO) {
			ob_start();
			// JsCalendar: inizio.
			$lang = array_key_exists('lang',$_GET) ? $_GET['lang'] : null;
			if($lang==null)	$lang = array_key_exists('lang',$_REQUEST) ? $_REQUEST['lang'] : null;
			if($lang==null)	$lang = 'en';
	// 		$calendar = new DHTML_Calendar(ROOT_FOLDER.'formulator/jscalendar-1.0/', $lang, 'calendar-win2k-2', false);
			if(!$calendar_writtenLoadFiles) { $calendar->load_files(); $calendar_writtenLoadFiles=true; }
			$attributiHtml = array('style' => 'text-align: center',
					'name'=> 'subfield_date_'.$aNomeCampo.'',
					'value' => $miovalore,
					);
			if($aClasseCss!=null && $aClasseCss!='') $attributiHtml['class']="$aClasseCss";
			$calendar->make_input_field(
				// calendar options go here; see the documentation and/or calendar-setup.js
				array('firstDay' => 1, // show Monday first
					'showsTime' => false,
					'showOthers' => true,
					'ifFormat' => $formato_data,
	// 				'timeFormat' => '24'
					),
				// Attributi standard
				$attributiHtml
			);
			// JsCalendar: fine.
			$ret .= ob_get_contents();
			ob_end_clean();
		} else {
			// Giorno
			$ret .= "<select ";
			if($aClasseCss!=null && $aClasseCss!='') $ret .= "class=\"$aClasseCss\" ";
			$ret .= "name=\"subfield_day_".$aNomeCampo."\"><option value=''>--</option>";
			for($_day=1; $_day<=31; $_day++) {
				$ret .="<option value=\"" . sprintf("%02d", $_day) . "\" ";
				if($_day==$_giorno) { $ret .= "selected"; }
				$ret .=">" . sprintf("%02d", $_day) . "</option>";
			}
			$ret .= "</select> / ";
			// Mese
			$ret .= "<select ";
			if($aClasseCss!=null && $aClasseCss!='') $ret .= "class=\"$aClasseCss\" ";
			$ret .= "name=\"subfield_month_".$aNomeCampo."\"><option value=''>--</option>";
			for($_month=1; $_month<=12; $_month++) {
				$ret .="<option value=\"" . sprintf("%02d", $_month) . "\" ";
				if($_month==$_mese) { $ret .= "selected"; }
				$ret .=">" . sprintf("%02d", $_month) . "</option>";
			}
			$ret .= "</select> / ";
			// Anno
			$ret .= "<select ";
			if($aClasseCss!=null && $aClasseCss!='') $ret .= "class=\"$aClasseCss\" ";
			$ret .= "name=\"subfield_year_".$aNomeCampo."\"><option value=''>--</option>";
			for($_year=2000; $_year<=2020; $_year++) {
				$ret .="<option value=\"" . sprintf("%02d", $_year) . "\" ";
				if($_year==$_anno) { $ret .= "selected"; }
				$ret .=">" . sprintf("%02d", $_year) . "</option>";
			}
			$ret .= "</select>";
		}
	} else {
		echo "<input type=\"hidden\" name=\"subfield_day_".$aNomeCampo."\" value=\"00\" />";
		echo "<input type=\"hidden\" name=\"subfield_month_".$aNomeCampo."\" value=\"00\" />";
		echo "<input type=\"hidden\" name=\"subfield_year_".$aNomeCampo."\" value=\"0000\" />";
	}
	if($aVisualizzaData && $aVisualizzaOra) $ret .= "&nbsp;";
	if($aVisualizzaOra) {
		// Ore
		$ret .= "<select ";
		if($aClasseCss!=null && $aClasseCss!='') $ret .= "class=\"$aClasseCss\" ";
		$ret .= "name=\"subfield_hour_".$aNomeCampo."\"><option value=''>--</option>";
		for($_hour=0; $_hour<=23; $_hour++) {
			$ret .="<option value=\"" . sprintf("%02d", $_hour) . "\" ";
			if($_hour==$_ore) { $ret .= "selected"; }
			$ret .=">" . sprintf("%02d", $_hour) . "</option>";
		}
		$ret .= "</select> : ";
		// Minuti
		$ret .= "<select ";
		if($aClasseCss!=null && $aClasseCss!='') $ret .= "class=\"$aClasseCss\" ";
		$ret .= "name=\"subfield_minute_".$aNomeCampo."\"><option value=''>--</option>";
		for($_minute=0; $_minute<=59; $_minute++) {
			$ret .="<option value=\"" . sprintf("%02d", $_minute) . "\" ";
			if($_minute==$_minuti) { $ret .= "selected"; }
			$ret .=">" . sprintf("%02d", $_minute) . "</option>";
		}
		$ret .= "</select> : ";
		// Secondi
		$ret .= "<select ";
		if($aClasseCss!=null && $aClasseCss!='') $ret .= "class=\"$aClasseCss\" ";
		$ret .= "name=\"subfield_seconds_".$aNomeCampo."\"><option value=''>--</option>";
		for($_second=0; $_second<=59; $_second++) {
			$ret .="<option value=\"" . sprintf("%02d", $_second) . "\" ";
			if($_second==$_secondi) { $ret .= "selected"; }
			$ret .=">" . sprintf("%02d", $_second) . "</option>";
		}
		$ret .= "</select>";
	} else {
		echo "<input type=\"hidden\" name=\"subfield_hour_".$aNomeCampo."\" value=\"00\" />";
		echo "<input type=\"hidden\" name=\"subfield_minute_".$aNomeCampo."\" value=\"00\" />";
		echo "<input type=\"hidden\" name=\"subfield_seconds_".$aNomeCampo."\" value=\"00\" />";
	}
	
	return $ret;
} // campoData: fine.


function campoPermissions($aNomeCampo, $aValore='', $aClasseCss=null, $readonly=false) {
	$ret = '';
	$ret .= "<input type=\"hidden\" id=\"field_{$aNomeCampo}\" name=\"field_{$aNomeCampo}\" value=\"{$aValore}\" />";
	$ret .= "<script type=\"text/javascript\">";
	$ret .= "function {$aNomeCampo}_select(adiv,indice,diritto) {";
	if(!$readonly) {
// 		$ret .= "alert(adiv.parentNode.attributes[0].value);";
// 		$ret .= "alert(document.getElementById(\"field_{$aNomeCampo}\").value);";
		// 2012.05.17: start.
		$ret.="var indiceClasse=0;";
		$ret.="for(i=0;i<adiv.attributes.length;i++) { ";
		$ret.="if(adiv.attributes[i].name=='class') { indiceClasse=i; break; }";
		//$ret.="alert(adiv.attributes[i].name+' '+adiv.attributes[i].value);";
		$ret.="}";
		// 2012.05.17: end.
		$ret .= "var myclass=adiv.attributes[indiceClasse].value;";
		$ret .= "var myvalue=document.getElementById(\"field_{$aNomeCampo}\").value;";
		$ret .= " if(myclass=='permission'){";
		$ret .= "  myclass='permissionSelected';";
		$ret .= "  myvalue=myvalue.substring(0,indice)+diritto+myvalue.substring(indice+1);";
		$ret .= " }else{";
		$ret .= "  myclass='permission';";
		$ret .= "  myvalue=myvalue.substring(0,indice)+'-'+myvalue.substring(indice+1);";
		$ret .= " };";
		$ret .= "adiv.attributes[indiceClasse].value=myclass;";
		$ret .= "document.getElementById(\"field_{$aNomeCampo}\").value=myvalue;";
// 		$ret .= "alert(document.getElementById(\"field_{$aNomeCampo}\").value);";
	}
	$ret .= "}";
	$ret .= "</script>";
	$ret .= "<table class=\"formtable\">";
	$ret .= "<tr><th colspan=\"3\" class=\"formtable\">User</th><th colspan=\"3\" class=\"formtable\">Group</th><th colspan=\"3\" class=\"formtable\">Everybody</th>";
	$ret .= "</tr>";
	$ret .= "<tr>";
	// User
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[0]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,0,'r');\">Read</div></td>";
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[1]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,1,'w');\">Write</div></td>";
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[2]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,2,'x');\">Exec</div></td>";
	// Group
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[3]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,3,'r');\">Read</div></td>";
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[4]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,4,'w');\">Write</div></td>";
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[5]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,5,'x');\">Exec</div></td>";
	// World
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[6]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,6,'r');\">Read</div></td>";
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[7]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,7,'w');\">Write</div></td>";
	$ret .= "<td class=\"formtable\"><div class=\"permission".($aValore[8]=='-'?'':'Selected')."\" onclick=\"javascript:{$aNomeCampo}_select(this,8,'x');\">Exec</div></td>";
	$ret .= "</tr>";
	$ret .= "</table>";
// 	if($aClasseCss!=null) { $ret .= "class=\"" . $aClasseCss . "\" "; }
	return $ret;
}

?>
