<?php

// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

class ThemeOptions {

	function __construct() {
		$me = basename(__DIR__);
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('thumb_transition', true);
		setThemeOption('thumb_size', 79, NULL);
		setThemeOptionDefault('thumb_crop_width', 79);
		setThemeOptionDefault('thumb_crop_height', 79);
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOptionDefault('images_per_page', 24);
		setThemeOptionDefault('images_per_row', 6);
		setThemeOptionDefault('albums_per_page', 6);

		if (class_exists('cacheManager')) {
			$me = basename(__DIR__);
			cacheManager::deleteCacheSizes($me);
			cacheManager::addCacheSize($me, NULL, 79, 79, 79, 79, NULL, NULL, true, NULL, NULL, NULL);
		}
		if (function_exists('menuExists') && !menuExists('netPhotoGraphics')) {
			$menuset = array(
					array('title' => 'links', 'link' => '379e75c850e1334ef7bece52694c2f26cebec78f', 'include_li' => '1'
							,
							'type' => 'menulabel', 'nesting' => '0', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'Forum', 'link' => 'https://netphotographics.org/forum/', 'include_li' => '1'
							,
							'type' => 'customlink', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'Demo', 'link' => 'demo/', 'include_li' => '1'
							,
							'type' => 'customlink', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'Admin demo', 'link' => 'openAdmin/admin', 'include_li' => '1'
							,
							'type' => 'customlink', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'screenshots', 'link' => 'screenshots', 'include_li' => '1'
							,
							'type' => 'album', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'User guide', 'link' => './docs/user guide.pdf', 'include_li' => '1'
							,
							'type' => 'customlink', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => '', 'link' => 'troubleshooting-guide.htm', 'include_li' => '1'
							,
							'type' => 'page', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'Releases', 'link' => 'printNPGgitHubLink(\'releases\',\'Releases\');', 'include_li' => '1'
							,
							'type' => 'menufunction', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => '', 'link' => 'release-notes.htm', 'include_li' => '1'
							,
							'type' => 'page', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'Change List', 'link' => 'printNPGgitHubLink(\'commits/master\',\'Change list\');', 'include_li' => '1'
							,
							'type' => 'menufunction', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'Repository', 'link' => 'printNPGgitHubLink(\'\',\'Repository\');', 'include_li' => '1'
							,
							'type' => 'menufunction', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => 'Development', 'link' => 'https://github.com/sbillard/netPhotoGraphics-DEV', 'include_li' => '1'
							,
							'type' => 'customlink', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => ''),
					array('title' => '', 'link' => 'contact-us.htm', 'include_li' => '1'
							,
							'type' => 'page', 'nesting' => '1', 'show' => '1', 'span_class' => '', 'span_id' => '')
			);
			createMenu($menuset, 'netPhotoGraphics');
		}
	}

	function getOptionsSupported() {
		return array(
				gettext('Allow search') => array(
						'key' => 'Allow_search',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to enable search form.')
				),
				gettext('Allow direct link from multimedia') => array(
						'key' => 'zpmobile_mediadirectlink',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to enable a direct link to multimedia items on the single image page in case the player is not supported by the device but the actual format is.')
				)
		);
	}

	function getOptionsDisabled() {
		return array('image_size', 'thumb_size');
	}

	function handleOption($option, $currentValue) {

	}

}

?>