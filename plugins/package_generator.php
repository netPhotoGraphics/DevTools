<?php

/** A tool to produce the package file--a list of all the files in
 * the distribution.
 *
 * To add new user plugins or themes to the distribution package edit the package
 * file adding a line for each plugin/theme:
 *
 * themes/<i>themename/theme_description.php</i> for a new theme
 *
 * plugins/<i>pluginname</i>.php for a new plugin
 *
 * Then run the package generator.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins/package
 * @pluginCategory tools
 *
 * @Copyright Stephen L Billard
 * permission granted for use in conjunction with netPhotoGraphics. All other rights reserved
 */
// force UTF-8 Ø

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Generates the <em>netPhotoGraphics.package</em> file.');

$option_interface = 'package';

npgFilters::register('admin_utilities_buttons', 'package::buttons');

class package {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('package_path', CORE_FOLDER);
		}
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Repository Path') => array(
						'key' => 'package_git_path',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('Enter the full path to the the local <em>netPhotoGraphics</em> repository.')
				)
		);
		return $options;
	}

	static function buttons($buttons) {
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Extract getAllTranslations'),
				'formname' => 'translations_button',
				'action' => FULLWEBPATH . '/plugins/package_generator/getAllTranslations.php',
				'icon' => ARROW_DOWN_GREEN,
				'title' => gettext('Extract "allTranslations" strings'),
				'alt' => '',
				'rights' => ADMIN_RIGHTS
		);
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Create package'),
				'formname' => 'package_button',
				'action' => FULLWEBPATH . '/plugins/package_generator/package_generator.php',
				'icon' => ARROW_DOWN_GREEN,
				'title' => gettext('Download new package file'),
				'alt' => '',
				'rights' => ADMIN_RIGHTS
		);
		return $buttons;
	}

}
