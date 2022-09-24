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
require_once(file_get_contents(dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/core-locator.npg') . "admin-functions.php");

$repository = getOption('package_git_path') . '';
define('GIT_PATH', trim($repository, '/') . '/'); //	points to the folder used for package creation, normally the GIT folder

if (is_dir(GIT_PATH . CORE_FOLDER) && is_dir(GIT_PATH . THEMEFOLDER) && is_dir(GIT_PATH . USER_PLUGIN_FOLDER)) {
	$_resident_files[] = THEMEFOLDER;
	$dir = opendir(GIT_PATH . '/' . THEMEFOLDER . '/');
	while (($theme = readdir($dir)) !== false) {
		if ($theme[0] !== '.' && is_dir(GIT_PATH . '/' . THEMEFOLDER . '/' . $theme)) {
			$_resident_files[] = THEMEFOLDER . '/' . $theme;
			$_resident_files = array_merge($_resident_files, getFiles(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme, stdExclude));
		}
	}

	$_resident_files[] = USER_PLUGIN_FOLDER;
	$curdir = getcwd();
	chdir(GIT_PATH . '/plugins/');
	$filelist = safe_glob('*.php', 0);
	chdir($curdir);

	foreach ($filelist as $plugin) {
		if (is_dir($dir = USER_PLUGIN_SERVERPATH . stripSuffix($plugin))) {
			$_resident_files[] = 'plugins/' . stripSuffix($plugin) . '/';
			$_resident_files = array_merge($_resident_files, getFiles($dir, stdExclude));
		}
		$_resident_files[] = 'plugins/' . $plugin;
	}

	$_resident_files[] = CORE_FOLDER;
	$_resident_files = array_merge($_resident_files, getFiles(SERVERPATH . '/' . CORE_FOLDER, array_merge(stdExclude, array('setup', 'version.php'))));

	$_special_files[] = CORE_FOLDER . '/version.php';
	$_special_files[] = CORE_FOLDER . '/setup';
	$_special_files = array_merge($_special_files, getFiles(CORE_SERVERPATH . 'setup', stdExclude));

	$filepath = CORE_SERVERPATH . 'netPhotoGraphics.package';
	chmod($filepath, 0666);
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
	header('Location: ' . getAdminLink('admin.php') . '?action=external&msg=Package created and stored in the ' . CORE_SERVERPATH . ' folder.');
	exit();
}

header('Location: ' . getAdminLink('admin.php') . gettext('?action=external&error=errorbox&msg=The Package Generator option "Repository Path" is not defined or does not point to a netPhotoGraphics repository'));
exit();

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 * @param $exclude file names to exclude from the list
 */
function getFiles($folder, $exclude) {
	global $_resident_files;
	$dirs = array_diff(scandir($folder), $exclude);
	$localfiles = array();
	$localfolders = array();
	foreach ($dirs as $file) {
		$file = str_replace('\\', '/', $file);
		$key = str_replace(SERVERPATH . '/', '', filesystemToInternal($folder . '/' . $file));
		if (is_dir($folder . '/' . $file)) {
			$localfolders[] = $key;
			$localfolders = array_merge($localfolders, getFiles($folder . '/' . $file, $exclude));
		} else {
			$localfiles[] = $key;
		}
	}
	return array_merge($localfiles, $localfolders);
}

function writeComponent($file, $component, $flag) {
	$component = strtr($component, array(CORE_FOLDER . '/' . PLUGIN_FOLDER => '%extensions%', CORE_FOLDER => '%core%'));
	fwrite($file, $component . $flag . "\n");
}
