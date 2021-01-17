<?php
/**
 * decides what 404 errors get logged
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/filter_404_logging
 * @pluginCategory admin
 *
 */
$plugin_is_filter = defaultExtension(0 | FEATURE_PLUGIN);
$plugin_description = gettext('Filter what gets logged for 404 errors.');

$option_interface = 'filter404';

npgFilters::register('log_404', 'filter404::filter');

class filter404 {

	function getOptionsSupported() {

		$options = array(
				gettext('Filter RegEx\'s') => array('key' => 'filter_404_logging_exceptions', 'type' => OPTION_TYPE_CUSTOM,
						'desc' => gettext('Regular expressions to prevent 404 logging.')
				)
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
		$list = getSerializedArray(getOption('filter_404_logging_exceptions'));
		$key = 0;
		foreach ($list as $key => $regex) {
			?>
			<input id="404filter_<?php echo $key; ?>a" type="textbox" size="15" name="404filter_<?php echo $key; ?>" style="width: 100%" 						 value="<?php echo html_encode($regex); ?>" />
			<br />
			<?php
		}
		$i = $key;
		while ($i < $key + 4) {
			$i++;
			?>
			<input id="404filter_<?php echo $i; ?>a" type="textbox" name="404filter_<?php echo $i; ?>" style="width: 100%" value="" />
			<br />
			<?php
		}
	}

	static function handleOptionSave($themename, $themealbum) {
		$notify = '';
		$list = array();
		foreach ($_POST as $key => $param) {
			if ($param) {
				if (strpos($key, '404filter_') !== false) {
					$list[] = $param;
				}
			}
		}
		setOption('filter_404_logging_exceptions', serialize($list));
	}

	static function filter($log, $data) {
		list($album, $image, $galleryPage, $theme, $page) = $data;
		$list = getSerializedArray(getOption('filter_404_logging_exceptions'));
		foreach ($list as $regex) {
			$log = $log && !preg_match($regex, $album);
		}
		return $log;
	}

}
