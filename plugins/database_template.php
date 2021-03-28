<?php

/* Creates the database template file
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/database_template
 * @pluginCategory tools
 *
 * @Copyright Stephen L Billard
 * permission granted for use in conjunction with netPhotoGraphics. All other rights reserved
 */
// force UTF-8 Ø

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Generates the <em>databaseTemplate</em> file.');

npgFilters::register('admin_utilities_buttons', 'database_structure_button');

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
			'rights' => ADMIN_RIGHTS
	);
	return $buttons;
}

?>