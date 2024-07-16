<?php

/**
 * This plugin extracts release notes to an independently readable file
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/releaseNotes
 * @pluginCategory tools
 */
$plugin_is_filter = 5 | FEATURE_PLUGIN;
$plugin_description = gettext('Allows using netPhotoGraphics editing to create release notes.');

npgFilters::register('edit_cms_utilities', 'releaseNotesPublish');
npgFilters::register('save_article_data', 'releaseNotesExecute');

if (OFFSET_PATH == 2) {
	enableExtension('releaseNotes', $plugin_is_filter); //	at lease re-enable at setup incase it gets left disabled
}

$option_interface = 'releaseNotesOptions';

class releaseNotesOptions {

	function getOptionsSupported() {
		$list = [];
		list($plugin_subtabs, $plugin_default, $pluginlist, $plugin_paths, $plugin_member, $classXlate, $pluginDetails) = getPluginTabs();

		ksort($pluginDetails);
		foreach ($pluginDetails as $extension => $details) {
			if (isset($details['deprecated'])) {
				$list[] = $extension;
			}
		}

		$options = array(
				gettext('Deorecated plugins') => array('key' => 'releaseNotesOptions_note',
						'type' => OPTION_TYPE_CHECKBOX_ARRAY_UL,
						'checkboxes' => $list,
						'desc' => gettext('List of deprecated plugins. Check when noted in Release notes for the minor release.'))
		);
		return $options;
	}

}

function handleOption($option, $currentValue) {

}

function handleOptionSave($themename, $themealbum) {

}

function releaseNotesPublish($before, $object, $prefix = NULL) {
	$tl = $object->getTitleLink();
	if (RW_SUFFIX) {
		$tl = substr($tl, 0, strlen($tl) - strlen(RW_SUFFIX));
	}

	if ($tl == 'release-notes') {
		$output = '<p class="checkbox">' . "\n" . '<label>' . "\n" . '<input type="checkbox" name="publishNotes' . $prefix . '" id="publishNotes'
						. $prefix . '" value="1" checked="checked"/> '
						. gettext('Publish Release Notes')
						. "\n</label>\n</p>\n";
		return $before . $output;
	} else {
		return $before;
	}
}

function releaseNotesExecute($object) {
	if (isset($_POST['publishNotes'])) {
		$f = fopen(SERVERPATH . '/docs/release_notes.htm', 'w');
		$h = '<!DOCTYPE html>
<html lang="en">
	<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" lang="en">
	<title>netPhotoGraphics release notes</title>
	<style type="text/css">
		body, td, pre {
			text-align:left !important;
			color:#000;
			font-family:Arial, Helvetica, sans-serif;
			font-size:13px;
			margin:8px;
			line-height: 20px;
		}
		body {
			text-align:left !important;
			background:#FFF !important;;
		}
		h1 {
			display: block !important;
			text-align: center !important;
			font-size: 2em;
		}
		h2 {
			display: block !important;;
			font-size: 1.5em !important;;
		}
		h3 {
			display: block !important;
			font-size: 1.17em !important;;
		}
		h4 {
			display: block !important;
			font-family: Arial, Helvetica, Sans-Serif !important;
			font-size: 15px !important;
			color: #396 !important;
			margin-top: 20px !important;
			font-weight: normal !important;
			margin-bottom: 2px !important;
		}
		h5,
		h6 {
			display: block !important;;
			font-family: Arial, Helvetica, Sans-Serif !important;
			font-size: 13px !important;
			font-weight: bold !important;
			color: black !important;
			margin-top: 20px !important;
			margin-bottom: 0 !important;
		}
		h6 {
			display: block !important;
			font-style: italic !important;
		}
		code {
			color: #00008B;
			font-size: 12px;
			font-family: Arial, Helvetica, sans-serif;
		}
		 pre {
			color: #00008B;
			margin-left: 0px;
			background-color: #f2f2f2;
			padding: 0.5em;
			font-size: 12px;
			font-family: Courier, Geneva, monospace;
			overflow: auto;
		}
		tt,
		.inlinecode {
			color: #00008B;
			font-size: 13px;
			font-weight: bold !important;
		}
		a {
			text-decoration: none;
			color: #6c802e;
		}
		ol {
			display: block;
			list-style-type: decimal;
			margin-top: 1em;
			margin-bottom: 1em;
			margin-left: 0;
			margin-right: 0;
			padding-left: 40px;
		}
	</style>
	</head>
	<body>
';
		$e = "
	</body>
</html>";
		fwrite($f, $h);
		fwrite($f, "<h1>netPhotoGraphics release notes</h1>");
		fwrite($f, $object->getContent());
		fwrite($f, $e);
		fclose($f);

		npgFilters::register('edit_error', 'releaseNotesPublished');
	}
}

function releaseNotesPublished() {
	return "<p class='messagebox fade-message'>" . gettext('release_notes.htm has been updated') . '</p>';
}
