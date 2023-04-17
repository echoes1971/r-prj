<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbe_modify.php $
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

$maxTabs = max( count($myform->getGroupNames()), $myform->getDetailFormsCount() ); if($maxTabs>1) $maxTabs++; // + 1 tab ALL

$formActionsHtml  = "";
$formActionsHtml .= "<input type=\"image\" name=\"cancella\" border=\"0\" src=\"".getSkinFile("icons/editdelete.png")."\" onclick=\"javascript:cancella_".$mydbe->getKeyAsHash()."();return false;\" title=\"Delete\">";
$formActionsHtml .= "&nbsp;&nbsp;&nbsp;";
foreach($myform->getActions() as $code=>$action) {
	if($action===null) continue; // Could has been erased by an override :-P
	$action_img = array_key_exists('icon',$action) && $action['icon']>'' ? $action['icon'] : '';
	$action_label = array_key_exists('label',$action) && $action['label']>'' ? $action['label'] : '';
	$action_desc = array_key_exists('desc',$action) && $action['desc']>'' ? $action['desc'] : '';
	$action_page = array_key_exists('page',$action) && $action['page']>'' ? $action['page'] : '';
	$formActionsHtml .= "<input type=\"".($action_img>''?'image':'button')."\" ";
	$formActionsHtml .= "class=\"formtable\" ";
	$formActionsHtml .= "id=\"action_id_$code\" ";
	$formActionsHtml .= "name=\"action_id_$code\" ";
	if($action_img>'') $formActionsHtml .= "src=\"".getSkinFile("$action_img")."\" ";
	if($action_label>'') $formActionsHtml .= "value=\"$action_label\" ";
	if($action_desc>'') $formActionsHtml .= "title=\"$action_desc\" ";
	if($action_page>'')
		$formActionsHtml .= "onclick=\"javascript:if(confirm('Confirm $action_label?')){ this.form.action='".ROOT_FOLDER."actions/$action_page';this.form.submit(); } else { return false; };\" ";
	$formActionsHtml .= "/>&nbsp;";
}
ob_start();
do_hook('dbe_modify_actions',array( 'dbmgr'=>&$dbmgr, 'obj_id'=>$mydbe->getValue('id'), 'dbetype'=>$dbetype, 'formtype'=>$formtype ) );
$formActionsHtml .= ob_get_contents();
ob_end_clean();
$formActionsHtml .= "<input type=\"image\" class=\"formtable\" id=\"action_invia\" name=\"action_invia\" src=\"".getSkinFile("icons/filesave.png")."\" value=\"Save\" title=\"Save\" onclick=\"javascript:this.form.submit();\" />";
$formActionsHtml .= "&nbsp;";
$formActionsHtml .= "<a href=\"dbe_view.php?dbetype=$dbetype&formtype=$formtype&".$mydbe->getCGIKeysCondition()."\"><img border=\"0\" src=\"".getSkinFile("mng/icone/Zoom16.gif")."\"></a>";
$formActionsHtml .= "<input type=\"image\" class=\"formtable\" id=\"action_close\" name=\"action_close\" src=\"".getSkinFile("icons/fileclose.png")."\" value=\"Close\" title=\"Close\" onclick=\"javascript:window.location.href='".$myform->getPagePrefix()."_list.php?dbetype=$dbetype&formtype=$formtype';return false;\" />";


?><form action="<?php echo $myform->getAction(); ?>" method="<?php echo $myform->getMethod(); ?>" <?php if($myform->getEnctype()>"") echo "enctype=\"".$myform->getEnctype()."\""; ?>>
	<input type="hidden" name="dbetype" value="<?php echo $dbetype; ?>" />
	<input type="hidden" name="formtype" value="<?php echo $formtype; ?>" /><?php
	foreach( array_keys($mydbe->getKeys()) as $nomeCampo ) {
		if(in_array($nomeCampo,$campiVisibili)) continue;
		$myfield = $myform->getField($nomeCampo );
		echo $myfield->render_hidden();
	}
	echo "<table class=\"formtable\" align=\"center\">";
	// Title and Actions Bar: top
	echo "<tr>";
	echo "<th class=\"formtable\" colspan=\"$maxTabs\">"
		.($myform->getDetailIcon()>""?"<img src=\"".getSkinFile($myform->getDetailIcon())."\">&nbsp;":'')
		.$myform->getName()." ".$myform->getDetailTitle();
	echo "<div style=\"float:right;\">$formActionsHtml</div>";
	echo "</th>";
	echo "</tr>";
	
	// 2012.02.22: start.
	$tabNames=array();
	echo "<tr>";
	$countGroups = count($myform->getGroupNames());
	foreach( $myform->getGroupNames() as $nome_gruppo ) {
		$_mygroup=$myform->getGroup( $nome_gruppo );
		$decodedGroupName = $myform->decodeGroupName($nome_gruppo);
		$tabNames[]=$nome_gruppo;
		$__myTHClass = $decodedGroupName>"" ? 'formtable' : 'formtableSelected';
		if($decodedGroupName>"") {
		} else {
			$decodedGroupName = $myform->getDetailTitle();
		}
		echo "<th id=\"tab_{$nome_gruppo}_button\" class=\"$__myTHClass\" colspan=\"".intval($maxTabs/$countGroups)."\" ";
// 		echo "width=\"".intval(100/($countGroups+1))."%\"";
		echo ">";
		echo "<div onclick=\"javascript:showTab('tab_{$nome_gruppo}');\">$decodedGroupName</div></th>";
	}
	if($countGroups>1) echo "<th id=\"tab_ALL_button\" class=\"formtable\" colspan=\"".($maxTabs/$countGroups)."\"><div onclick=\"javascript:showTab('tab_ALL');\">Show All</div></th>";
	echo "</tr>";
	
	foreach( $myform->getGroupNames() as $nome_gruppo ) {
		$_mygroup=$myform->getGroup( $nome_gruppo );
		$decodedGroupName = $myform->decodeGroupName($nome_gruppo);
		echo "<tr id=\"tab_{$nome_gruppo}\" ".($decodedGroupName>""?"style=\"display:none;\"":"")."><td colspan=\"$maxTabs\"><table class=\"formtableGroup\" align=\"center\">"; // 2012.02.22
		foreach( $_mygroup as $nomeCampo ) {
			$myfield = $myform->getField( $nomeCampo );
			$_isKey = in_array($nomeCampo,array_keys($mydbe->getKeys()));
			if( in_array($nomeCampo ,$campiVisibili) ) {
				echo "<tr class=\"formtable\">";
				echo "<td class=\"formtable\" width=\"30%\" align=\"right\" valign=\"top\">".($_isKey?'<b>':'').$myfield->getTitle().($_isKey?'</b>':'')."</td>";
				echo "<td class=\"formtable\" width=\"70%\" align=\"left\">";
				if(in_array($nomeCampo, $campiReadonly)) {
					if( is_a($myfield,'FKField') || get_class($myfield)=='FChildSort' ) echo $myfield->render_readonly($dbmgr);
					else echo $myfield->render_readonly();
				} else {
					if( is_a($myfield,'FKField') || get_class($myfield)=='FChildSort' ) echo $myfield->render($dbmgr);
					else echo $myfield->render();
				}
				echo "</td>";
				echo "</tr>";
			} else {
				echo $myfield->render_hidden();
			}
		}
		echo "</td></tr></table>";
	}
	echo "<script>";
	echo "function showTab(nomeTab) {";
	echo "try {";
	echo " var aDiv;";
	echo " var aDivButton;";
	foreach($tabNames as $_tabName) {
		echo "aDiv = document.getElementById('tab_$_tabName');";
		echo "aDivButton = document.getElementById('tab_{$_tabName}_button');";
		echo "if(aDiv) {";
		echo " aDiv.style.display = 'tab_$_tabName'==nomeTab || 'tab_ALL'==nomeTab ? '' : 'none';";
		echo " for(i=0;i<aDivButton.attributes.length; i++) {";
		echo "  if(aDivButton.attributes[i].name!='class') { continue; };";
		echo "  aDivButton.attributes[i].value = 'tab_$_tabName'==nomeTab || 'tab_ALL'==nomeTab ? 'formtableSelected' : 'formtable';";
		echo "  break;";
		echo " };";
		echo "};";
	}
	echo "} catch(e) { alert(e); }";
	echo "}";
	echo "</script>";
	
	if($myform->getDetailFormsCount()>0) {
		echo "<tr>";
		$tabNames=array();
		for($i=0; $i<$myform->getDetailFormsCount(); $i++) {
			$tabNames[]="child$i";
			$childForm = $myform->getDetail($i);
			$dest_form = $childForm;
			if( is_a($childForm,'FAssociation') ) $dest_form = get_class($myform)==get_class( $childForm->getFromForm() ) ? $childForm->getToForm() : $childForm->getFromForm();
			echo "<th id=\"tab_child{$i}_button\" ";
			echo "class=\"formtable".($i==0?'Selected':'')."\" ";
// 			echo "width=\"".intval(100/($myform->getDetailFormsCount()+1))."%\" ";
			echo "onmouseover=\"javascript:this.style.cursor='pointer'\" ";
			echo "onmouseout=\"javascript:this.style.cursor='normal'\">";
			echo "<div onclick=\"javascript:showTabChild('tab_child{$i}');\">".($dest_form->getDetailIcon()>''?"<img src=\"".getSkinFile($dest_form->getDetailIcon())."\">&nbsp;":'')." ".$dest_form->getListTitle()."</div>";
			echo "</th>";
		}
		echo "<th id=\"tab_childALL_button\" class=\"formtable\" onmouseover=\"javascript:this.style.cursor='pointer'\" onmouseout=\"javascript:this.style.cursor='normal'\"><div onclick=\"javascript:showTabChild('tab_childALL');\">Show All</div></th>";
		echo "</tr>";
		
		for($i=0; $i<$myform->getDetailFormsCount(); $i++) {
			$childForm = $myform->getDetail($i);
			$dest_form = $childForm;
			if( is_a($childForm,'FAssociation') ) $dest_form = get_class($myform)==get_class( $childForm->getFromForm() ) ? $childForm->getToForm() : $childForm->getFromForm();
			echo "<tr id=\"tab_child{$i}\" style=\"display:".($i==0?'':'none').";\">";
			echo "<td colspan=\"$maxTabs\">";
			echo "<table class=\"formtableGroup\" align=\"center\">";
			echo "<tr class=\"formtable\">";
			echo "<td class=\"formtable\" width=\"30%\" align=\"right\" valign=\"top\"><b>".$dest_form->getListTitle()."</b></td>";
			echo "<td class=\"formtable\" width=\"70%\" align=\"left\">";
				$descrizioni = array();
				foreach( $childs[ get_class($childForm) ] as $child) {
					if($child==null) continue;
					$dest_form->setValues( $child->getValuesDictionary() );
					$link="dbe_modify.php?dbetype=".$child->getTypeName()."&formtype=".get_class($dest_form)."&".$child->getCGIKeysCondition();
					$descrizioni[] = "<a href=\"$link\" class='formtable'>-".$dest_form->getShortDescription($dbmgr)."</a>";
				}
				echo implode("<br/>",$descrizioni);
				if(count($descrizioni)>0) echo "<br/>";
				$__mydbe = $childForm->getDBE();
				echo "&nbsp;<img  title=\"Link/Unlink ".$dest_form->getListTitle()."\" "
					."alt=\"Link/Unlink "
					.$dest_form->getListTitle()."\" src=\"".getSkinFile("mng/icone/link_selector.gif")."\" "
					."onclick=\"javascript:window.open('dbedetail_association.php?from_type=".$mydbe->getTypeName()."&to_type=".$__mydbe->getTypeName()."&from_form=".get_class($myform)."&to_form=".get_class($childForm)
						."&".$mydbe->getCGIKeysCondition()."','Add/Remove ".$dest_form->getListTitle()."','toolbar=no,menubar=no,resizable=yes,scrollbars=yes,width=200,height=300,top=10,left=10');\" "
					."border=\"0\" "
					."onmouseover=\"javascript:this.style.cursor='pointer'\" onmouseout=\"javascript:this.style.cursor='normal'\""
					."/>";
				if (!is_a($childForm,'FAssociation') ) {
					$childDBE = $childForm->getDBE();
					$childDBE->readFKFrom($mydbe);
					echo "&nbsp;&nbsp;&nbsp;";
					echo "<a href=\"".$childForm->getPagePrefix()."_new.php?dbetype=".$childDBE->getTypeName()."&formtype=".get_class($childForm)."&".$childDBE->getFKCGIConditionFromMaster($mydbe, true)."\">";
					echo "<img  title=\"Add ".$dest_form->getListTitle()."\" alt=\"Add ".$dest_form->getListTitle()."\" src=\"".getSkinFile("mng/icone/New16.gif")."\" border=\"0\"/>";
					echo "</a>";
				}
			echo "</td>";
			echo "</tr>";
			echo "</table>";
			echo "<script>";
			echo "function showTabChild(nomeTab) {";
			echo "try {";
			echo " var aDiv;";
			echo " var aDivButton;";
			foreach($tabNames as $_tabName) {
				echo "aDiv = document.getElementById('tab_$_tabName');";
				echo "aDivButton = document.getElementById('tab_{$_tabName}_button');";
				echo "if(aDiv) {";
				echo " aDiv.style.display = 'tab_$_tabName'==nomeTab || 'tab_childALL'==nomeTab ? '' : 'none';";
				echo " for(i=0;i<aDivButton.attributes.length; i++) {";
				echo "  if(aDivButton.attributes[i].name!='class') { continue; };";
				echo "  aDivButton.attributes[i].value = 'tab_$_tabName'==nomeTab || 'tab_childALL'==nomeTab ? 'formtableSelected' : 'formtable';";
				echo "  break;";
				echo " };";
				echo "};";
			}
			echo "} catch(e) { alert(e); }";
			echo "}";
			echo "</script>";
			echo "</td></tr>";
		}
	}
	// Title and Actions Bar: bottom
	echo "<tr>";
	echo "<th class=\"formtable\" colspan=\"$maxTabs\">"
		.($myform->getDetailIcon()>""?"<img src=\"".getSkinFile($myform->getDetailIcon())."\">&nbsp;":'')
		.$myform->getName()." ".$myform->getDetailTitle();
	echo "<div style=\"float:right;\">$formActionsHtml</div>";
	echo "</th>";
	echo "</tr>";
	echo "</table>";
?></form><?php

// **** Delete Form
?><form id="form_cancella_<?php echo $mydbe->getKeyAsHash();?>" border=0 action=""><?php
	foreach( array_keys($mydbe->getKeys()) as $nomeCampo ) {
		$myfield = $myform->getField($nomeCampo );
		echo $myfield->render_hidden();
	}
	echo "<input type=\"hidden\" name=\"dbetype\" value=\"$dbetype\"/>";
	echo "<input type=\"hidden\" name=\"formtype\" value=\"$formtype\"/>";
	$my_father_id_field = $myform->getField( 'father_id' );
	if($my_father_id_field!==null) {
		echo "<input type=\"hidden\" name=\"redir_to\" value=\"father_id\"/>";
		echo $my_father_id_field->render_hidden();
	}
	?><script>
function cancella_<?php echo $mydbe->getKeyAsHash(); ?>() {
	myform = document.getElementById('form_cancella_<?php echo $mydbe->getKeyAsHash(); ?>');
	conferma = confirm('Confirm delete?');
	if ( conferma ) {
		myform.action = 'dbe_delete_do.php';
		myform.submit();
	}
}
</script><?php
?></form><?php

require_once( getSkinFile("mng/gestione_footer.php") ); ?>