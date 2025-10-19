<?php
// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

require_once(__DIR__ . '/functions.php');

class ThemeOptions {

	function __construct($setDefaultOptions) {

		$me = basename(__DIR__);
		setThemeOptionDefault('Theme_logo', 'A multi-media oriented Content Management System');
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('Slideshow', true);
		setThemeOptionDefault('Graphic_logo', 'admin-logo');
		setThemeOptionDefault('Watermark_head_image', false);
		setThemeOptionDefault('netPhotoGraphics_personality', 'image_page');
		setThemeOptionDefault('netPhotoGraphics_transition', 'slide-hori');
		setThemeOptionDefault('netPhotoGraphics_caption_location', 'image');
		setThemeOptionDefault('Theme_colors', 'netPhotoGraphics');
		setThemeOptionDefault('netPhotoGraphics_menu', 'netPhotoGraphics');
		setThemeOptionDefault('images_per_page', 20);
		setThemeOptionDefault('images_per_row', 5);
		setThemeOptionDefault('albums_per_page', 9);
		setThemeOption('image_size', 595, NULL);
		setThemeOption('image_use_side', 'longest', NULL);
		setThemeOptionDefault('thumb_transition', true);
		setThemeOptionDefault('thumb_size', 90);
		setThemeOptionDefault('thumb_crop_width', 90);
		setThemeOptionDefault('thumb_crop_height', 90);
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOptionDefault('gallery_index', 1);
		setThemeOptionDefault('netPhotoGraphics_daily_album_image', 1);
		setThemeOptionDefault('netPhotoGraphics_daily_album_image_effect', '');
		setThemeOptionDefault('display_theme_info', 0);
		if (class_exists('cacheManager')) {
			cacheManager::deleteCacheSizes($me);
			cacheManager::addCacheSize($me, 595, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL);
			cacheManager::addCacheSize($me, getThemeOption('thumb_size'), NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL);
			cacheManager::addCacheSize($me, NULL, 180, 80, NUll, NULL, NULL, NULL, true, NULL, NULL, NULL);
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
		global $personalities;

		$note = '<p class="notebox">' . gettext('<strong>Note:</strong> This option is valid only if the CMS plugin is enabled or the Separate gallery index option is checked. Of course the <em>menu_manager</em> plugin must also be enabled.') . '</p>';

		if (!extensionEnabled('print_album_menu') && (($m = getOption('netPhotoGraphics_menu')) == 'effervescence' || $m == 'zenpage' || $m == 'garland')) {
			$note .= '<p class="notebox">' . sprintf(gettext('<strong>Note:</strong> The <em>%s</em> custom menu makes use of the <em>print_album_menu</em> plugin.'), $m) . '</p>';
		}
		$options = array(
				gettext('Separate gallery index') => array(
						'key' => 'gallery_index',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 1,
						'desc' => gettext('Check to move the gallery index from the home page to gallery.php.') . '<p class="notebox">' . gettext('<strong>Note:</strong> this is assumed the CMS plugin is enabled.') . '</p>'),
				gettext('Theme logo') => array(
						'key' => 'Theme_logo',
						'type' => OPTION_TYPE_TEXTBOX,
						'multilingual' => 1,
						'order' => 8,
						'desc' => gettext('The text for the theme logo')
				),
				gettext('Watermark head image') => array(
						'key' => 'Watermark_head_image',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 11,
						'desc' => gettext('Check to place a watermark on the heading image. (Image watermarking must be set.)')
				),
				gettext('Daily image') => array(
						'key' => 'netPhotoGraphics_daily_album_image',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3,
						'desc' => gettext('If checked the heading image will change daily rather than on each page load.')
				),
				gettext('Allow search') => array(
						'key' => 'Allow_search',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3.5,
						'desc' => gettext('Check to enable search form.')
				),
				gettext('Slideshow') => array(
						'key' => 'Slideshow',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 6,
						'desc' => gettext('Check to enable slideshow for the <em>Smoothgallery</em> personality.')
				),
				gettext('Graphic logo') => array(
						'key' => 'Graphic_logo',
						'type' => OPTION_TYPE_CUSTOM,
						'order' => 4,
						'desc' => sprintf(gettext('Select a logo (PNG files in the <em>%s/images</em> folder) or leave empty for text logo.'), UPLOAD_FOLDER)
				),
				gettext('Display theme info') => array(
						'key' => 'display_theme_info',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 10.9,
						'desc' => gettext('If checked the theme settings summary will be displayed in the footer.')
				),
				gettext('Theme personality') => array(
						'key' => 'netPhotoGraphics_personality',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => $personalities,
						'order' => 9,
						'desc' => gettext('Select the theme personality')
				),
				gettext('Theme colors') => array(
						'key' => 'Theme_colors',
						'type' => OPTION_TYPE_CUSTOM,
						'order' => 7,
						'desc' => gettext('Select the colors of the theme')
				),
				gettext('Custom menu') => array(
						'key' => 'netPhotoGraphics_menu',
						'type' => OPTION_TYPE_CUSTOM,
						'order' => 2,
						'desc' => gettext('Set this to the <em>menu_manager</em> menu you wish to use.') . $note)
		);

		if (getOption('netPhotoGraphics_personality') == 'Image_gallery') {
			$options[gettext('Image gallery transition')] = array(
					'key' => 'netPhotoGraphics_transition',
					'type' => OPTION_TYPE_SELECTOR,
					'selections' => array(gettext('None') => '', gettext('Fade') => 'fade', gettext('Shrink/grow') => 'resize', gettext('Horizontal') => 'slide-hori', gettext('Vertical') => 'slide-vert'),
					'order' => 10,
					'desc' => gettext('Transition effect for Image gallery')
			);
			$options[gettext('Image gallery caption')] = array(
					'key' => 'netPhotoGraphics_caption_location',
					'type' => OPTION_TYPE_RADIO,
					'buttons' => array(gettext('On image') => 'image', gettext('Separate') => 'separate', gettext('Omit') => 'none'),
					'order' => 10.5,
					'desc' => gettext('Location for Image gallery picture caption')
			);
		}

		if (class_exists('image_effects')) {
			$effects = new image_effects();
			$effectOptions = $effects->getOptionsSupported();
			$effect = array_shift($effectOptions);
			while ($effect && !array_key_exists('selections', $effect)) {
				$effect = array_shift($effectOptions);
			}
			if ($effect && array_key_exists('selections', $effect)) {
				$options[gettext('Index Image')] = array(
						'key' => 'netPhotoGraphics_daily_album_image_effect',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => $effect['selections'],
						'null_selection' => gettext('none'),
						'order' => 5,
						'desc' => gettext('Apply this effect to the index page image.')
				);
			}
		}
		return $options;
	}

	function getOptionsDisabled() {
		return array('image_size');
	}

	function handleOption($option, $currentValue) {
		global $themecolors;
		switch ($option) {
			case 'Theme_colors':
				echo '<select id="EF_themeselect_colors" name="' . $option . '"' . ">\n";
				generateListFromArray(array($currentValue), $themecolors, false, false);
				echo "</select>\n";
				break;
			case 'netPhotoGraphics_menu':
				$menusets = getMenuSets();
				echo '<select id="EF_menuset" name="netPhotoGraphics_menu"';
				if (empty($menusets)) {
					echo ' disabled="disabled"';
				}
				echo ">\n";
				echo '<option value="" style="background-color:LightGray">' . gettext('*standard menu') . '</option>';
				generateListFromArray(array($currentValue), $menusets, false, false);
				echo "</select>\n";
				break;
			case 'Graphic_logo':
				?>
				<select id="EF_themeselect_logo" name="Graphic_logo">
					<option value="" style="background-color:LightGray"><?php echo gettext('*no logo selected'); ?></option>';
					<option value="*"<?php if ($currentValue == '*') echo ' selected="selected"'; ?>><?php echo gettext('Effervescence'); ?></option>';
					<?php generateListFromFiles($currentValue, SERVERPATH . '/' . UPLOAD_FOLDER . '/images', '.png'); ?>
				</select>
				<?php
				break;
		}
	}

}
?>