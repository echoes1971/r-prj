<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: db_update.php $
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

require_once getSkinFile("mng/gestione_header.php");

// Message: start.
$msg = array_key_exists('messaggio',$_REQUEST) ? $_REQUEST['messaggio'] : null;
if ( $msg!=null ) {
	echo $popupMessaggio->render();
}
// Message: end.

?><h1>DB <?php echo $action_descr; ?></h1><?php

 if($utente!==null) {
  ?><div style="float:right;text-align:right;clear:right;"><font size="1">&nbsp;<b>User:&nbsp;<?php
  echo $utente->getValue('fullname');
  ?><br/><a href="<?php echo ROOT_FOLDER; ?>logout_do.php">Logout</a></b></font></div><?php
 }
?>
 <table>
  <tr>
   <th align="right">Schema:</th><td><?php echo $dbmgr->getSchema(); ?></td>
   <th align="right"></th><td><?php  ?></td>
  </tr>
  <tr>
   <th align="right">DB Version</th><td><?php echo $dbmgr->db_version(); ?></td>
   <th align="right">New Version</th><td><?php echo DB_VERSION; ?></td>
  </tr>
  <tr><td></td><td></td></tr>
 </table>
 <input type="button" value="<?php echo $action_descr; ?>" onclick="javascript:document.getElementById('db_update_do').src='db_update_do.php';">
 <br/>
 <div id="div_db_update_do"
         style="position:absolute;top:14em;left:1em;right:1em;bottom:1em;border:1px solid black;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;">
 <iframe id="db_update_do" width="100%" height="100%" border="1"></iframe>
 </div>


<?php require_once getSkinFile("mng/gestione_footer.php"); ?>