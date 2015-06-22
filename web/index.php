<?php
include_once '../src/init.php';
include_once '../src/dbconnect.php';
include_once '../src/functions.php';
fix_time();


if (isset($_GET['add'])) {
	include 'action/add.php';
} elseif (isset($_GET['edit'])) {
	include 'action/edit.php';
} elseif (isset($_GET['delete'])) {
	include 'action/delete.php';
} else {
	include 'action/catalog.php';
}


if (isset($_GET['loadtest'])) {
	print sprintf("%0.10f", get_execution_time());
} else {
	include 'templates/template.html.php';
}
?>