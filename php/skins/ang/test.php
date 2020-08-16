<?php

$myObj = (object)[]; //new stdClass();
$myObj->name = "Cippa";

$myJSON = json_encode($myObj);

echo $myJSON;
?>
