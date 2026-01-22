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

define('OFFSET_PATH', 3);
require_once(file_get_contents(dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/core-locator.npg') . "admin-globals.php");

$database_name = db_name();
define('FIELD_COMMENT', 'npg');
$prefix = trim(prefix(), '`');
$resource = db_show('tables');
if ($resource) {
	$result = array();
	while ($row = db_fetch_assoc($resource)) {
		$result[] = $row;
	}
	db_free_result($resource);
} else {
	$result = false;
}
$tables = array();
if (is_array($result)) {
	foreach ($result as $row) {
		$tables[] = reset($row);
	}
}
$database = array();
$i = 0;
foreach ($tables as $table) {
	$table = substr($table, strlen($prefix));

	$tablecols = db_list_fields($table);
	foreach ($tablecols as $key => $datum) {
		if (strpos($datum['Comment'], 'optional_') === false) { // leave these for setup time decisions
			// add comment for our fields so we can recognize them later
			if (!$datum['Comment']) {
				$datum['Comment'] = FIELD_COMMENT;
			}
			if (strpos(strtolower($datum['Type']), 'int') !== false) {
				$datum['Type'] = preg_replace('`\(\d*\)`', '', $datum['Type']);
			}
			if (isset($datum['Collation']) && $datum['Collation'] === 'utf8_unicode_ci') {
				$datum['Collation'] = 'utf8mb3_unicode_ci';
			}
			// remove don't care fields
			unset($datum['Key']);
			unset($datum['Extra']);
			unset($datum['Privileges']);
			$database[$table]['fields'][$datum['Field']] = $datum;
		}
	}

	$indices = array();
	$sql = 'SHOW KEYS FROM ' . prefix($table);
	$result = query_full_array($sql);
	foreach ($result as $index) {
		if ($index['Key_name'] !== 'PRIMARY') {
			$index['Index_comment'] = FIELD_COMMENT; //flag as one of ours
			$indices[$index['Key_name']][] = $index;
		}
	}

	foreach ($indices as $keyname => $index) {
		if (count($index) > 1) {
			$size = $column = array();
			foreach ($index as $element) {
				$column[] = "`" . $element['Column_name'] . "`";
				$size[] = $element['Sub_part'];
			}
			$index = reset($index);
			$index['Column_name'] = implode(',', $column);
			$index['Size'] = implode(',', $size);
		} else {
			$index = reset($index);
			$index['Column_name'] = "`" . $index['Column_name'] . "`";
		}
		unset($index['Table']);
		unset($index['Seq_in_index']);
		unset($index['Cardinality']);
		unset($index['Comment']);
		unset($index['Visible']);
		unset($index['Expression']);
		$database[$table]['keys'][$keyname] = $index;
	}
}

$template = getSerializedArray(file_get_contents(CORE_SERVERPATH . 'databaseTemplate'));
$dropped = $renamed = array();

foreach ($template as $table => $row) {
	$old = array_keys($template[$table]['fields']);
	$new = array_keys($database[$table]['fields']);
	if ($old != $new) {
		$dif_old = array_diff($old, $new);
		$dif_new = array_diff($new, $old);
		foreach ($dif_old as $key => $field) {
			if (isset($dif_new[$key])) {
				$renamed[] = "array('table' => '$table', 'was' => '$field', 'is' => '$dif_new[$key]'),";
				unset($dif_old[$key]);
				unset($dif_new[$key]);
			}
		}

		if (!empty($dif_old)) {
			foreach ($dif_old as $field) {
				$dropped[] = 'dropped ' . $table . ':' . $field;
			}
		}
		if (!empty($dif_new)) {
			foreach ($dif_new as $field) {
				$dropped[] = 'added ' . $table . ':' . $field;
			}
		}
	}
}

file_put_contents(CORE_SERVERPATH . 'databaseTemplate', serialize($database));

$more = '';
if (!empty($renamed)) {
	$setupdb = file_get_contents(CORE_SERVERPATH . 'setup/database.php');
	$setupdb = str_replace("\$renames = array(\n", "\$renames = array(\n\t\t" . implode("\n\t\t", $renamed) . "\n", $setupdb);
	file_put_contents(CORE_SERVERPATH . 'setup/database.php', $setupdb);
	$more = '&more=database_template';
	array_unshift($renamed, gettext('Possible field name changes detected.'));
	array_unshift($renamed, '');
}
if (!empty($dropped)) {
	$more = '&more=database_template';
	array_unshift($dropped, '');
}
$_SESSION['database_template'] = array_merge($renamed, $dropped);
header('Location: ' . getAdminLink('admin.php') . '?action=external&msg=' . gettext("Database template created") . $more);
exit();
?>