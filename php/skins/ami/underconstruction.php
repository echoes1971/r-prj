<?php
/**
 * @copyright &copy; 2020 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: underconstruction.php $
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

// Message: start.
$msg = array_key_exists('messaggio',$_REQUEST) ? $_REQUEST['messaggio'] : null;
if ( $msg!=null ) {
	echo $popupMessaggio->render();
}
// Message: end.
?><div id="middle_container"><?php
?><div id="left"></div>
<div id="middle_noright">
 <div id="underconstruction">
  <br/>
  <h1>Under Construction</h1>
  <br/>
  <h1>Under Construction</h1>
  <br/>
  <h1>Under Construction</h1>
  <br/>
  <h1>Under Construction</h1>
  <br/>
 </div>
</div>
</div><?php

require_once(getSkinFile("footer.php")); ?>
