#!/usr/bin/php
<?php

	require_once(__DIR__ . '/vendor/autoload.php');

	use ShaneMcC\PTouchPrint\RasterImage;
	use ShaneMcC\PTouchPrint\PrintJob;

	$options = getopt('', ['size:', 'file:', 'cups:', 'ip:', 'port:', 'raw', 'debug', 'out']);
	$size = isset($options['size']) ? $options['size'] : 12;

	if (!isset($options['file'])) {
		// Try our demo files.
        $options['file'] = __DIR__ . '/images/demo-' . $size . 'mm.png';
	}

	if (!isset($options['file'])) {
		echo 'You need to specify a file to try and print.', "\n";
		die(1);
	}

	if (!file_exists($options['file'])) {
		echo 'You need to specify a valid file to try and print.', "\n";
		die(1);
	}

	$image = new RasterImage($options['file']);
	$job = new PrintJob($size);
	$job->startJob()->addImage($image)->endJob();

	if (isset($options['out'])) {
		$image->displayImage();
		die(0);
	}

	if (isset($options['debug'])) {
		$job->printHEXToSTDOUT();
		die(0);
	}

	if (isset($options['raw'])) {
		$job->printToSTDOUT();
		die(0);
	}

	if (isset($options['cups'])) {
		$job->printToCUPS($options['cups']);
		die(0);
	}

	if (isset($options['ip'])) {
		$job->printToIP($options['ip'], isset($options['port']) ? $options['port'] : 9100);
		die(0);
	}

	echo 'You need to specify something to do.', "\n";
	die(1);
