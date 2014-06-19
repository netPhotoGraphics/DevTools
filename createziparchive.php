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

echo '<h1>Creating setup zip file</h1>';
flush();

try {
	$sourcefolder = '/newstuff/ZenPhoto20-master/'; // maybe you want to get this via CLI argument ...
	$targetname = 'package/index.php';
	$zipfilename = md5(time()) . 'ZenPhoto20.zip'; // replace with tempname()
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

	echo 'Done ...';
} catch (Exception $e) {
	printf("Error:<br/>%s<br>%s>", $e->getMessage(), $e->getTraceAsString());
}

function addFiles2Zip(ZipArchive $zip, $path, $removeFolder = false) {
	$d = opendir($path);
	while ($file = readdir($d)) {
		if ($file == "." || $file == "..")
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
try {
$zipfilename = md5(time()).'ZenPhoto20.zip'; //remove with tempname()
$fp_tmp = fopen($zipfilename,'w');
$fp_cur = fopen(__FILE__, 'r');
fseek($fp_cur, __COMPILER_HALT_OFFSET__);
$i=0;
while($buffer = fread($fp_cur,10240)) {
fwrite($fp_tmp,$buffer);
}
fclose($fp_cur);
fclose($fp_tmp);
$zipfile = new ZipArchive();
if($zipfile->open($zipfilename)===true) {
if(!$zipfile->extractTo('.')) throw new Exception('extraction failed...');
} else throw new Exception('reading archive failed');
$zipfile->close();
unlink($zipfilename);

$const_webpath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
header('Location: ' . $const_webpath. '/zp-core/setup/index.php?autorun=admin');

} catch (Exception $e) {
printf("Error:<br/>%s<br>%s>",$e->getMessage(),$e->getTraceAsString());
};
__HALT_COMPILER();