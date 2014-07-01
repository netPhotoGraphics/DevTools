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
 
 //Adapted for ZenPhoto20 by Stephen Billard
 
echo '<h1>Creating setup zip file</h1>';
$me = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
if (!isset($_GET['process'])) {
	echo '<meta http-equiv="refresh" content="1; url=' . $me . '?process" />';
	exit();
}
Define('TARGET','package/');
try {
	$sourcefolder = '/newstuff/master/'; // maybe you want to get this via CLI argument ...
	require_once($sourcefolder.'/zp-core/version.php');
	$targetname = TARGET . 'setup.php.bin';
	$zipfilename = md5(time()) . 'setup.zip'; // replace with tempname()
	// create a archive from the submitted folder
	$zipfile = new ZipArchive();
	$zipfile->open($zipfilename, ZipArchive::CREATE);
	addFiles2Zip($zipfile, $sourcefolder, $sourcefolder);
	$zipfile->close();

	// compile the selfextracting php-archive
	$fp_dest = fopen($targetname, 'w');
	$fp_cur = fopen(__FILE__, 'r');
	fseek($fp_cur, __COMPILER_HALT_OFFSET__);
	$i = 0;
	while ($buffer = fgets($fp_cur)) {
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
	
	$readme = TARGET . 'readme.txt';
	$text = sprintf("Installation instructions\r\n\r\n" .
									"Unzip this archive.\r\n\r\n" .
									'Upload the setup.php.bin file into the root folder of your website. (Note: the upload must be done in "binary" mode or the file may be corrupted. The ".bin" suffix should cause your FTP client to use this mode.)' . "\r\n\r\n" .
									'On your website rename setup.php.bin to setup.php' . "\r\n\r\n" .
									'Using your browser, visit "website"/setup.php (where "website" is the link to the root of your website.)' . "\r\n\r\n" .
									"The ZenPhoto20 files will self-extract and the setup process will start automatically.\r\n", ZENPHOTO_VERSION);
	file_put_contents($readme, $text);
	
	$zipfile = new ZipArchive();
	$zipfile->open(TARGET . 'setup-' . ZENPHOTO_VERSION . '.zip', ZipArchive::CREATE);
	$zipfile->addFile($readme, basename($readme));
	$zipfile->addFile($sourcefolder.'/docs/release notes.htm', 'release notes.htm');
	$zipfile->addFile($targetname, 'setup.php.bin');
	$zipfile->close();

	unlink($readme);
	unlink($targetname);

	echo 'Done ...';
} catch (Exception $e) {
	printf("Error:<br/>%s<br>%s>", $e->getMessage(), $e->getTraceAsString());
}

function getSuffix($filename) {
	return strtolower(substr(strrchr($filename, "."), 1));
}

function addFiles2Zip(ZipArchive $zip, $path, $removeFolder = false) {
	$d = opendir($path);
	while ($file = readdir($d)) {
	
		if ($file{0} == "." || $file == 'Thumbs.db' || getSuffix($file) == 'md')
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
$me = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
echo "<h1>Extracting ZenPhoto20 files</h1>";
	
if (!isset($_GET['process'])) {
	echo '<meta http-equiv="refresh" content="1; url=' . $me . '?process" />';
	exit();
}
$const_webpath = "http://" . $_SERVER['HTTP_HOST'] .  rtrim(dirname($me), '/\\');

try {
	$zipfilename = md5(time()) . 'setup.zip'; //remove with tempname()
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
		if (!$zipfile->extractTo('.'))
			throw new Exception('extraction failed...');
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
		throw new Exception('reading archive failed: ' . $msg);
	}
	$zipfile->close();
	unlink($zipfilename);
	unlink(__FILE__);
	
	?>
	done...
	<br />
	<a href="<?php echo $const_webpath . '/zp-core/setup/index.php?autorun=admin';?>">run setup</a>

	<script>
		// <!-- <![CDATA[
		window.onload = function() {
			window.location = '<?php echo $const_webpath; ?>/zp-core/setup/index.php?autorun=admin';
		}
		// ]]> -->
	</script>
	<?php 
	
} catch (Exception $e) {
	printf("Error:<br/>%s<br>%s>", $e->getMessage(), $e->getTraceAsString());
};
__HALT_COMPILER();