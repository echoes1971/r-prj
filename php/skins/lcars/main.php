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

?><div id="left-menu" class="lcars-column start-space lcars-u-1"><?php
?><div class="lcars-bar lcars-u-1"><?php
// Root Object (Home)
echo "<div class=\"lcars-title vertical lcars-rust-bg\">";
echo "<a href=\"main.php?obj_id=".$root_obj->getValue('id')."\">".$root_obj->getValue('name')."</a>";
echo "</div>";

do_hook('divleft_before');
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
			}
		} else {
			if(is_a($menu_item,'DBELink') && !$myform->isInternal()) {
			}
		}
		if($menu_item->getValue('id')>'') {
			$txt_align = "";
			$fg_color = "";
			$bg_color = "lcars-rust-bg";
			if( is_a($myform,'FFolder') ) {
				$fg_color = "lcars-white-color";
				$bg_color = "lcars-dodger-blue-alt-bg";
			} else if( is_a($myform,'FLink') ) {
				$fg_color = "lcars-white-color";
				$bg_color = "lcars-dodger-blue-bg";
			} else if( is_a($myform,'FNote') ) {
				$txt_align = "txtright";
				$fg_color = "lcars-black-color";
				$bg_color = "lcars-neon-carrot-bg";
			} else if( is_a($myform,'FPage') ) {
				$txt_align = "txtright";
				$fg_color = "lcars-black-color";
				$bg_color = "lcars-red-damask-bg";
			}

			
			echo "<div class=\"lcars-title vertical $txt_align $fg_color $bg_color\">$indent";

			// if($myform->getDetailIcon()>"") {
			// 	echo "<img src=\"".getSkinFile($myform->getDetailIcon())."\" alt=\"\" />&nbsp;";
			// }
			if( is_a($myform,'FLink') ) {
				echo $myform->render_view($dbmgr);
			} else
				echo "<a class=\"$fg_color\" href=\"main.php?obj_id=".$menu_item->getValue('id')."\">".$menu_item->getValue('name')."</a>";
			echo "</div>";
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
?></div><?php

do_hook('divleft_after');
?></div><?php

?><div id="container"><?php

// Middle
?><div id="<?php echo $dbmgr->hasGroup($GROUP_WEBMASTER) ? "middle" : "middle_noright"; ?>"><?php
 ?><div class="lcars-row"><?php
 ?><div class="lcars-bar horizontal lcars-cosmic-bg left-end decorated bottom"></div><?php
 ?><div class="lcars-bar horizontal lcars-lilac-bg"><?php
$breadcrumbs=array();
$breadcrumb_fg="lcars-lilac-color";
$breadcrumb_bg="lcars-black-bg";
foreach($parent_list as $bread_crumb) {
	if(!is_a($bread_crumb,'DBEFolder')) continue;
	$breadcrumbs[] = "<div class=\"lcars-title horizontal $breadcrumb_bg\"><a class=\"$breadcrumb_fg\" href=\"main.php?obj_id=".$bread_crumb->getValue('id')."\">".$bread_crumb->getValue('name')."</a></div>";
}
echo join("",$breadcrumbs);
//echo join("<li class=\"breadcrumb\"> &gt; </li>",$breadcrumbs);
 ?></div><?php
 ?><div class="lcars-bar horizontal lcars-cosmic-bg right-end decorated bottom"></div><?php
 ?></div><?php
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
	echo "<div class=\"content-header\">";
	echo "<h1 class=\"content lcars-lilac-color\">".$current_form->getField('name')->render_view()."</h1>";
	$_stardate_str = strtotime($current_form->getField('last_modify_date')->getValue());
	// $_stardate = substr($_stardate_str,0,-3).".".substr($_stardate_str,-3);
	$_stardate = strftime("%Y%m%d.%H%M",$_stardate_str);
	$_stardate_date = $current_form->getField('last_modify_date')->render_view();
	echo "<h2 class=\"content lcars-lilac-color\">Stardate $_stardate</h2>";
	// echo "<h2 class=\"content lcars-lilac-color\">Stardate $_stardate • $_stardate_date</h2>";
	echo "</div>";
	$_content_body_fg = "lcars-lavender-purple-color";
	echo "<div class=\"content-body $_content_body_fg\">";
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
	echo "</div>";

	$content_items_count=count($content_items);
	foreach($content_items as $content_item) {
		echo "<div class=\"lcars-row\">";

		echo "<div class=\"lcars-element left-rounded lcars-eggplant-bg  lcars-u-1-1\"></div>";
		$_desc = $content_item->getValue('description');
		echo "<div class=\"lcars-element lcars-lavender-purple-bg lcars-u-1-1\">";
		echo "<img src=\"".getSkinFile($content_item->getDetailIcon())."\" alt=\"\" />&nbsp;";
		if( is_a($content_item,'FFile') && $content_item->isImage() && $content_item->getValue('alt_link')>'' ) {
			echo $content_item->getValue('name');
		} else {
			echo "<a href=\"main.php?obj_id=".$content_item->getValue('id')."\">".$content_item->getValue('name')."</a>";
		}
		echo "</div>";

		echo "<div class=\"lcars-element lcars-black-bg lcars-u-3-2\">";
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
	// 		echo $content_item->render_view($dbmgr);
	// 		echo $content_item->render_view();
		}
		if($_desc>'') {
			echo "<p class=\"content_item\">".str_replace("\n","<br/>",$_desc)."</p>";
		}

		// if($content_items_count>1) echo "<hr/>";
		echo "</div>";
		echo "<div class=\"lcars-element right-rounded lcars-cosmic-bg lcars-u-1-1\"></div>";

		echo "</div>";

		// echo "<div class=\"lcars-row\"><div class=\"lcars-element lcars-black-bg lcars-u-4-1\"></div></div>";
	}
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
