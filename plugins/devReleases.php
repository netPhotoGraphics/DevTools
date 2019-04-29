<?php
/* Provides an install button for development releases
 *
 * This plugin is will place an install button pointing to the current Development
 * release (sbillard/netPhotoGraphics-DEV) repository. Clicking the button will download
 * and install this development build.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/devReleases
 * @pluginCategory development
 *
 * @Copyright 2017 by Stephen L Billard for use in https://%GITHUB%/netPhotoGraphics and derivitives
 *
 * permission granted for use in conjunction with netPhotoGraphics. All other rights reserved
 */

// force UTF-8 Ø

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Provides an install button from the development releases.');

if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
	require_once( SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/gitHubAPI/github-api.php');
}

use Milo\Github;

if (class_exists('Milo\Github\Api') && zpFunctions::hasPrimaryScripts()) {
	if (getOption('getDEVUpdates_lastCheck') + 8640 < time()) {
		setOption('getDEVUpdates_lastCheck', time());
		try {
			$api = new Github\Api;
			$fullRepoResponse = $api->get('/repos/:owner/:repo/releases/latest', array('owner' => 'sbillard', 'repo' => 'netPhotoGraphics-DEV'));
			$fullRepoData = $api->decode($fullRepoResponse);
			$assets = $fullRepoData->assets;

			if (!empty($assets)) {
				$item = array_pop($assets);
				setOption('getDEVUpdates_latest', $item->browser_download_url);
			}
		} catch (Exception $e) {
			debugLog(gettext('GitHub repository not accessible. ') . $e);
		}
	}
	$devVersionURI = getOption('getDEVUpdates_latest');
	$devVersion = preg_replace('~[^0-9,.]~', '', str_replace('setup-', '', stripSuffix(basename($devVersionURI))));
	$zenphoto_version = explode('-', ZENPHOTO_VERSION);
	$zenphoto_version = preg_replace('~[^0-9,.]~', '', array_shift($zenphoto_version));

	zp_register_filter('admin_utilities_buttons', 'devRelease::buttons');
	if (isset($_GET['update_check'])) {
		zp_register_filter('admin_note', 'devRelease::notice');
	}
}
if (isset($_GET['action'])) {
	if ($_GET['action'] == 'check_updates') {
		XSRFdefender('check_update');
		purgeOption('getDEVUpdates_lastCheck');
		purgeOption('getUpdates_lastCheck');
		header('location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?update_check');
		exit();
	}
	if ($_GET['action'] == 'install_dev') {
		if ($msg = getRemoteFile($devVersionURI, SERVERPATH)) {
			$class = 'errorbox';
		} else {
			$found = safe_glob(SERVERPATH . '/setup-*.zip');
			if (!empty($found)) {
				$file = array_shift($found);
				if (!unzip($file, SERVERPATH)) {
					$class = 'errorbox';
					$msg = gettext('netPhotoGraphics could not extract extract.php.bin from zip file.');
				} else {
					unlink(SERVERPATH . '/readme.txt');
					unlink(SERVERPATH . '/release notes.htm');
				}
			}
			if (file_exists(SERVERPATH . '/extract.php.bin')) {
				if (isset($file)) {
					unlink($file);
				}
				if (rename(SERVERPATH . '/extract.php.bin', SERVERPATH . '/extract.php')) {
					header('Location: ' . FULLWEBPATH . '/extract.php');
					exit();
				} else {
					$class = 'errorbox';
					$msg = gettext('Renaming the <code>extract.php.bin</code> file failed.');
				}
			} else {
				$class = 'errorbox';
				$msg = gettext('Did not find the <code>extract.php.bin</code> file.');
			}
		}
		if ($msg) {
			header('location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&error&msg=' . html_encode($msg));
			exit();
		}
	}
}

class devRelease {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('zenphoto_package_path', ZENFOLDER);
		}
	}

	static function buttons($buttons) {
		global $devVersion, $zenphoto_version, $newestVersion;
		$check = true;
		foreach ($buttons as $button) {
			if (isset($button['category']) && $button['category'] == gettext('Updates')) {
				$check = false;
				$zenphoto_version = $newestVersion;
				break;
			}
		}
		if (version_compare($devVersion, $zenphoto_version, '>')) {
			$buttons[] = array(
					'XSRFTag' => 'install_update',
					'category' => gettext('Updates'),
					'enable' => 2,
					'button_text' => sprintf(gettext('Install DEV %1$s'), $devVersion),
					'formname' => 'download_update',
					'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=install_dev',
					'icon' => INSTALL,
					'alt' => '',
					'title' => sprintf(gettext('Download and install netPhotoGraphics development version %1$s on your site.'), $devVersion),
					'hidden' => '<input type="hidden" name="action" value="install_dev" />',
					'rights' => ADMIN_RIGHTS
			);
		} else {
			if ($check) {
				$buttons[] = array(
						'XSRFTag' => 'check_update',
						'category' => gettext('Updates'),
						'enable' => 1,
						'button_text' => gettext('Check for updates'),
						'formname' => 'check_update',
						'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=check_updates',
						'icon' => CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN,
						'alt' => '',
						'title' => gettext('Check for newer versions of netPhotoGraphics.'),
						'hidden' => '<input type="hidden" name="action" value="check_updates" />',
						'rights' => ADMIN_RIGHTS
				);
			}
		}
		return $buttons;
	}

	static function notice() {
		global $devVersion, $zenphoto_version;
		if (isset($newestVersion) || version_compare($devVersion, $zenphoto_version, '>')) {
			$msg = gettext('There is an update available.');
		} else {
			$msg = gettext('You are running the latest netPhotoGraphics version.');
		}
		?>
		<div class="messagebox fade-message">
			<h2><?php echo $msg; ?></h2>
		</div>
		<?php
	}

}
?>