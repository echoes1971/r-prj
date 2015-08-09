<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbe_view.php $
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

$____myutente = array_key_exists('utente',$_SESSION) ? $_SESSION['utente'] : null;

$maxTabs = max( count($myform->getGroupNames()), $myform->getDetailFormsCount() ); if($maxTabs>1) $maxTabs++; // + 1 tab ALL

$formActionsHtml  = "";
ob_start();
do_hook('dbe_view_actions',array( 'dbmgr'=>&$dbmgr, 'obj_id'=>$mydbe->getValue('id'), 'dbetype'=>$dbetype, 'formtype'=>$formtype ) );
$formActionsHtml .= ob_get_contents();
ob_end_clean();
$formActionsHtml .= "<a href=\"dbe_modify.php?dbetype=$dbetype&formtype=$formtype&".$mydbe->getCGIKeysCondition()."\"><img border=\"0\" src=\"".getSkinFile("mng/icone/Edit16.gif")."\"></a>";
$formActionsHtml .= "<input type=\"image\" class=\"formtable\" id=\"action_close\" name=\"action_close\" src=\"".getSkinFile("icons/fileclose.png")."\" value=\"Close\" title=\"Close\" onclick=\"javascript:window.location.href='".$myform->getPagePrefix()."_list.php?dbetype=$dbetype&formtype=$formtype';return false;\" />";

?><table class="formtable" align="center"><?php
	echo "<tr>";
	echo "<th class=\"formtable\" colspan=\"$maxTabs\">";
	if($myform->getDetailIcon()>"") { echo "<img src=\"".getSkinFile($myform->getDetailIcon())."\">&nbsp;"; }
	echo $myform->getName()." ".$myform->getDetailTitle();
	if($____myutente!==null) echo "<div style=\"float:right;\">$formActionsHtml</div>";
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
		if($decodedGroupName>"") {
		} else {
			$decodedGroupName = $myform->getDetailTitle();
		}
		echo "<th id=\"tab_{$nome_gruppo}_button\" class=\"formtableSelected\" colspan=\"".intval($maxTabs/$countGroups)."\" ";
// 		echo "width=\"".intval(100/($countGroups+1))."%\"";
		echo ">";
		echo "<div onclick=\"javascript:showTab('tab_{$nome_gruppo}');\">$decodedGroupName</div></th>";
	}
	if($countGroups>1) echo "<th id=\"tab_ALL_button\" class=\"formtable\" colspan=\"".($maxTabs/$countGroups)."\"><div onclick=\"javascript:showTab('tab_ALL');\">Show All</div></th>";
	echo "</tr>";
	
	foreach( $myform->getGroupNames() as $nome_gruppo ) {
		$_mygroup=$myform->getGroup( $nome_gruppo );
		if($____myutente===null && $nome_gruppo=='_permission') continue;
		$decodedGroupName = $myform->decodeGroupName($nome_gruppo);
		echo "<tr id=\"tab_{$nome_gruppo}\">";
		echo "<td colspan=\"$maxTabs\">";
		echo "<table class=\"formtableGroup\" align=\"center\">";
		foreach( $_mygroup as $nomeCampo ) {
			if($____myutente===null && in_array($nomeCampo,array('creator','creation_date','last_modify')) ) continue;
			$myfield = $myform->getField( $nomeCampo );
			$_isKey = in_array($nomeCampo,array_keys($mydbe->getKeys()));
			if( in_array($nomeCampo ,$campiVisibili) ) {
				echo "<tr class=\"formtable\">";
				echo "<td class=\"formtable\" width=\"30%\" align=\"right\" valign=\"top\">".($_isKey?'<b>':'').$myfield->getTitle().($_isKey?'</b>':'')."</td>";
				echo "<td class=\"formtable\" width=\"70%\" align=\"left\">";
				if ( is_a($myfield,'FKField') && $myfield->destform!=null ) {
					$mydestform = $formulator->getInstance($myfield->destform);
					$_mydestdbe=$mydestform->getDBE();
					$_mydestdbe = $mydbe->writeFKTo($_mydestdbe);
					$link="dbe_view.php?dbetype=".$_mydestdbe->getTypeName()."&formtype=".get_class($mydestform)."&".$_mydestdbe->getCGIKeysCondition();
					echo "<a href=\"$link\" class='formtable'>".$myfield->render_readonly($dbmgr)."</a>";
				} else if (is_a($myfield,'FKObjectField')) {
					echo $myfield->render_view($dbmgr, $showlink=true);
				} else {
					echo $myfield->render_readonly($dbmgr);
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
			echo "<th id=\"tab_child{$i}_button\" class=\"formtable".($i==0?'Selected':'')."\" onmouseover=\"javascript:this.style.cursor='pointer'\" onmouseout=\"javascript:this.style.cursor='normal'\"><div onclick=\"javascript:showTabChild('tab_child{$i}');\">";
			if ($dest_form->getDetailIcon()>"") { echo "<img src=\"".getSkinFile($dest_form->getDetailIcon())."\">&nbsp;"; };
			echo $dest_form->getListTitle();
			echo "</div>";
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
			// Altrimenti non funziona il javascript
			if( count( $childs[ get_class($childForm) ] )>0 ) {
				echo "<table class=\"formtableGroup\" align=\"center\">";
				echo "<tr class=\"formtable\">";
				echo "<td class=\"formtable\" width=\"30%\" align=\"right\" valign=\"top\"><b>".$dest_form->getListTitle()."</b></td>";
				echo "<td class=\"formtable\" width=\"70%\" align=\"left\" title=\"Add/remove ".$dest_form->getListTitle()."\">";
				$descrizioni = array();
				foreach( $childs[ get_class($childForm) ] as $child) {
					$dest_form->setValues( $child->getValuesDictionary() );
					$link="dbe_view.php?dbetype=".$child->getTypeName()."&formtype=".get_class($dest_form)."&".$child->getCGIKeysCondition();
					$descrizioni[] = "<a href=\"$link\">".$dest_form->getShortDescription($dbmgr)."</a>";
				}
				echo implode("<br/>",$descrizioni);
				echo "</td>";
				echo "</tr>";
				echo "</table>";
			}
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
			echo "</td></tr>"; // 2012.02.22
		}
	}
	echo "</tr>";
	echo "<tr>";
	echo "<th class=\"formtable\" colspan=\"$maxTabs\">";
	if($myform->getDetailIcon()>"") { echo "<img src=\"".getSkinFile($myform->getDetailIcon())."\">&nbsp;"; }
	echo $myform->getName()." ".$myform->getDetailTitle();
	if($____myutente!==null) echo "<div style=\"float:right;\">$formActionsHtml</div>";
	echo "</th>";
	echo "</tr>";
?></table><?php

require_once( getSkinFile("mng/gestione_footer.php") ); ?>