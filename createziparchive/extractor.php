<?php
/*
 * 	This script is a derivative of work produced by createziparchive (c) 2008 iljitsch@mail.com cookiepattern.blogspot.com
 *
 * 	The derivative work is copyright (c) 2020 by Stephen Billard, all rights reserved
 * 	This copyright notice must be included in all copies of this script.
 */
Define('PHP_MIN_VERSION', 'd.d');
if (version_compare(PHP_VERSION, PHP_MIN_VERSION, '<')) {
	die(sprintf(gettext('netPhotoGraphics requires PHP version %s or greater'), PHP_MIN_VERSION));
}
if (!class_exists('ZipArchive')) {
	die('The extraction process requires the PHP ZipArchive class.');
}
@ini_set('memory_limit', '-1');
$me = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
echo "<h1>Extracting netPhotoGraphics _VERSION_ files</h1>";

if (!isset($_GET['process'])) {
	echo '<meta http-equiv="refresh" content="1; url=' . $me . '?process" />';
	exit();
}
if (!(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
	$protocol = "https";
} else if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == "https") {
	$protocol = "https";
} else if (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
	$protocol = "https";
} else {
	$protocol = "http";
}
$const_webpath = $protocol . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($me), '/\\');

try {
	$zipfilename = md5(time()) . '.extract.zip'; //remove with tempname()
	if (!$fp_tmp = fopen($zipfilename, 'w')) {
		die('Unable to open ' . $zipfilename . ' for writing. Check your file permissions.');
	}
	if (!$fp_cur = fopen(__FILE__, 'r')) {
		die('Unable to open ' . __FILE__ . '. Check your file permissions.');
	}
	if (fseek($fp_cur, __COMPILER_HALT_OFFSET__) < 0) {
		die('Something went wrong, could not seek to "__HALT_COMPILER()" statement.');
	}
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
	<a href="<?php echo $const_webpath . '/npgCore/setup/index.php?autorun=admin'; ?>">run setup</a>

	<script>
		// <!-- <![CDATA[
		window.onload = function () {
			window.location = '<?php echo $const_webpath; ?>/npgCore/setup/index.php?autorun=admin';
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