<?php

	define('DIR', '.');

	function change($dir) {
		$items = glob($dir . '/*.php');

		foreach ($items as $item) {
			$old_cnt = file_get_contents($item);
			if (contains($old_cnt, '@Zend')) {
				continue;
			}

			$new_cnt = trim($old_cnt);
			if ($old_cnt !== $new_cnt) {
				echo 'Clean file: ' . $item . "<br />\r\n";
				file_put_contents($item, $new_cnt);
			}
		}

		$dirs = array_filter(glob($dir . '/*'), 'is_dir');
		foreach ($dirs as $d) {
			change($d);
		}
	}

	change(DIR);


