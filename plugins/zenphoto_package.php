<?php

/* Creates the zenphoto.package file
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 * @category ZenPhoto20Tools
 *
 * Copyright Stephen L Billard
 * permission granted for use in conjunction with ZenPhoto20. All other rights reserved
 */
// force UTF-8 Ø

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Generates the <em>zenphoto.package</em> file.');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'zenphoto_package';

zp_register_filter('admin_utilities_buttons', 'zenphoto_package::buttons');

class zenphoto_package {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('zenphoto_package_path', ZENFOLDER);
		}
	}

	function getOptionsSupported() {
		return array(gettext('Folder') => array('key' => 'zenphoto_package_path', 'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(DATA_FOLDER => DATA_FOLDER, ZENFOLDER => ZENFOLDER, UPLOAD_FOLDER => UPLOAD_FOLDER),
						'desc' => gettext('Place the package file in this folder.')));
	}

	static function buttons($buttons) {
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('extract getAllTranslations'),
				'formname' => 'zenphoto_translations_button',
				'action' => FULLWEBPATH . '/plugins/zenphoto_package/getAllTranslations.php',
				'icon' => ARROW_DOWN_GREEN,
				'title' => gettext('Extract "allTranslations" strings'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS
		);
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Create package'),
				'formname' => 'zenphoto_package_button',
				'action' => FULLWEBPATH . '/plugins/zenphoto_package/zenphoto_package_generator.php',
				'icon' => ARROW_DOWN_GREEN,
				'title' => gettext('Download new package file'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS
		);
		return $buttons;
	}

}

?>