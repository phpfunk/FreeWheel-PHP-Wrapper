<?php
function __autoload($class)
{
	$class = $_SERVER['DOCUMENT_ROOT'] . "/PATH/TO/YOUR/FILES/class." . strtolower(str_replace("_", "-", $class)) . ".php";
	if (file_exists($class)) { include_once($class); } 
}
?>