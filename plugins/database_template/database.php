<?php

/* Creates the database template file
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/database_template
 * @category developerTools
 *
 * Copyright Stephen L Billard
 * permission granted for use in conjunction with ZenPhotoGraphics. All other rights reserved
 */
// force UTF-8 Ø

define("OFFSET_PATH", 3);
require_once(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . "/zp-core/admin-globals.php");


$database_name = db_name();
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
		$tables[] = array_shift($row);
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
				$datum['Comment'] = 'zp20';
			}
			// remove don't care fields
			unset($datum['Collation']);
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
			$index['Index_comment'] = 'zp20'; //flag as one of ours
			$indices[$index['Key_name']][] = $index;
		}
	}
	foreach ($indices as $keyname => $index) {
		if (count($index) > 1) {
			$column = array();
			foreach ($index as $element) {
				$column[] = "`" . $element['Column_name'] . "`";
			}
			$index = array_shift($index);
			$index['Column_name'] = implode(',', $column);
		} else {
			$index = array_shift($index);
			$index['Column_name'] = "`" . $index['Column_name'] . "`";
		}
		unset($index['Table']);
		unset($index['Seq_in_index']);
		unset($index['Collation']);
		unset($index['Cardinality']);
		unset($index['Comment']);

		$database[$table]['keys'][$keyname] = $index;
	}
}

file_put_contents(SERVERPATH . '/' . ZENFOLDER . '/databaseTemplate', serialize($database));

header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg=' . gettext("Database template created"));
exitZP();
?>