<?php

/* Creates the zenphoto database template file
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/database_template

 * @pluginCategory development
 * @category ZenPhoto20Tools
 *
 * Copyright Stephen L Billard
 * permission granted for use in conjunction with ZenPhoto20. All other rights reserved
 */
// force UTF-8 Ø

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Generates the <em>databaseTemplate</em> file.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_utilities_buttons', 'database_structure_button');

function database_structure_button($buttons) {
	$buttons[] = array(
			'category' => gettext('Development'),
			'enable' => true,
			'button_text' => gettext('Database Template'),
			'formname' => 'database_template.php',
			'action' => FULLWEBPATH . '/plugins/database_template/database.php',
			'icon' => ARROW_DOWN_GREEN,
			'title' => gettext('Creates the database structure template.'),
			'alt' => '',
			'hidden' => '',
			'rights' => ADMIN_RIGHTS
	);
	return $buttons;
}

?>