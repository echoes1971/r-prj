<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: login.php $
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
require_once getSkinFile("header.php");

// Message: start.
$msg = array_key_exists('messaggio',$_REQUEST) ? $_REQUEST['messaggio'] : null;
if ( $msg!=null ) {
	echo $popupMessaggio->render();
}
// Message: end.

?><div id="middle_container" style="text-align: center;"><?php
?><div id="middle_noright" style="left:0em; right:0em;"><?php
if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE ")>0 ) {
?><br/><br/><br/><br/><br/><br/><br/><br/><br/><?php
}
?><table width="100%" height="100%">
 <tr>
  <td align="center" valign="middle"><form action="login_do.php" method="POST"><?php
  if($from_obj_id!==null) {
	echo "<input type=\"hidden\" name=\"from_obj_id\" value=\"$from_obj_id\" />";
  }
?><table class="login" align="center" valign="middle" width="200">
  <tr><th class="login" colspan="2">Login</th></tr>
  <tr>
   <td class="login" align="right">Login</td><td class="login"><input class="login" type="text" name="field_login"></td>
  </tr>
  <tr>
   <td class="login" align="right">Password</td><td class="login"><input class="login" type="password" name="field_pwd"></td>
  </tr>
  <tr>
   <td class="login" colspan="2" align="center"><input class="login" type="submit" name="submit" value="Login"></td>
  </tr>
 </table>
</form>

  </td>
 </tr>
</table><?php
?></div><?php
?></div><?php

require_once getSkinFile("footer.php"); ?>