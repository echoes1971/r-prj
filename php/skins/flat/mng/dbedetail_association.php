<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbedetail_association.php $
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
require_once( getSkinFile("mng/gestione_header.php") );

if($reload>"") {
?><script>window.opener.reload();</script><?php
}
?><p align="center">
<h2>Select <?php echo $view_form->getListTitle(); ?>:</h2><br/>
<form name="dbeassociation" action="dbedetail_association_do.php" method="POST" class='formtable'>
 <input type="hidden" name="from_type" value="<?php echo $from_type; ?>" />
 <input type="hidden" name="to_type" value="<?php echo $to_type; ?>" />
 <input type="hidden" name="from_form" value="<?php echo $from_form; ?>" />
 <input type="hidden" name="to_form" value="<?php echo $to_form; ?>" />
 <?php
foreach($from_chiave as $k=>$v) {
	echo campoGenerico('hidden',$k,$v,'formtable')."\n";
}

foreach($decoded_list_full as $tmp) {
	$selected="";
	$stringa_chiave=getKeyString($tmp);
	if( is_a($toDBE,'DBAssociation') ) {
		$toDBE->readFKFrom($tmp);
		$stringa_chiave_to=getKeyString($toDBE);
		$selected = in_array($stringa_chiave_to,$to_dict_keys) ? " checked " : "";
	} else {
		$fromDBE = $tmp->writeFKTo($fromDBE);
		$mia_chiave_from=getKeyString($fromDBE);
		$selected = $stringa_chiave_from == $mia_chiave_from ? " checked " : "";
	}
	$view_form->setValues( $tmp->getValuesDictionary() );
	?><input type="checkbox" class='formtable' name="key_list_<?php echo $stringa_chiave; ?>" <?php echo $selected; ?>> <?php echo $view_form->getShortDescription($dbmgr); ?><br/><?php
}

?><br/>
<input type='submit' name='invia' value='OK' class='formtable' />&nbsp;<input type="button" name="chiudiFrame" value="Close" onclick="javascript:window.close();" class='formtable' /></form>
</p>
<br/><?php

require_once( getSkinFile("mng/gestione_footer.php") ); ?>
