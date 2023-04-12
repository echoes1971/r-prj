<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: gestione_menu.php $
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

?><script>
function hideMenu(numero_menu,numero_sottolivelli,sublevel_1,sublevel_2) {
	item_no=0;
	while( showHide( 'menu_'+numero_menu+'_'+item_no) ) {
		item_no++;
	}
}
</script>
<?php
$display='none';
$menu_counter=0;
foreach($mymenu as $title=>$items) {
?><table class="menu" valign="middle" align="center" width="95%" >
	<tr class="menu" onmouseover="javascript:this.style.cursor='pointer'" onmouseout="javascript:this.style.cursor='normal'">
		<th class="menu" onclick="javascript:hideMenu(<?php echo $menu_counter; ?>,1);"><?php echo $title; ?></th>
	</tr><?php
	$item_counter=0;
	foreach($items as $label => $item) {
?><tr class="menu" id='menu_<?php echo $menu_counter; ?>_<?php echo $item_counter; ?>' style="display:<?php echo $display; ?>">
		<td class="menu" <?php if(array_key_exists('title',$item) && $item['title']>"") { echo "title='".$item['title']."'"; } ?>><?php
		if(array_key_exists('href',$item) && $item['href']>"") {
		?><a class="menu" href="<?php echo $item['href']; ?>" target="<?php echo $item['target']; ?>"><?php echo $label; ?></a><?php
		} else {
			if($label=='--')
				echo "<hr>";
			else
				echo $label;
		}
	?></td>
	</tr><?php
		$item_counter++;
	}
?></table><br/><?php
	$menu_counter++;
}

require_once( getSkinFile("mng/gestione_footer.php") ); ?>
