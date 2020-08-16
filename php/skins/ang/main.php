<?php
/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: index.php $
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

$_dirname_self = dirname($_SERVER['PHP_SELF']);

if(!defined("MY_ROOT_FOLDER")) define("MY_ROOT_FOLDER", "../../");

?><!doctype html>
<html>
<head>
    <!-- META -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"><!-- Optimize mobile viewport -->

    <!-- SCROLLS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        html                    { overflow-y:scroll; }
/*         body                    { padding-top:50px; } */
    </style>

    <!-- SPELLS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.7.8/angular.min.js"></script><!-- load angular -->
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.7.8/angular-route.js"></script><!-- load angular -->
    <script src="<?php echo getSkinFile("core.js"); ?>"></script>
<!--     <script src="nodesCtrl.js"></script> -->
<!--     <script src="psCtrl.js"></script> -->
<!--     <script src="usersCtrl.js"></script> -->

    <title><?php echo $_SESSION['site_title']; ?>{{ current_obj && current_obj.name && current_obj.name!='Home' ? ' '+current_obj.name : ''}}</title>
    <base href="<?php echo getSkinFile(""); ?>">
</head>
<body ng-app="rprjApp" ng-controller="mainCtrl">
<div>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#"><?php echo $_SESSION['site_title']; ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
<?php
 if(array_key_exists('root_obj',$_SESSION) && $_SESSION['root_obj']!==null) {
  echo "<li class=\"nav-item\"><a class=\"nav-link\" href=\"".MY_ROOT_FOLDER."main.php?obj_id=".$_SESSION['root_obj']->getValue('id')."\">".$_SESSION['root_obj']->getValue('name')."</a></li> ";
 }
 if(array_key_exists('menu_top',$_SESSION) && is_array($_SESSION['menu_top'])) {
  foreach($_SESSION['menu_top'] as $menu_item) {
   if($menu_item->getTypeName()!='DBEFolder' && $menu_item->getTypeName()!='DBELink' && $menu_item->getTypeName()!='DBEPeople') continue;
   echo "<li class=\"nav-item\">";
   if($menu_item->getTypeName()=='DBELink') {
    $tmpform = new FLink(); $tmpform->setValues($menu_item->getValuesDictionary());
    echo " ".$tmpform->render_view()." "; //$dbmgr)." ";
   } else {
    echo " <a class=\"nav-link\" href=\"".MY_ROOT_FOLDER."main.php?obj_id=".$menu_item->getValue('id')."\">".$menu_item->getValue('name')."</a> ";
   }
   echo "</li>";
  }
 }
?>
      <li class="nav-item">
        <a class="nav-link" href="#">Link</a>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Dropdown
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
  </div>
</nav>

{{ myui }}

</body>
</html>
