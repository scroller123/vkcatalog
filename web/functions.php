<?php

function gettime()
{
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = (double)($mtime[1]) + (double)($mtime[0]);
	return ($mtime);
}

function fix_time()
{
	$_SESSION[starttime] = gettime();
}


function get_execution_time()
{
	return gettime()-$_SESSION[starttime];
}

?>