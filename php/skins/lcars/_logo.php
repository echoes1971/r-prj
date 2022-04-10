<div class="lcars-title left horizontal lcars-anakiwa-bg"><?php
// echo $_SESSION['site_title'];
echo "<a class=\"lcars-black-color\" href=\"".ROOT_FOLDER."main.php?obj_id=".$_SESSION['root_obj']->getValue('id')."\">";
echo "<b>".$_SESSION['site_title']."</b>";
echo "</a>";
?></div>