<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: main.php $
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

require_once(getSkinFile("header.php"));


?><div id="middle_container"><?php

?><div id="left"><?php
// Middle Left
 //<table class="menu" valign="middle" align="center" width="100%">
 ?><table class="menu" align="center" width="100%"><?php
// Root Object (Home)
  ?><tr><th class="menu"><?php
	echo "<a href=\"main.php?obj_id=".$root_obj->getValue('id')."\">".$root_obj->getValue('name')."</a>";
  ?></th></tr><?php
 ?></table><div id="left_content"><?php
 do_hook('divleft_before');
//<table class="menu" valign="middle" align="center" width="100%">
?><table class="menu" align="center" width="100%"><?php

// Items da visualizzare come contenuto
$content_items = array();

// Alberello
function render_level(&$parent_list,$level=0,$indent="") {
	global $menu_tree;
	global $formulator;
	global $content_items;
	global $current_obj_id;
	//echo "current_obj_id: $current_obj_id<br/>\n";
	$parent_item=$parent_list[$level];
	//echo "parent_item: ".$parent_item->getValue('id')."::".$parent_item->getValue('name')."::".count($menu_tree[$parent_item->getValue('id')])."<br/>\n";
	foreach($menu_tree[$parent_item->getValue('id')] as $menu_item) {
		$dbetype = $menu_item->getValue('classname')>'' ? $menu_item->getValue('classname') : $menu_item->getTypeName();
		$formtype = $formulator->getFormNameByDBEName($dbetype);
		$myform = $formulator->getInstance($formtype,'Modify','dbe_modify_do.php'); // 2011.04.04 eval("\$myform = new $formtype('Modify','dbe_modify_do.php');");
		$myform->setValues($menu_item->getValuesDictionary());
		if( ($menu_item->getValue('father_id')==$current_obj_id)
			|| ($menu_item->getValue('fk_obj_id')==$current_obj_id) ){
			if(!is_a($menu_item,'DBEFolder')
				&& !is_a($menu_item,'DBELink')
				&& !is_a($menu_item,'DBENote')
				&& !is_a($menu_item,'DBEPage')
			) {
				$content_items[]=$myform;
			} else if(is_a($menu_item,'DBELink') && !$myform->isInternal()) {
				$content_items[]=$myform;
				continue;
			} else if(is_a($menu_item,'DBENote')) {
				$content_items[]=$myform;
				continue;
			} else if(is_a($menu_item,'DBEPage')) {
				$content_items[]=$myform;
				continue;
/*			} else if(is_a($menu_item,"DBEFolder")) {
// 				$content_items[]=$myform;
// 				continue;
			} else {
				$content_items[]=$myform;
				continue;*/
			}
		} else {
/*			if(!is_a($menu_item,"DBEFolder") && !is_a($menu_item,"DBEPeople")) {
				continue;
			}*/
			if(is_a($menu_item,'DBELink') && !$myform->isInternal()) {
// 				$content_items[]=$myform;
// 				continue;
			}
		}
		if($menu_item->getValue('id')>'') {
			echo "<tr><td class=\"menu\">$indent";
			if($myform->getDetailIcon()>"") {
				echo "<img src=\"".getSkinFile($myform->getDetailIcon())."\" alt=\"\" />&nbsp;";
			}
			if( is_a($myform,'FLink') ) {
				echo $myform->render_view($dbmgr);
			} else
				echo "<a href=\"main.php?obj_id=".$menu_item->getValue('id')."\">".$menu_item->getValue('name')."</a>";
	// 		echo $menu_item_view>'' ? $menu_item_view : "<a href=\"main.php?obj_id=".$menu_item->getValue('id')."\">".$menu_item->getValue('name')."</a>";
			echo "</td></tr>";
		}
		if( ($level+1)<count($parent_list)
			&& $menu_item->getValue('id')==$parent_list[$level+1]->getValue('id')
		) {
			render_level($parent_list,$level+1,"$indent&nbsp;&nbsp;");
		}
	}
}
render_level($parent_list,0,"");
if(!is_a($current_obj,'DBEFolder')) {
	//$content_items[]=$current_form;
}

?></table><?php
do_hook('divleft_after');
?></div></div><?php

// Middle
?><div id="<?php echo $dbmgr->hasGroup($GROUP_WEBMASTER) ? "middle" : "middle_noright"; ?>"><?php
 ?><ul class="breadcrumb"><?php
$breadcrumbs=array();
foreach($parent_list as $bread_crumb) {
	$breadcrumbs[] = "<li class=\"breadcrumb\"><a  class=\"breadcrumb\" href=\"main.php?obj_id=".$bread_crumb->getValue('id')."\">".$bread_crumb->getValue('name')."</a></li>";
}
echo join("<li class=\"breadcrumb\"> &gt; </li>",$breadcrumbs);
 ?></ul><?php
  ?><div id="breadcrumb_after"><?php
  do_hook('breadcrumb_after');
  ?></div><?php

 ?><div class="middle_content"><?php

do_hook('divmiddle_before');

// 2012.07.23: start.
// Search results
$searchresult_items_count=count($searchresult_items);
if($search_object>'') {
	echo "<h2>Search</h2>";
	echo "<form class=\"search\" name=\"\" action=\"main.php\">";
	echo "<label class=\"search\">Name or description</label><input class=\"search\" name=\"search_object\" type=\"text\" value=\"$search_object\" />";
	echo "</form>";
	echo "<br/>";
	echo "<h3>Results</h3>";
	echo "<br/>";
// 	if($searchresult_items_count>0) {
	echo "<div class=\"content_list\">";
	foreach($searchresult_items as $content_item) {
		$_desc = $content_item->getValue('description');
		echo "<div class=\"content_item\">";
		echo "<h3 class=\"content_item\">";
		echo "<img src=\"".getSkinFile($content_item->getDetailIcon())."\" alt=\"\" />&nbsp;";
		if( is_a($content_item,'FFile') && $content_item->isImage() && $content_item->getValue('alt_link')>'' ) {
			echo $content_item->getValue('name');
		} else {
			echo "<a href=\"main.php?obj_id=".$content_item->getValue('id')."\">".$content_item->getValue('name')."</a>";
		}
		echo "</h3>";
		if( is_a($content_item,'FFile') && $content_item->isImage() ) {
			$__alt_link=$content_item->getValue('alt_link');
			
			echo $content_item->getField('filename')->render_thumbnail($__alt_link);
			echo '<br/><br/>';
		} elseif( is_a($content_item,'FPage') ) {
			if($current_obj_id==$content_item->getValue('id')) {
				if($_desc>'') {
					echo "<p class=\"content_item\">$_desc</p>";
					echo "<br/>";
					$_desc='';
				}
				echo $content_item->getField('html')->render_view('main.php','download.php');
			}
		} elseif( is_a($content_item,'FLink') ) {
			echo $content_item->render_view();
		} elseif( is_a($content_item,'FNote') ) {
			$_desc='';
		} else {
			echo "<b>".$content_item->getField('name')->render_view()."</b>";
		}
		if($_desc>'') {
			echo "<p class=\"content_item\">".str_replace("\n","<br/>",$_desc)."</p>";
		}
		if($searchresult_items_count>1) echo "<hr/>";
		echo "</div>";
	}
	echo "</div>";
} else {
// 2012.07.23: end.
	if($index_page_form!==null) {
		echo $index_page_form->getField('html')->render_view();
	} elseif( is_a($current_form,'FPage') && !is_a($current_form,'FNews') ) {
		echo $current_form->getField('html')->render_view();
	} elseif( is_a($current_form,'FFile') && $current_form->isImage() ) {
	// 	echo $current_form->getField('name')->render_view();
	// 	echo $current_form->getField('description')->render_view();
		echo $current_form->render_view($dbmgr);
		echo $current_form->getField('filename')->render_image();
	} else {
		echo $current_form->render_view($dbmgr);
	}

	$content_items_count=count($content_items);
	if($content_items_count>0) echo "<div class=\"content_list\">";
	foreach($content_items as $content_item) {
		$_desc = $content_item->getValue('description');
		echo "<div class=\"content_item\">";
		echo "<h3 class=\"content_item\">";
		echo "<img src=\"".getSkinFile($content_item->getDetailIcon())."\" alt=\"\" />&nbsp;";
		if( is_a($content_item,'FFile') && $content_item->isImage() && $content_item->getValue('alt_link')>'' ) {
			echo $content_item->getValue('name');
		} else {
			echo "<a href=\"main.php?obj_id=".$content_item->getValue('id')."\">".$content_item->getValue('name')."</a>";
		}
		echo "</h3>";
		if( is_a($content_item,'FFile') && $content_item->isImage() ) {
			$__alt_link=$content_item->getValue('alt_link');
			
			echo $content_item->getField('filename')->render_thumbnail($__alt_link);
			echo '<br/><br/>';
		} elseif( is_a($content_item,'FPage') ) {
			if($current_obj_id==$content_item->getValue('id')) {
				if($_desc>'') {
					echo "<p class=\"content_item\">$_desc</p>";
					echo "<br/>";
					$_desc='';
				}
				echo $content_item->getField('html')->render_view('main.php','download.php');
			}
		} elseif( is_a($content_item,'FLink') ) {
			echo $content_item->render_view($dbmgr);
		} elseif( is_a($content_item,'FNote') ) {
			$_desc='';
		} else {
			echo "<b>".$content_item->getField('name')->render_view()."</b>";
	// 		echo $content_item->render_view($dbmgr);
	// 		echo $content_item->render_view();
		}
		if($_desc>'') {
			echo "<p class=\"content_item\">".str_replace("\n","<br/>",$_desc)."</p>";
		}
		if($content_items_count>1) echo "<hr/>";
		echo "</div>";
	}
	if($content_items_count>0) echo "</div>";
	// echo join("<br/><hr/>",$content_items);
// 2012.07.23: start.
}
// 2012.07.23: end.

do_hook('divmiddle_after');
 ?></div><?php
?></div><?php
 if( $dbmgr->hasGroup($GROUP_WEBMASTER) && ($current_obj->canWrite('U') || $current_obj->canWrite('G') ) ) {
?><div id="right"><?php
	// Middle Right
	do_hook('divright_before');
	
	// Actions
	echo "<ul class=\"obj_actions\">Actions";
	echo "<li class=\"obj_actions\"><a href=\"javascript:main_actions_mostra_url('Edit','mng/dbe_modify.php?dbetype=$dbetype&formtype=$formtype&field_id=".$current_obj->getValue('id')."');\"><img border=\"0\" src=\"".getSkinFile("mng/icone/Edit16.gif")."\" alt=\"\" />Edit</a></li>";
	echo "<li class=\"obj_actions\">&nbsp;</li>";
	for($i=0; $i<$current_form->getDetailFormsCount(); $i++) {
		$childForm = $current_form->getDetail($i);
		$dest_form = $childForm;
		if (!is_a($childForm,'FAssociation') ) {
			$childDBE = $childForm->getDBE();
			$childDBE->readFKFrom($current_obj);
			$newUrl = "mng/". $childForm->getPagePrefix()."_new.php?dbetype=".$childDBE->getTypeName()."&formtype=".get_class($childForm)."&".$childDBE->getFKCGIConditionFromMaster($current_obj, true);
			$newTitle = "Add ".$dest_form->getDetailTitle();
			?><li class="obj_actions"><a href="javascript:main_actions_mostra_url('<?php echo $newTitle; ?>','<?php echo $newUrl; ?>');"><img title="<?php echo $newTitle; ?>" alt="<?php echo $newTitle; ?>" src="<?php echo getSkinFile("mng/icone/New16.gif"); ?>" border="0" /> <?php echo $dest_form->getDetailTitle(); ?></a></li><?php
		}
	}
	echo "</ul>";
	
	do_hook('divright_after');
  ?></div><?php
 }

?></div><?php


require_once(getSkinFile("footer.php")); ?>
