<?php
	/**
	 * Скрипт для перекодирования системных файлов из windows-1251 в utf-8, запускается из корня сайта.
	 * Перед применением стоит сделать бэкап файлов.
	 */
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	function checkDir($dir, $backup = false) {

		$items = glob('{' . "{$dir}/*.php,{$dir}/*.js',{$dir}/*.xsl" . '}', GLOB_BRACE);

		foreach ($items as $item) {
			checkFile($item, $backup);
		}

		$dirs = glob($dir . '/*', GLOB_ONLYDIR);

		foreach ($dirs as $d) {
			checkDir($d, $backup);
		}
	}

	function checkFile($item, $backup = false) {

		if (mb_substr($item, -4) == '.bak') {
			return;
		}

		$filesize = filesize($item);
		if ($filesize > 1024 * 1024) {
			echo 'File ' . $item . "is too big<br />\r\n";
		} else {

			$oldContent = file_get_contents($item);

			$update = false;

			$newContent = trim($oldContent);
			if ($oldContent != $newContent) {
				$update = true;
				echo 'Contains spaces: ' . $item . "<br />\r\n";
			}

			$newContent2 = str_replace("\r\n", "\n", $newContent);
			if ($newContent != $newContent2) {
				$update = true;
				$newContent = $newContent2;
				echo 'Contains \r\n: ' . $item . "<br />\r\n";
			}

			if (in_array(mb_substr($newContent, 0, 2), [pack('CC', 254, 255), pack('CC', 255, 254)])) {
				$newContent = mb_substr($newContent, 2);
				echo 'Contains bom: ' . $item . "<br />\r\n";
			} elseif (mb_substr($newContent, 0, 3) == pack('CCC', 239, 187, 191)) {
				$newContent = mb_substr($newContent, 3);
				echo 'Contains bom: ' . $item . "<br />\r\n";
			}

			if ($oldContent != $newContent) {
				$update = true;
			}

			if (!preg_match('//u', $oldContent)) {
				echo 'Not utf encoding: ' . $item . "<br />\r\n";
				$newContent = iconv('windows-1251', 'utf-8//IGNORE', $newContent);
				$update = true;
			}

			if ($update) {
				echo 'Change file: ' . $item . "<br />\r\n";
				file_put_contents($item, $newContent);
				if ($backup) {
					file_put_contents($item . '.bak', $oldContent);
				}
			}
		}
	}

	$dirs = [
		'./classes',
		'./js',
		'./libs',
		'./styles'
	];

	$files = [
		'./autothumbs.php',
		'./captcha.php',
		'./counter.php',
		'./cron.php',
		'./dummy.php',
		'./go-out.php',
		'./index.php',
		'./releaseStreams.php',
		'./sbots.php',
		'./session.php',
		'./sitemap.php',
		'./standalone.php',
		'./static_banner.php'
	];

	foreach ($dirs as $dir) {
		checkDir($dir);
	}

	foreach ($files as $item) {
		checkFile($item);
	}


