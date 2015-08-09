<?php

/** *************** Modify existing types ********************** */
class FPage2 extends FPage {
	function FPage2( $nome='', $azione='', $metodo="POST" ) {
		parent::FPage( $nome, $azione, $metodo );
		
		$this->fields[ 'html' ] = new FHtmlEdit( 'html', "Html", 'Contenuto html.', 255, '', $aClasseCss='formtable', 60, 20);
	}
}

class FNews2 extends FNews {
	function FNews2( $nome='', $azione='', $metodo="POST" ) {
		parent::FNews( $nome, $azione, $metodo );
		
		$this->fields[ 'html' ] = new FHtmlEdit( 'html', "Html", 'Contenuto html.', 255, '', $aClasseCss='formtable', 60, 20);
	}
}

if(isset($formulator) && $formulator!==null) {
	$formulator->register("FPage","FPage2");
 	$formulator->register("FNews","FNews2");
}


?>
