<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: _dbe_detail_list.php $
 * @package rproject::skins
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
 * Pagina di visualizzazione lista e filtro risultati
 * @param dbetype
 * @param formtype
 * @param filterformtype
 * @param filterForm istanza di form
 * @param lista_form istanza di form
 * @param lista_title titolo della pagina di lista, default = $lista_form->getListTitle()
 * @param lista_colonne array con i nomi dei campi da visualizzare, default = $lista_form->getListColumnNames();
 * @param prefisso_pagine default = $lista_form->getPagePrefix()
 * @param listaDBE lista delle entity da visualizzare
 */
?><!-- Header Pannello: inizio. -->
<table class="pannello" align="center" valign="middle">
 <tr class="pannello" <?php if($filterForm!=null ) { ?> title='Click to open search filter'<?php } ?>>
  <th class="pannello" onclick="javascript:showHide('pannelloFiltroRicerca');" onmouseover="javascript:this.style.cursor='pointer'" onmouseout="javascript:this.style.cursor='normal'"><?php if($lista_form->getDetailIcon()>"") { echo "<img src=\"".getSkinFile($lista_form->getDetailIcon())."\">&nbsp;"; } ?><?php echo $lista_title; ?></th>
 </tr><?php
if($filterForm!=null ) {
?><tr class="pannello" id="pannelloFiltroRicerca" style="display: none;">
  <td class="pannello" style="padding: 0px; spacing: 0px;"><?php
  ?><form name="filterForm" method="POST">
   <input type="hidden" name="dbetype" value="<?php echo $dbetype; ?>"/>
   <input type="hidden" name="formtype" value="<?php echo $formtype; ?>"/>
   <input type="hidden" name="filterformtype" value="<?php echo $filterformtype; ?>"/>
   <table class="filtroRicerca" width="95%" align="center"><?php
	$nomiCampiFiltro = $filterForm->getFilterFields();
	for($f=0; $f<count($nomiCampiFiltro); $f++) {
		$nomeCampo = $nomiCampiFiltro[$f];
		$myfield = $filterForm->getField( $nomeCampo );
  ?><tr class="filtroRicerca">
     <th class="filtroRicerca"><?php echo $myfield->getTitle(); ?></th><td class="filtroRicerca"><?php
		if( is_a($myfield,'FKObjectField') ) {
			$old_viewmode = $myfield->viewmode;
			$myfield->viewmode ='distinct';
			echo $myfield->render($dbmgr);
			$myfield->viewmode=$old_viewmode;
		} else if( is_a($myfield,'FKField') ) {
			if($myfield->viewmode=='readonly') {
				$myfield->viewmode='distinct'; //select';
				echo $myfield->render($dbmgr);
				$myfield->viewmode='readonly';
			} else
				echo $myfield->render($dbmgr);
		} else {
			echo $myfield->render();
		} ?></td>
    </tr><?php
	}
 ?><tr class="filtroRicerca">
     <td class="filtroRicerca" colspan="2" style="text-align:center">
      <input type="submit" name="filtra" value="Search" class="formtable"/>
     </td>
    </tr>
   </table><?php
   ?></form><?php
  ?></td>
 </tr><?php
}
?><tr class="pannello">
  <td class="pannello">
<!-- Header Pannello: fine. -->

<table class="listaDBE" width="80%" align="center"><?php
	if( count($listaDBE)>0 ) {
?><tr><?php
		foreach($lista_colonne as $colonna) {
			$myfield=$lista_form->getField($colonna);
	?><th class="listaDBE" width="<?php echo (100/count($lista_colonne))?>%"><?php echo $myfield->getTitle(); ?></th><?php
		}
?><th class="listaDBE" width="40" colspan="3" align="center">&nbsp;</th>
 	<!--th class="listaDBE" width="5%">&nbsp;</th>
 	<th class="listaDBE" width="5%">&nbsp;</th-->
 </tr><?php
	}
$linea = 0;
foreach( $listaDBE as $mydbe ) {
	$classeRiga = 'listaDBE_pari';
	if ( ($linea % 2) == 0 ) {
		$classeRiga = 'listaDBE_pari';
	} else {
		$classeRiga = 'listaDBE_dispari';
	}
?>
 <tr><?php
	$lista_form->setValues( $mydbe->getValuesDictionary() );
	foreach($lista_colonne as $colonna) {
		$myfield=$lista_form->getField($colonna);
// 		$myfield->setValue( $mydbe->getValue($colonna) );
?><td class="<?php echo $classeRiga;?>" align="right"><?php echo is_a($myfield,'FKField') ? $myfield->render_view($dbmgr) : $myfield->render_view(); ?></td><?php
	}
?><td class="<?php echo $classeRiga?>" align="center" nowrap>
		<a href="<?php echo $prefisso_pagine; ?>_view.php?dbetype=<?php echo $dbetype; ?>&formtype=<?php echo $formtype; ?>&field_id=<?php echo  $mydbe->getValue('id'); ?>">
			<img border="0" src="<?php echo getSkinFile("mng/icone/Zoom16.gif"); ?>">
		</a>
	</td>
	<td class="<?php echo $classeRiga;?>" align="center" nowrap>
		<a href="<?php echo $prefisso_pagine; ?>_modify.php?dbetype=<?php echo $dbetype; ?>&formtype=<?php echo $formtype; ?>&field_id=<?php echo  $mydbe->getValue('id'); ?>">
			<img border="0" src="<?php echo getSkinFile("mng/icone/Edit16.gif"); ?>">
		</a>
	</td>
	<td class="<?php echo $classeRiga;?>" align="center" nowrap><?php
	if( $mydbe->getValue('id')>0 ) {
	?><form id="form_cancella_<?php echo $mydbe->getValue('id');?>" border=0 action=""><?php
			?><input type="hidden" name="field_id" value="<?php echo $mydbe->getValue('id'); ?>"><?php
			?><input type="hidden" name="dbetype" value="<?php echo $dbetype; ?>"/><?php
			?><input type="hidden" name="formtype" value="<?php echo $formtype; ?>"/><?php
			?><script>
			function cancella_<?php echo $mydbe->getValue('id'); ?>() {
				myform = document.getElementById('form_cancella_<?php echo $mydbe->getValue('id'); ?>');
				//alert( myform.action );
				conferma = confirm('Cancellare?');
				if ( conferma ) {
					myform.action = '<?php echo $prefisso_pagine; ?>_delete_do.php';
				}
			}
			</script><?php
			?><input type="image" name="cancella" border="0" src="<?php echo getSkinFile("mng/icone/Delete16.gif"); ?>" onclick="javascript:cancella_<?php echo $mydbe->getValue('id'); ?>();"><?php
		?></form><?php
	}
?></td>
 </tr>
<?php
$linea++;
} ?>
 <tr><td class="listaDBE_pari" colspan="5" align="left" title="New"><a href="<?php echo $prefisso_pagine; ?>_new.php?dbetype=<?php echo $dbetype; ?>&formtype=<?php echo $formtype; ?>"><img border="0" src="<?php echo getSkinFile("mng/icone/New16.gif"); ?>"></a></td></tr>
</table>


<!-- Footer Pannello: inizio. -->
  </td>
 </tr>
</table>
<!-- Footer Pannello: fine. -->
