<?php

/*
 * debug tool for Ewound Horman
 *
 *
 * @author Stephen Billard (sbillard)
 *
 * @Copyright 2014-2018 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics} and derivatives
 *
 * @pluginCategory development
 */
$plugin_is_filter = 1 | CLASS_PLUGIN;
if (defined('SETUP_PLUGIN')) { //	gettext debugging aid
	$plugin_description = gettext("Search debug tool.");
}

class searchDebug {

	static function filter($rslt, $obj) {
		global $_searchInstance;
		if ($_searchInstance) {
			if ($rslt) {
				debugLog('returning ' . $obj->table . ': ' . $obj, false, 'search');
			} else {
				$bt = debug_backtrace();
				debugLog($bt[2]['function'] . ' loop completed', false, 'search');
			}
		}
		return $rslt;
	}

	static function stats($searchstring, $which, $success, $dynalbumname, $iteration) {
		global $_searchInstance, $_current_search;
		if (empty($_searchInstance)) {
			if (empty($_current_search)) {
				return $searchstring;
			}
			$_searchInstance = $_current_search;
			debugLog('Search for: ' . $_searchInstance->getSearchWords(), false, 'search');
		}

		if ($success === 'cache') {
			$what = ' served from Cache';
		} else if ($success) {
			$what = ' found in Database';
		} else {
			$what = ' not found';
		}
		debuglog($which . $what, false, 'search');
		return $searchstring;
	}

}

npgFilters::register('next_object_loop', 'searchDebug::filter');
npgFilters::register('search_statistics', 'searchDebug::stats');
?>