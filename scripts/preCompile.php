<?php
	include_once '../standalone.php';
	$requestedFile = '..' . $_SERVER['REQUEST_URI'];

	if (contains($requestedFile, '?')) {
		$requestedFile = mb_substr($requestedFile, 0, mb_strpos($requestedFile, '?'));
	}

	$info = pathinfo($requestedFile);
	$extension = $info['extension'];

	if ($extension == 'js') {
		header('Content-type: text/javascript; charset=utf-8');
	} else {
		header('Content-type: text/css; charset=utf-8');
	}

	$filename = $info['basename'];
	chdir($info['dirname']);
	include 'compile.php';
	echo file_get_contents($filename);

