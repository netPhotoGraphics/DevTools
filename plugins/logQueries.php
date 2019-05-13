<?php

/*
 * Use this plugin to log database queries. It is intended for performance analysis.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/logQueries
 * @pluginCategory development
 *
 * @Copyright 2019 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics and derivatives}
 */

// force UTF-8 Ø

$plugin_is_filter = 1000 | FEATURE_PLUGIN;
$plugin_description = gettext("Log database queries.");

zp_register_filter('database_query', 'logQueries::query', 9999);

class logQueries {

	static function query($result, $sql) {
		$bt = debug_backtrace();
		array_shift($bt); //	This function
		array_shift($bt); //	The filter processing
		array_shift($bt); //	functions-vasic:query()

		$output = trim($sql) . NEWLINE;
		$prefix = '  ';
		$line = '';
		$caller = '';
		foreach ($bt as $b) {
			$caller = (isset($b['class']) ? $b['class'] : '') . (isset($b['type']) ? $b['type'] : '') . $b['function'];
			if (!empty($line)) { // skip first output to match up functions with line where they are used.
				$prefix .= '  ';
				$output .= 'from ' . $caller . ' (' . $line . ")\n" . $prefix;
			} else {
				$output .= '  ' . $caller . " called ";
			}
			$date = false;
			if (isset($b['file']) && isset($b['line'])) {
				$line = basename($b['file']) . ' [' . $b['line'] . "]";
			} else {
				$line = 'unknown';
			}
		}
		if (!empty($line)) {
			$output .= 'from ' . $line;
		}
		debugLog($output, false, 'debug');
		return NULL;
	}

}
