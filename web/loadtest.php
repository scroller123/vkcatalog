<?php
/*
 * HIGHLOAD test
 *
 * SERVER INFORMATION:
 * Operating system	Debian Linux 6.0
 * Processor information	Intel(R) Core(TM) i3-4130 CPU @ 3.40GHz, 4 cores
 * Real memory 8 GB
 *
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



/*
 * LOAD 1000 PAGES !
 */
for($j=0; $j<10; $j++) {
	$ch = array();
	$mh = curl_multi_init();
	$empty_content_counter = 0;
	$max = 0;
	$total = 0;

	for($i=0; $i<100; $i++) {
		$ch[] = curl_init();
		curl_setopt($ch[count($ch)-1], CURLOPT_URL, 'http://vkcatalog.gsup.ru/?loadtest&order=' . (mt_rand(0, 1) ? 'id' : 'price') . '&direction=' . (mt_rand(0, 1) ? 'asc' : 'desc') . '&page=' . mt_rand(0, 20000));
		curl_setopt($ch[count($ch)-1], CURLOPT_HEADER, 0);
		curl_setopt($ch[count($ch)-1], CURLOPT_RETURNTRANSFER, TRUE);
		curl_multi_add_handle($mh, $ch[count($ch)-1]);
	}


	$active = null;
	do {
		$mrc = curl_multi_exec($mh, $active);
	} while ($mrc == CURLM_CALL_MULTI_PERFORM);

	while ($active && $mrc == CURLM_OK) {
		if (curl_multi_select($mh) != -1) {

			do {
				usleep(100000);
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
	}



	//close the handles
	foreach ($ch as $i=>$c) {
		$content = curl_multi_getcontent($c);
		if (empty($content))
			$empty_content_counter++;
		$total += doubleval($content);
		$max = $max < doubleval($content) ? doubleval($content) : $max;
		curl_multi_remove_handle($mh, $c);
	}
	print '<div style="float: left; border: 1px solid #606060; margin: 0px 5px 5px 0px; padding: 5px; ">';
	print 'Среднее: ' . ($total/count($ch)) . '<br/>';
	print 'Максимум: <strong' . ($max >= 0.3 ? ' style="color: red"' : '') . '>' . $max . '</strong><br/>';
	curl_multi_close($mh);

	print count($ch).' страниц, выполнено за '.(gettime()-$start).' сек.<br/>';
	if ($empty_content_counter > 0)
		print $empty_content_counter . ' страниц без результата<br/>';

	print '</div>';

}

?>
<div style="clear: both;">Всего затрачено: <?php print (gettime()-$start);?> сек.</div>