<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2008 iljitsch@mail.com cookiepattern.blogspot.com
 *  All rights reserved
 *
 *  This script is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/*
 * 	Adapted for netPhotoGraphics by Stephen Billard
 * 	Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/netPhotoGraphics ZenPhoto20}
 * 	This copyright notice MUST APPEAR in all copies of the script!
 */
@ini_set('memory_limit', '-1');
Define('TARGET', 'package/');
if (isset($_GET['source']) && $_GET['source']) {
	define('VARIENT', $_GET['source']);
} else {
	define('VARIENT', 'master');
}

echo '<h1>Creating ' . VARIENT . ' extract zip file</h1>';
$me = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
if (!isset($_GET['process'])) {
	echo '<meta http-equiv="refresh" content="1; url=' . $me . '?process&source=' . VARIENT . '" />';
	exit();
}

try {
	switch (VARIENT) {
		case 'DEV':
			$sourcefolder = '/test_sites/dev/';
			break;
		case 'master':
			$sourcefolder = '/Downloads/netPhotoGraphics' . '-' . VARIENT . '/';
			break;
		case 'GIT':
			$sourcefolder = '/github/netPhotoGraphics-DEV/';
			break;
	}

	if (file_exists($sourcefolder . 'zp-core/version.php')) {
		$core = 'zp-core';
	} else {
		$core = 'npgCore';
	}
	require_once($sourcefolder . $core . '/version.php');
	if (defined('NETPHOTOGRAPHICS_VERSION')) {
		$version = NETPHOTOGRAPHICS_VERSION;
	} else {
		$version = ZENPHOTO_VERSION;
	}
	$version = explode('-', $version);
	define('VERSION', $version[0]);
	$targetname = TARGET . 'extract.php.bin';
	$zipfilename = md5(time()) . 'extract.zip'; // replace with tempname()
	if (file_exists(TARGET . 'setup-' . VARIENT . '-' . VERSION . '.zip'))
		unlink(TARGET . 'setup-' . VARIENT . '-' . VERSION . '.zip');

	// create a archive from the submitted folder
	$zipfile = new ZipArchive();
	$zipfile->open($zipfilename, ZipArchive::CREATE);
	addFiles2Zip($zipfile, $sourcefolder . $core . '/', $sourcefolder);
	addFiles2Zip($zipfile, $sourcefolder . 'themes/', $sourcefolder);
	addFiles2Zip($zipfile, $sourcefolder . 'plugins/', $sourcefolder);
	$zipfile->addFile($sourcefolder . '/docs/release notes.htm', 'docs/release notes.htm');
	$zipfile->addFile($sourcefolder . '/docs/filterDoc.htm', 'docs/filterDoc.htm');
	$zipfile->addFile($sourcefolder . '/docs/user guide.pdf', 'docs/user guide.pdf');
	$zipfile->addEmptyDir('albums');
	$zipfile->addEmptyDir('uploaded');

	$zipfile->close();

	// compile the selfextracting php-archive
	$fp_dest = fopen($targetname, 'w');
	$fp_cur = fopen(__FILE__, 'r');
	fseek($fp_cur, __COMPILER_HALT_OFFSET__);
	$i = 0;
	while ($buffer = fgets($fp_cur)) {
		$buffer = str_replace('_VERSION_', VERSION, $buffer);
		$buffer = str_replace('_CORE_', $core, $buffer);
		fwrite($fp_dest, $buffer);
	}
	fclose($fp_cur);

	$fp_zip = fopen($zipfilename, 'r');
	while ($buffer = fread($fp_zip, 10240)) {
		fwrite($fp_dest, $buffer);
	}
	fclose($fp_zip);
	fclose($fp_dest);
	unlink($zipfilename);

	$zipfile = new ZipArchive();
	$zipfile->open(TARGET . 'setup-' . VARIENT . '-' . VERSION . '.zip', ZipArchive::CREATE);
	$zipfile->addFile('readme.txt', 'readme.txt');
	$zipfile->addFile($sourcefolder . '/docs/release notes.htm', 'release notes.htm');
	$zipfile->addFile($targetname, 'extract.php.bin');
	$zipfile->close();

	echo 'setup-' . VARIENT . '-' . VERSION . '.zip created';
} catch (Exception $e) {
	printf("Error:<br/>%s<br>%s>", $e->getMessage(), $e->getTraceAsString());
}

function getSuffix($filename) {
	return strtolower(substr(strrchr($filename, "."), 1));
}

function addFiles2Zip(ZipArchive $zip, $path, $removeFolder = false) {
	$d = opendir($path);
	while ($file = readdir($d)) {
		set_time_limit(360);
		if ($file[0] == "." || $file == 'Thumbs.db' || ($sfx = getSuffix($file)) == 'md' || $sfx == 'bat')
			continue;
		$curfile = ($removeFolder) ? preg_replace('~^' . $removeFolder . '~', '', $path . $file) : $path . $file;
		if (is_dir($path . $file)) {
			$zip->addEmptyDir($curfile);
			addFiles2Zip($zip, $path . $file . '/', $removeFolder);
		} else {
			$zip->addFile($path . $file, $curfile);
		}
	}
	closedir($d);
}

__HALT_COMPILER();<?php
/*
* 	This script is a derivitive of work produced by createziparchive (c) 2008 iljitsch@mail.com cookiepattern.blogspot.com
*
* 	The derivitive work is copyright (c) 2014 by Stephen Billard, all rights reserved
* 	This copyright notice must be included in all copies of this script.
*/
Define('PHP_MIN_VERSION', '5.2');
if (version_compare(PHP_VERSION, PHP_MIN_VERSION, '<')) {
die(sprintf(gettext('netPhotoGraphics requires PHP version %s or greater'), PHP_MIN_VERSION));
}

@ini_set('memory_limit', '-1');
$me = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
echo "<h1>Extracting netPhotoGraphics _VERSION_ files</h1>";

if (!isset($_GET['process'])) {
echo '<meta http-equiv="refresh" content="1; url=' . $me . '?process" />';
exit();
}
$const_webpath = "http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($me), '/\\');

try {
$zipfilename = md5(time()) . '.extract.zip'; //remove with tempname()
$fp_tmp = fopen($zipfilename, 'w');
$fp_cur = fopen(__FILE__, 'r');
fseek($fp_cur, __COMPILER_HALT_OFFSET__);
$i = 0;
while ($buffer = fread($fp_cur, 10240)) {
fwrite($fp_tmp, $buffer);
}
fclose($fp_cur);
fclose($fp_tmp);
$zipfile = new ZipArchive();
if (($result = $zipfile->open($zipfilename)) === true) {
set_time_limit(360);
if (!$zipfile->extractTo('.')) {
$error = error_get_last();
throw new Exception($error['message'], 0);
}
} else {
switch ($result) {
case ZipArchive::ER_INCONS:
$msg = 'Inconsistent archive';
break;
case ZipArchive::ER_MEMORY:
$msg = 'Insufficient memory';
break;
case ZipArchive::ER_NOENT:
$msg = 'File not found';
break;
case ZipArchive::ER_NOZIP:
$msg = 'Not a zip archive';
break;
case ZipArchive::ER_OPEN:
$msg = "Can't open file";
break;
case ZipArchive::ER_READ:
$msg = 'Read error';
break;
case ZipArchive::ER_SEEK:
$msg = 'Seek error';
break;
default:
$msg = 'Error ' . $result;
break;
}
throw new Exception('reading archive failed: ' . $msg, 1);
}
$zipfile->close();
unlink($zipfilename);
unlink(__FILE__);
?>
done...
<br />
<a href="<?php echo $const_webpath . '/_CORE_/setup/index.php?autorun=admin'; ?>">run setup</a>

<script>
	// <!-- <![CDATA[
	window.onload = function () {
		window.location = '<?php echo $const_webpath; ?>/_CORE_/setup/index.php?autorun=admin';
	}
	// ]]> -->
</script>
<?php
} catch (Exception $e) {
$zipfile->close();
@unlink($zipfilename);
echo "Error:<br />";
echo $e->getMessage() . "<br />";
if ($e->getCode()) {
echo $e->getTraceAsString();
} else {
echo "Try removing the old installation files.";
}
}
__HALT_COMPILER();