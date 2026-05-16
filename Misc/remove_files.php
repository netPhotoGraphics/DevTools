<?php

/*
 * Finds and (optionally) removes files from a folder
 * Does not recurse into directories
 */

$folder = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
$removed = false;
$me = basename(__FILE__);

echo 'finding files in ' . $folder . '<br/>';

if (($dir = opendir($folder)) !== false) {
	while (($file = readdir($dir)) !== false) {
		if ($file == $me) {
			continue;
		}
		set_time_limit(30);
		echo '.';
		$file_path = $folder . '/' . $file;
		echo $file;
		if ($isdir = is_dir($file_path)) {
			echo ' (dir)';
			if ($file == '.' || $file == '..') {
				echo ' skipped<br />';
				continue;
			}
		}

		if (is_link($file_path)) {
			$target = @readlink($file_path);
			echo '=>' . $target;
			if (!$target) {
				echo ' <b><i>failed to read link</b></i>';
			}
		}
		if (isset($_GET['remove']) && $_GET['remove'] && $file != $me) {
			if ($isdir) {
				$removed = @rmdir($file_path);
			} else {
				$removed = @unlink($file_path);
			}

			if ($removed) {
				echo ' removed';
			} else {
				echo ' <b>failed to remove</b>';
			}
		}
		echo "\n<br />";
	}
} else {
	echo 'could not open ' . $folder;
}
