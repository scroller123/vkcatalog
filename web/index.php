<?php
include_once 'init.php';
include_once 'dbconnect.php';
include_once 'functions.php';
fix_time();



if (isset($_GET['add'])) {


} else {
	include 'catalog.php';
}



if (isset($_GET['loadtest'])) {
	print get_execution_time();
} else {
	include 'templates/template.html.php';
}
?>