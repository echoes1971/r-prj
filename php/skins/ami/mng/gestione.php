<?php
/**
 * @copyright &copy; 2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: gestione.php $
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
 
require_once( getSkinFile("header.php") );

?><div class="row"><?php

echo "<div class=\"col-2\" style=\"height:50em;\">";
do_hook('mng_left_before');
?><iframe id="gestione_left" name="gestione_left" src="gestione_menu.php" width="100%" height="100%" ></iframe><?php
do_hook('mng_left_after');
echo "</div>";
?><div class="middle_content col-10" style="height:50em;"><iframe id="gestione_right" name="gestione_right" <?php
if($dbmgr->hasGroup($GROUP_WEBMASTER)) echo "src=\"dbe_view.php?dbetype=DBEFolder&formtype=FFolder&field_id=".$_SESSION['root_obj']->getValue('id')."\"";
?> width="100%" height="100%"></iframe></div><?php

/** 2011.04.23: disabled by now
echo "<div id=\"right\">";
do_hook('mng_right_before');
// mng right: what to put here?
?><?php
do_hook('mng_right_after');
echo "</div>";
*/
echo "</div>";

require_once( getSkinFile("footer.php") ); ?>