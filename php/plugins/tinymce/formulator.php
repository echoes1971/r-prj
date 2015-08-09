<?php
/**
 * Estensioni al formulator
 */


class FHtmlEdit extends FHtml {
	function js_tinymce() {
		$ret = "";
		$ret .= "<script type=\"text/javascript\" src=\"".getPluginSkinFile("tinymce","")."jscripts/tiny_mce/tiny_mce.js\"></script>";
		$ret .= "<script type=\"text/javascript\">";
		$ret .= "tinyMCE.init({ mode : \"exact\", elements: \"field_".$this->aNomeCampo."\", theme : \"advanced\",theme_advanced_toolbar_location : \"top\", });";
//		$ret .= "tinyMCE.init({ mode : \"exact\", elements: \"field_".$this->aNomeCampo."\", theme : \"simple\"});";
		$ret .= "</script>";
		// tinyMCE.execCommand('mceAddControl', false, props.id);
// 		$ret .= "<div onclick=\"javascript:tinyMCE.execCommand('mceToggleEditor', false, 'field_".$this->aNomeCampo."');\">Toggle plain / html editor</div>";
		$ret .= "<input type=\"button\" class=\"formtable\" onclick=\"javascript:if(tinyMCE.getInstanceById('field_".$this->aNomeCampo."')==null){tinyMCE.execCommand('mceAddControl', false, 'field_".$this->aNomeCampo."');}else{tinyMCE.execCommand('mceRemoveControl', false, 'field_".$this->aNomeCampo."');};\" value=\"plain/html\" />";
// 		$ret .= "<input type=\"button\" class=\"formtable\" onclick=\"javascript:tinyMCE.execCommand('mceToggleEditor', false, 'field_".$this->aNomeCampo."');\" value=\"plain/html\" />";
// 		$ret .= "<input type=\"button\" class=\"formtable\" onclick=\"javascript:tinyMCE.execCommand('mceAddControl', false, 'field_".$this->aNomeCampo."');\" value=\"add\" />";
// 		$ret .= "<input type=\"button\" class=\"formtable\" onclick=\"javascript:tinyMCE.execCommand('mceRemoveControl', false, 'field_".$this->aNomeCampo."');\" value=\"remove\" />";
// 		$ret .= "<input type=\"button\" class=\"formtable\" onclick=\"javascript:alert(tinyMCE.getInstanceById('field_".$this->aNomeCampo."')==null);\" value=\"mymce\" />";
		return $ret;
	}
	function render() {
		return $this->js_tinymce().parent::render();
	}
// 	function render_view() {
// 		return parent::render_view().$this->popupDomaintools_view();
// 	}
}



?>
