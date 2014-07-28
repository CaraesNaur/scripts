<?php

// put library filenames in here
$libs = array(
);

$store = array();
$counts = 0;

foreach ($libs as $lib) {
	$f = file('./' . $lib);
	$f = array_slice($f, 4);
	$f = trim(implode('', $f));
	$f = preg_split('/(?=# Dev Name:)/', $f);
	echo "$lib chunks: " . count($f) . "\n";

	foreach ($f as $d) {
		if (trim($d) == '') {
			continue;
		}

		preg_match('/(?<=^# Dev Name: )(\S+)/', $d, $m);

		$n = $m[1];

		$d = preg_replace('/(?<=ENDDEF).*/s', '', trim($d));

		if (!array_key_exists($n, $store)) {
			$store[$n] = array();
		}

		$store[$n][] = array(
			'src_lib' => $lib,
			'device' => $d
		);

		$counts++;
	}
}

echo "Devices: " . count(array_keys($store)) . ' (' . $counts . " entries)\n\n";

$lib_header = "EESchema-LIBRARY Version 2.3  29/04/2008-12:22:53\n# Converted with eagle2kicad.ulp Version 0.9\n# Device count = XXXX\n\n";
$lib_footer = "#End Library\n";
$lib_content = '';

$lc = 0;

//$store = array_slice($store, 0, 10);

foreach ($store as $dev => $r) {
	$c = count($r);

	if ($c == 1) {
		echo "Including unique device $dev\n";
		$lib_content .= $r[0]['device'] . "\n\n";
		$lc++;

	}
	else {
		echo "\n";

		$dh = array();

		foreach ($r as $k => $v) {
			$dh[] = trim(array_shift(preg_split('/(^DEF)/ms', $v['device'])));
			//echo "[$k]: from " . $v['src_lib'] . "\n$dh\n";
		}

		$du = array_unique($dh);

		if (count($du) == 1) {
			echo "Including one of " . count($dh) . " identical devices for $dev\n";
			$lib_content .= $r[0]['device'] . "\n\n";
			$lc++;
			continue;
		}

		foreach ($r as $k => $v) {
			if (array_key_exists($k, $du)) {
				echo "[$k]: from " . $v['src_lib'] . "\n" . $du[$k] . "\n";
			}
		}

		$p = '';
		$s = array_keys($du);
		$l = false;

		while (!$l) {
			$p = readline("Select $dev device definition (" . implode(', ', $s) . "): ");

			if (ctype_digit($p)) {
				$p = (int) $p;

				if (in_array($p, $s)) {
					$l = true;

					echo "Including selected entry $p for device $dev\n";
					$lib_content .= $r[$p]['device'] . "\n\n";
					$lc++;
				}
			}
		}
	}
}

// edit output fielname here
$fh = fopen('OUTPUT_FILENME', 'w');
fwrite($fh, str_replace('XXXX', $lc, $lib_header));
fwrite($fh, $lib_content);
fwrite($fh, $lib_footer);


echo "Lib: $lc\n";
