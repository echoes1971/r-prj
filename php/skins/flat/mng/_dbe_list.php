<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: _dbe_list.php $
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
 * Pagina di visualizzazione lista e filtro risultati
 * @param dbetype
 * @param formtype
 * @param filterformtype
 * @param myform (empty) instance of the form tied to the dbe
 * @param filterForm istanza di form
 * @param lista_form istanza di form
 * @param lista_title titolo della pagina di lista, default = $lista_form->getListTitle()
 * @param lista_colonne array con i nomi dei campi da visualizzare, default = $lista_form->getListColumnNames();
 * @param prefisso_pagine default = $lista_form->getPagePrefix()
 * @param listaDBE lista delle entity da visualizzare
 */

$forms_cancella=array();

?><script>
function <?php echo $prefisso_pagine; ?>_selezionaTutti(checked) {
	cecchi = document.getElementsByName( "<?php echo $prefisso_pagine; ?>_obj_hash[]"); // window.parent.frames['iKnowCorpo'].
	for(i=0; i<cecchi.length; i++) {
		cecchi[i].checked = checked;
	}
}
function <?php echo $prefisso_pagine; ?>_de_select(aValue) {
	cecchi = document.getElementsByName( "<?php echo $prefisso_pagine; ?>_obj_hash[]");
	lista="";
	for(i=0; i<cecchi.length; i++) {
		if(cecchi[i].value==aValue) {
			cecchi[i].checked = ! (cecchi[i].checked);
			break;
		}
	}
// 	return false;
}
function <?php echo $prefisso_pagine; ?>_un_edit(aValue,colonna) {
	myview = document.getElementById(aValue+'_'+colonna+'_view');
	myedit = document.getElementById(aValue+'_'+colonna+'_edit');
	a = myview.style.display; b = myedit.style.display;
// 	alert('b='+b+';');
// 	if(b=='none') {
		myview.style.display = b; myedit.style.display = a;
// 	}
	// Check it anyway!!!
	cecchi = document.getElementsByName( "<?php echo $prefisso_pagine; ?>_obj_hash[]");
	lista="";
	for(i=0; i<cecchi.length; i++) {
		if(cecchi[i].value==aValue) {
			cecchi[i].checked = true;
			break;
		}
	}
}
function <?php echo $prefisso_pagine; ?>_get_checked() {
	cecchi = document.getElementsByName( "<?php echo $prefisso_pagine; ?>_obj_hash[]");
	lista="";
	for(i=0; i<cecchi.length; i++) {
		if(cecchi[i].checked) lista+=cecchi[i].value+"_";
	}
	document.getElementById("lista_obj_hashes").value=lista;
// 	alert(document.getElementById("lista_obj_hashes").value);
// 	return false;
}
function bottoniera_cancella(myform) {
	<?php echo $prefisso_pagine; ?>_get_checked();
	if(confirm('Confirm delete?')) {
		myform.action="dbe_list_delete_do.php";
		myform.submit();
	} else {
		return false;
	}
}
function bottoniera_saveall(myform) {
	<?php echo $prefisso_pagine; ?>_get_checked();
	if(confirm('Save all?')) {
		myform.action="dbe_list_modify_do.php";
		myform.submit();
	} else {
		return false;
	}
}
</script>
<!-- Header Pannello: inizio. -->
<table class="pannello" align="center" valign="middle">
 <tr class="pannello">
  <th class="pannello"><?php if($lista_form->getDetailIcon()>"") { echo "<img src=\"".getSkinFile($lista_form->getDetailIcon(),$check_plugins_first=true)."\">&nbsp;"; } ?><?php echo $lista_title; ?></th>
 </tr>
 <tr class="pannelloToolbar">
	<td class="pannelloToolbar" align="left"><div id="pannelloToolbar"><?php
	?><input type="image" name="reloadlist" title="Reload" border="0" src="<?php echo getSkinFile("mng/icone/Refresh16.gif"); ?>" onclick="javascript:if(confirm('Reload?')) { document.getElementById('filterForm').submit(); } else { return false; }">&nbsp;<?php
if($filterForm!=null ) {
	?>&nbsp;<input type="image" name="opensearchpanel" title="Open filter" border="0" src="<?php echo getSkinFile("mng/icone/filter_16x16.gif"); ?>" onclick="javascript:showHide('pannelloFiltroRicerca');return false;">&nbsp;<?php
}
	?>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo $prefisso_pagine; ?>_new.php?dbetype=<?php echo $dbetype; ?>&formtype=<?php echo $formtype; ?>"><img border="0" src="<?php echo getSkinFile("mng/icone/New16.gif"); ?>"></a><?php
if( count($listaDBE)>0 ) {
	?>&nbsp;<input type="image" name="saveall" title="Save selected" border="0" src="<?php echo getSkinFile("mng/icone/SaveAll16.gif"); ?>" onclick="javascript:bottoniera_saveall(document.getElementById('list_form'));">&nbsp;<?php
	?>&nbsp;<input type="image" name="cancella" title="Delete selected" border="0" src="<?php echo getSkinFile("mng/icone/Delete16.gif"); ?>" onclick="javascript:bottoniera_cancella(document.getElementById('list_form'));">&nbsp;<?php
}
if(count($myform->getListActions())>0) echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
foreach($myform->getListActions() as $code=>$action) {
	if($action===null) continue; // Could has been erased by an override :-P
	$action_img = array_key_exists('icon',$action) && $action['icon']>'' ? $action['icon'] : '';
	$action_label = array_key_exists('label',$action) && $action['label']>'' ? $action['label'] : '';
	$action_desc = array_key_exists('desc',$action) && $action['desc']>'' ? $action['desc'] : '';
	$action_page = array_key_exists('page',$action) && $action['page']>'' ? $action['page'] : '';
	echo "&nbsp;<input type=\"".($action_img>''?'image':'button')."\" ";
	echo "class=\"formtable\" ";
	echo "id=\"action_id_$code\" ";
	echo "name=\"action_id_$code\" ";
	if($action_img>'') echo "src=\"".getSkinFile("$action_img")."\" ";
	if($action_label>'') echo "value=\"$action_label\" ";
	if($action_desc>'') echo "title=\"$action_desc\" ";
	if($action_page>'')
		echo "onclick=\"javascript:{$prefisso_pagine}_get_checked();if(confirm('Confirm $action_label?')){ document.getElementById('list_form').action='".ROOT_FOLDER."actions/$action_page';document.getElementById('list_form').submit(); } else { return false; };\" ";
	echo "/>";
}
echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
do_hook('dbe_list_actions',array( 'dbmgr'=>&$dbmgr, 'dbetype'=>$dbetype, 'formtype'=>$formtype ) );
?></div></td>
 </tr><?php
if($filterForm!=null ) {
?><tr class="pannello" id="pannelloFiltroRicerca" <?php if(!array_key_exists('filtra',$_REQUEST) ) { ?> style="display: none;"<?php } ?>>
  <td class="pannello" style="padding: 0px; spacing: 0px;"><?php
  ?><form id="filterForm" name="filterForm" method="POST">
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
<form id="list_form" name="list_form" method="POST" action="">
 <input type="hidden" name="dbetype" value="<?php echo $dbetype; ?>" />
 <input type="hidden" name="formtype" value="<?php echo $formtype; ?>" />
 <input type="hidden" id="lista_obj_hashes" name="lista_obj_hashes" value="" />
<table class="listaDBE" width="100%" align="center"><?php
	if( count($listaDBE)>0 ) {
?><thead><tr><th class="listaDBE" width="40" align="center" title="De / Select All"><input type='checkbox' name='_seleziona_tutti_' onchange="javascript:<?php echo $prefisso_pagine; ?>_selezionaTutti(this.checked);" /></th><?php
		foreach($lista_colonne as $colonna) {
			$myfield=$lista_form->getField($colonna);
	?><th class="listaDBE" width="<?php echo (100/count($lista_colonne))?>%"><?php echo $myfield->getTitle(); ?></th><?php
		}
?><th class="listaDBE" width="40" colspan="3" align="center">&nbsp;</th>
 </tr></thead>
 <tfoot><tr><th class="listaDBE" colspan="<?php echo count($lista_colonne)+4; ?>" align="left">&nbsp;</th></tr></tfoot>
 <tbody><?php
	}
$linea = 0;
foreach( $listaDBE as $mydbe ) {
	$classeRiga = 'listaDBE_pari';
	if ( ($linea % 2) == 0 ) {
		$classeRiga = 'listaDBE_pari';
	} else {
		$classeRiga = 'listaDBE_dispari';
	}
	$myhash = $mydbe->getKeyAsHash();
?>
 <tr><td class="<?php echo $classeRiga?>" align="center" nowrap><?php
	$lista_form->setValues( $mydbe->getValuesDictionary() );
	if( !in_array('id', array_keys($mydbe->getKeys()) ) || ($mydbe->getValue('id')==intval($mydbe->getValue('id')) && $mydbe->getValue('id')>0)
		|| (intval($mydbe->getValue('id'))===0 && $mydbe->getValue('id')>'')
	) {
		?><input type="checkbox" name="<?php echo $prefisso_pagine; ?>_obj_hash[]" value="<?php echo $myhash; ?>" /><?php
		?><input type="hidden" name="<?php echo $prefisso_pagine; ?>_obj_hash_hidden[]" value="<?php echo $myhash; ?>" /><?php
		foreach( array_keys($mydbe->getKeys()) as $nomeCampo ) {
			$myfield = $lista_form->getField($nomeCampo);
			$myfield->setIsArray();
			echo $myfield->render_hidden();
		}
	}
	?></td><?php
	foreach($lista_colonne as $colonna) {
		$myfield=$lista_form->getField($colonna);
		$is_key = $mydbe->isPrimaryKey($colonna);
		// 
	?><td class="<?php echo $classeRiga;?>" align="right" onclick="javascript:<?php echo $prefisso_pagine; ?>_de_select('<?php echo $myhash; ?>');"<?php
		if(!$is_key) {
		?> ondblclick="javascript:<?php echo $prefisso_pagine; ?>_un_edit('<?php echo $myhash; ?>','<?php echo $colonna; ?>');"<?php
		};
		?> ><?php
		if(!$is_key) echo "<div id=\"{$myhash}_{$colonna}_view\" style=\"display:\">";
		echo is_a($myfield,'FKField') ?
				( is_a($myfield,'FKObjectField') ?
					$myfield->render_view($dbmgr,true) : $myfield->render_view($dbmgr)
				) : $myfield->render_view();
		if(!$is_key) {
			echo "</div>";
			echo "<div id=\"{$myhash}_{$colonna}_edit\" style=\"display:none\">";
			$myfield->setIsArray();
			echo $myfield->render($dbmgr);
			$myfield->setIsArray(false);
			echo "</div>";
		}
		?></td><?php
	}
?><td class="<?php echo $classeRiga?>" align="center" nowrap>
		<a href="<?php echo $prefisso_pagine; ?>_view.php?dbetype=<?php echo $dbetype; ?>&formtype=<?php echo $formtype; ?>&<?php echo $mydbe->getCGIKeysCondition(); ?>">
			<img border="0" src="<?php echo getSkinFile("mng/icone/Zoom16.gif"); ?>">
		</a>
	</td>
	<td class="<?php echo $classeRiga;?>" align="center" nowrap>
		<a href="<?php echo $prefisso_pagine; ?>_modify.php?dbetype=<?php echo $dbetype; ?>&formtype=<?php echo $formtype; ?>&<?php echo $mydbe->getCGIKeysCondition(); ?>">
			<img border="0" src="<?php echo getSkinFile("mng/icone/Edit16.gif"); ?>">
		</a>
	</td>
	<td class="<?php echo $classeRiga;?>" align="center" nowrap><?php
	if( !in_array('id', array_keys($mydbe->getKeys()) ) || ($mydbe->getValue('id')==intval($mydbe->getValue('id')) && $mydbe->getValue('id')>0)
		|| (intval($mydbe->getValue('id'))===0 && $mydbe->getValue('id')>'')
	) {
		?><input type="image" name="cancella" border="0" src="<?php echo getSkinFile("mng/icone/Delete16.gif"); ?>" onclick="javascript:cancella_<?php echo $myhash; ?>();return false;"><?php
		ob_start();
		?><form id="form_cancella_<?php echo $myhash;?>" border=0 action=""><?php
			foreach( array_keys($mydbe->getKeys()) as $nomeCampo ) {
				$myfield = $lista_form->getField($nomeCampo );
				$myfield->setIsArray(false);
				echo $myfield->render_hidden();
			}
			?><input type="hidden" name="dbetype" value="<?php echo $dbetype; ?>"/><?php
			?><input type="hidden" name="formtype" value="<?php echo $formtype; ?>"/><?php
			?><script>
			function cancella_<?php echo $myhash; ?>() {
				myform = document.getElementById('form_cancella_<?php echo $myhash; ?>');
				conferma = confirm('Confirm delete?');
				if ( conferma ) {
					myform.action = '<?php echo $prefisso_pagine; ?>_delete_do.php';
// 					alert(myform.action);
					myform.submit();
				}
				return false;
			}
			</script><?php
		?></form><?php
		$forms_cancella []= ob_get_contents();
		ob_end_clean();
	}
?></td>
 </tr><?php
	$linea++;
}
?></tbody><!-- tfoot><tr><th class="listaDBE" colspan="<?php echo count($lista_colonne)+3; ?>" align="left">&nbsp;</th></tfoot></tr -->
</table>
</form><?php /* list_form: fine. */ ?>
<!-- Footer Pannello: inizio. -->
  </td>
 </tr>
</table>
<!-- Footer Pannello: fine. -->
<?php echo implode($forms_cancella,"\n"); ?>
