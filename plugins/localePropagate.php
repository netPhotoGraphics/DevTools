<?php

/**
 * This plugin will copy the contents of the %USER_PLUGIN_FOLDER%/localePropegate folder to
 * the %CORE_FOLDER%/locale folder. It is intended to aid using locally developed
 * translations by preserving them over runs of <i>setup</i>.
 *
 * The plugin will perform this copy when <i>setup</i> is run IF it is enabled. If it is
 * disabled, it will remove any of these language files from the main installation.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Propegate language files.');
$plugin_author = "Stephen Billard (sbillard)";

if (OFFSET_PATH == 2) {

	chdir(USER_PLUGIN_SERVERPATH . '/localePropagate/');
	$folders = safe_glob('*', GLOB_ONLYDIR);

	foreach ($folders as $folder) {
		deleteDirectory(CORE_SERVERPATH . 'locale/' . $folder);
	}

	if (extensionEnabled('localePropagate')) {
		foreach ($folders as $folder) {
			copyDirectory($folder, CORE_SERVERPATH . 'locale/' . basename($folder));
		}
	}
}
?>
