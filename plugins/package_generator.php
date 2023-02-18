<?php

/** A tool to produce the package file--a list of all the files in
 * the distribution.  Permission granted for use in conjunction with netPhotoGraphics. All other rights reserved

 *
 * The script will use the local GitHub repository (the option <em>Repository Path</em>) to define the contents of the
 * THEMES and PLUGINS folders. To add or remove themes and plugins update
 * the repository so that it reflects the desired contents.
 *
 * The CORE folder content is derived from the the installation the
 * plugin is executed from.
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins/package
 * @pluginCategory tools
 *
 * @Copyright 2014-2023 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics} and derivatives
 */
// force UTF-8 Ø

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Generates the <em>netPhotoGraphics.package</em> file.');

$option_interface = 'package';

npgFilters::register('admin_utilities_buttons', 'package::buttons');

class package {

	function __construct() {

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
