<?php

/* Creates the zenphoto.package file
 *
 * @author Stephen Billard (sbillard)
<<<<<<< HEAD
 * 
=======
 *
>>>>>>> 256cc84562f6bb694b2972dcf89ed7bad4471f18
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

zp_register_filter('admin_utilities_buttons', 'zenphoto_package_button');

class zenphoto_package {

	function __construct() {
		setOptionDefault('zenphoto_package_path', ZENFOLDER);
	}

	function getOptionsSupported() {
		return array(gettext('Folder') => array('key'				 => 'zenphoto_package_path', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array(DATA_FOLDER => DATA_FOLDER, ZENFOLDER => ZENFOLDER, UPLOAD_FOLDER => UPLOAD_FOLDER),
										'desc'			 => gettext('Place the package file in this folder.')));
	}

}

function zenphoto_package_button($buttons) {
	$buttons[] = array(
					'category'		 => gettext('Development'),
					'enable'			 => true,
					'button_text'	 => gettext('Create package'),
					'formname'		 => 'zenphoto_package_button',
					'action'			 => FULLWEBPATH . '/plugins/zenphoto_package/zenphoto_package_generator.php',
					'icon'				 => 'images/arrow_down.png',
					'title'				 => gettext('Download new package file'),
					'alt'					 => '',
					'hidden'			 => '',
					'rights'			 => ADMIN_RIGHTS,
	);
	return $buttons;
}

?>