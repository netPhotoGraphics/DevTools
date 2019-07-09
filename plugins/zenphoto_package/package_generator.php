<?php

/**
 * Package list generator
 *
 * @author Stephen Billard (sbillard)
 * @package plugins/package

 * @category plugins/developerTools
 */
// force UTF-8 Ø

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . "/zp-core/admin-functions.php");

$stdExclude = Array('Thumbs.db', 'debug.html', 'readme.md', 'data');

$_resident_files[] = THEMEFOLDER;
foreach ($_gallery->getThemes() as $theme => $data) {
	if (protectedTheme($theme)) {
		$_resident_files[] = THEMEFOLDER . '/' . $theme;
		$_resident_files = array_merge($_resident_files, getResidentFiles(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme, $stdExclude));
	}
}

$_resident_files[] = USER_PLUGIN_FOLDER;
$paths = getPluginFiles('*.php');
foreach ($paths as $plugin => $path) {
	if (strpos($path, USER_PLUGIN_FOLDER) !== false) {
		if (distributedPlugin($plugin)) {
			if (is_dir($dir = stripSuffix($path))) {
				$_resident_files[] = str_replace(SERVERPATH . '/', '', $dir) . '/';
				$_resident_files = array_merge($_resident_files, getResidentFiles($dir, $stdExclude));
			}
			$_resident_files[] = str_replace(SERVERPATH . '/', '', $path);
		}
	}
}

$_resident_files[] = CORE_FOLDER;
$_resident_files = array_merge($_resident_files, getResidentFiles(SERVERPATH . '/' . CORE_FOLDER, array_merge($stdExclude, array('setup', 'version.php'))));

$_special_files[] = CORE_FOLDER . '/version.php';
$_special_files[] = CORE_FOLDER . '/setup';
$_special_files = array_merge($_special_files, getResidentFiles(CORE_SERVERPATH . 'setup', $stdExclude));

$filepath = SERVERPATH . '/' . getOption('package_path') . '/netPhotoGraphics.package';
@chmod($filepath, 0666);
$fp = fopen($filepath, 'w');
foreach ($_resident_files as $component) {
	writeComponent($fp, $component, '');
}
foreach ($_special_files as $component) {
	writeComponent($fp, $component, ':*');
}

fwrite($fp, count($_resident_files) + count($_special_files));
fclose($fp);
clearstatcache();
header('Location: ' . getAdminLink('admin.php') . '?action=external&msg=Package created and stored in the ' . getOption('package_path') . ' folder.');
exit();

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 */
function getResidentFiles($folder, $exclude) {
	global $_resident_files;
	$dirs = array_diff(scandir($folder), $exclude);
	$localfiles = array();
	$localfolders = array();
	foreach ($dirs as $file) {
		if ($file{0} != '.') {
			$file = str_replace('\\', '/', $file);
			$key = str_replace(SERVERPATH . '/', '', filesystemToInternal($folder . '/' . $file));
			if (is_dir($folder . '/' . $file)) {
				$localfolders[] = $key;
				$localfolders = array_merge($localfolders, getResidentFiles($folder . '/' . $file, $exclude));
			} else {
				$localfiles[] = $key;
			}
		}
	}
	return array_merge($localfiles, $localfolders);
}

function writeComponent($file, $component, $flag) {
	$component = strtr($component, array(CORE_FOLDER . '/' . PLUGIN_FOLDER => '%extensions%', CORE_FOLDER => '%core%'));
	fwrite($file, $component . $flag . "\n");
}

?>