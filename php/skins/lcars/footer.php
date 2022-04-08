<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: footer.php $
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

do_hook('footer_before');
?><div id="footer" class="lcars-row"><?php
  ?><div class="lcars-elbow left-top lcars-tan-bg"></div><?php
  ?><div class="lcars-bar horizontal both-divider bottom"><?php
do_hook('footer_content_before');
?><div class="lcars-title right horizontal">&copy; <?php echo date("Y"); ?> by Wintermute - All rights reserved.</div><?php
  ?></div><?php
  ?><div class="lcars-bar horizontal right-end left-divider bottom"></div><?php

  do_hook('footer_content_after');
?></div><?php
do_hook('footer_after');


?></div>
</body>
</html>
