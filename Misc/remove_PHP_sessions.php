<?php

/*
 * Removes Session files from an installation.
 * Assumes the files are stored in PHP_sessions
 */
$me = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
if (!(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
	$protocol = "https";
} else if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == "https") {
	$protocol = "https";
} else if (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
	$protocol = "https";
} else {
	$protocol = "http";
}
$const_webpath = $protocol . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($me), '/');
$me = basename($me);
$live = isset($_GET['remove']);

$folder = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/PHP_sessions';
if (isset($_GET['files'])) {
	$files = $_GET['files'];
	$removed = $_GET['removed'];
} else {
	$files = $removed = 0;
}

echo 'Removing Session files from ' . $folder . '.<br/>';

if (($dir = opendir($folder)) !== false) {
	while (($file = readdir($dir)) !== false) {
		if (strpos($file, 'sess_') !== 0) {
			continue;
		}
		$files++;
		$file_path = $folder . '/' . $file;
		if ($live) {
			if (@unlink($file_path)) {
				$removed++;
			}
		}
		if ($removed && $removed % 500 == 0) {
			printf('Removed %1$s of %2$s Session files.<br/>', number_format($removed), number_format($files));
			echo '<meta http-equiv="refresh" content="0; url=' . $const_webpath . '/' . $me . ($live ? '?remove&' : '?') . 'files=' . $files . '&removed=' . $removed . '" />';
			exit();
		}
	}
	printf('Removed %1$s of %2$s Session files .<br/>', number_format($removed), number_format($files));
	echo 'done...<br/>';
} else {
	echo 'could not open ' . $folder;
}
