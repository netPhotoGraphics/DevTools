<?php

/* Generates doc file for filters
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/filterDoc
 * @pluginCategory development
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Generates a Doc file for filters.');

npgFilters::register('admin_utilities_buttons', 'filterDoc_button');

function filterDoc_button($buttons) {
	if (isset($_REQUEST['filterDoc'])) {
		XSRFdefender('filterDoc');
		include (USER_PLUGIN_SERVERPATH . '/filterDocGen/process.php');
		processFilters();
	}
	$buttons[] = array(
			'category' => gettext('Development'),
			'enable' => true,
			'button_text' => gettext('Filter Doc Gen'),
			'formname' => 'filterDoc_button',
			'action' => '?filterDoc=gen',
			'icon' => PLUS_ICON,
			'title' => gettext('Generate filter document'),
			'alt' => '',
			'hidden' => '<input type="hidden" name="filterDoc" value="gen" />',
			'rights' => ADMIN_RIGHTS,
			'XSRFTag' => 'filterDoc'
	);
	return $buttons;
}

?>