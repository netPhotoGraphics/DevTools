<?php

/**
 * Package list generator
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 * @category package
 */
// force UTF-8 Ø

define("OFFSET_PATH", 3);
require_once(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . "/zp-core/admin-functions.php");

$stdExclude = Array('.', '..', '.DS_Store', '.cache', 'Thumbs.db', '.htaccess', '.svn', 'debug.html', '.buildpath', '.project', '.settings');


$_zp_resident_files[] = 'index.php';

$_zp_resident_files[] = THEMEFOLDER;
foreach ($_zp_gallery->getThemes() as $theme => $data) {
	if (zenPhotoTheme($theme)) {
//		$_zp_resident_files[] = THEMEFOLDER . '/' . $theme;
//		$_zp_resident_files = array_merge($_zp_resident_files, getResidentFiles(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme, $stdExclude));
	}
}

$_zp_resident_files[] = USER_PLUGIN_FOLDER;
$paths = getPluginFiles('*.php');
foreach ($paths as $plugin => $path) {
	if (strpos($path, USER_PLUGIN_FOLDER) !== false) {
		$p = file_get_contents($path);
		$i = strpos($p, '* @category');
		if (($key = $i) !== false) {
			$key = strtolower(trim(substr($p, $i + 11, strpos($p, "\n", $i) - $i - 11)));
			if ($key == 'package') {
				if (is_dir($dir = stripSuffix($path))) {
					$_zp_resident_files[] = str_replace(SERVERPATH . '/', '', $dir) . '/';
					$_zp_resident_files = array_merge($_zp_resident_files, getResidentFiles($dir, $stdExclude));
				}
				$_zp_resident_files[] = str_replace(SERVERPATH . '/', '', $path);
			}
		}
	}
}

$_zp_resident_files[] = ZENFOLDER;
$_zp_resident_files = array_merge($_zp_resident_files, getResidentFiles(SERVERPATH . '/' . ZENFOLDER, array_merge($stdExclude, array('setup', 'version.php'))));

$_special_files[] = ZENFOLDER . '/version.php';
$_special_files[] = ZENFOLDER . '/setup';
$_special_files = array_merge($_special_files, getResidentFiles(SERVERPATH . '/' . ZENFOLDER . '/setup', $stdExclude));

$filepath = SERVERPATH . '/' . getOption('zenphoto_package_path') . '/zenphoto.package';
@chmod($filepath, 0666);
$fp = fopen($filepath, 'w');
foreach ($_zp_resident_files as $component) {
	fwrite($fp, $component . "\n");
}
foreach ($_special_files as $component) {
	fwrite($fp, $component . ":*\n");
}

fwrite($fp, count($_zp_resident_files) + count($_special_files));
fclose($fp);
clearstatcache();
header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg=Package created and stored in the ' . getOption('zenphoto_package_path') . ' folder.');
exitZP();

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 */
function getResidentFiles($folder, $exclude) {
	global $_zp_resident_files;
	$dirs = array_diff(scandir($folder), $exclude);
	$localfiles = array();
	$localfolders = array();
	foreach ($dirs as $file) {
		$file = str_replace('\\', '/', $file);
		$key = str_replace(SERVERPATH . '/', '', filesystemToInternal($folder . '/' . $file));
		if (is_dir($folder . '/' . $file)) {
			$localfolders[] = $key;
			$localfolders = array_merge($localfolders, getResidentFiles($folder . '/' . $file, $exclude));
		} else {
			$localfiles[] = $key;
		}
	}
	return array_merge($localfiles, $localfolders);
}

?>