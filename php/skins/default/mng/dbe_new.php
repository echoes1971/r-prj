<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dbe_new.php $
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

$maxTabs = max( count($myform->getGroupNames()), 0 ); if($maxTabs>1) $maxTabs++; // + 1 tab ALL

$formActionsHtml  = "";
ob_start();
do_hook('dbe_new_actions',array( 'dbmgr'=>&$dbmgr, 'fk_obj_id'=>$mydbe->getValue('fk_obj_id'), 'dbetype'=>$dbetype, 'formtype'=>$formtype ) );
$formActionsHtml .= ob_get_contents();
ob_end_clean();
$formActionsHtml .= "<input type=\"image\" class=\"formtable\" id=\"action_invia\" name=\"action_invia\" src=\"".getSkinFile("icons/filesave.png")."\" value=\"Save\" title=\"Save\" onclick=\"javascript:this.form.submit();\" />";
$formActionsHtml .= "<input type=\"image\" class=\"formtable\" id=\"action_close\" name=\"action_close\" src=\"".getSkinFile("icons/fileclose.png")."\" value=\"Close\" title=\"Close\" onclick=\"javascript:window.location.href='".$myform->getPagePrefix()."_list.php?dbetype=$dbetype&formtype=$formtype';return false;\" />";

?><form action="<?php echo $myform->getAction(); ?>" method="<?php echo $myform->getMethod(); ?>" <?php if($myform->getEnctype()>"") echo "enctype=\"".$myform->getEnctype()."\""; ?>>
	<input type="hidden" name="dbetype" value="<?php echo $dbetype; ?>"/>
	<input type="hidden" name="formtype" value="<?php echo $formtype; ?>"/>
	<table class="formtable" align="center"><?php
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
			if( in_array($nomeCampo ,$campiVisibili) ) {
				if ($nomeCampo=='data') { $myfield->setValue( $oggi ); }
			?><tr class="formtable">
					<td class="formtable" width="30%" align="right" valign="top"><?php echo $myfield->getTitle(); ?></td>
					<td class="formtable" width="70%" align="left"><?php
					if( !in_array($nomeCampo, $campiReadonly) ) {
						echo is_a($myfield,'FKField') || get_class($myfield)=='FChildSort' ? $myfield->render($dbmgr) : $myfield->render();
					} else {
						echo "--";
					}
					?></td>
				</tr><?php
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
	
?><tr>
	<th class="formtable" colspan="<?php echo $maxTabs; ?>"><?php if($myform->getDetailIcon()>"") { echo "<img src=\"".getSkinFile($myform->getDetailIcon())."\">&nbsp;"; } ?><?php echo $myform->getName(); ?> <?php echo $myform->getDetailTitle();
	?><div style="float:right;"><?php echo $formActionsHtml; ?></div></th>
</tr>
</table>
</form><?php

require_once( getSkinFile("mng/gestione_footer.php") ); ?>