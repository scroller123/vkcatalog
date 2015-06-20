<?php
include_once 'init.php';
include_once 'dbconnect.php';
include_once 'functions.php';
fix_time();



if (isset($_GET['add'])) {
	include 'add.php';
} elseif (isset($_GET['edit'])) {
	include 'edit.php';
} else {
	include 'catalog.php';
}



if (isset($_GET['loadtest'])) {
	print sprintf("%0.10f", get_execution_time());
} else {
	include 'templates/template.html.php';
}
?>