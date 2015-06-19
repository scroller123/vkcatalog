<?php
/*
 * HIGHLOAD test
 */
header('Content-type: text/html; charset=utf-8');


function gettime()
{
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = (double)($mtime[1]) + (double)($mtime[0]);
	return ($mtime);
}

$start = gettime();


$ch = array();
$mh = curl_multi_init();


/*
 * LOAD 1000 PAGES !
 */
for($i=0; $i<500; $i++) {
	$ch[] = curl_init();
	curl_setopt($ch[count($ch)-1], CURLOPT_URL, "http://vkcatalog.gsup.ru:85/?loadtest&page=".mt_rand(0, 20000));
	curl_setopt($ch[count($ch)-1], CURLOPT_HEADER, 0);
	curl_setopt($ch[count($ch)-1], CURLOPT_RETURNTRANSFER, TRUE);

	curl_multi_add_handle($mh, $ch[count($ch)-1]);
}


$active = null;
//execute the handles
do {
	$mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && $mrc == CURLM_OK) {
	if (curl_multi_select($mh) != -1) {
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	}
}

//close the handles
foreach ($ch as $c){
	$content = curl_multi_getcontent($c);
	echo $content . "<br>";
	$total += doubleval($content);
	$max = $max < doubleval($content) ? doubleval($content) : $max;
	curl_multi_remove_handle($mh, $c);
}
echo "avg: " . ($total/count($ch)) . "<br>";
echo "max: " . $max . "<br>";
curl_multi_close($mh);

echo count($ch)." страниц, выполнено за ".(gettime()-$start)." сек.";