<?php

/**
 *
 * Supports <var>.txt</var> files which consist of a web link to the actual "image"
 * content.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/class-externalLink
 * @pluginCategory media
 *
 */
$plugin_is_filter = 800 | CLASS_PLUGIN;
if (defined('SETUP_PLUGIN')) { //	gettext debugging aid
	$plugin_description = gettext('Provides a means for showing text type documents (.txt, .html, .htm).');
}

$option_interface = 'textObject';

Gallery::addImageHandler('lnk', 'imbededLink');

require_once(CORE_SERVERPATH . PLUGIN_FOLDER . '/class-textobject/class-textobject_core.php');

class externalLink extends TextObject_core {

	function __construct($album = NULL, $filename = NULL, $quiet = false) {

		$this->watermark = getOption('imbededVideo_watermark');
		$this->watermarkDefault = getOption('imbededVideo_watermark_default_images');

		if (is_object($album)) {
			parent::__construct($album, $filename, $quiet);
		}
	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Watermark default images') => array('key' => 'imbededVideo_watermark_default_images', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to place watermark image on default thumbnail images.')));
	}

	/**
	 * Returns the image file name for the thumbnail image.
	 *
	 * @return string
	 */
	function getThumbImageFile() {
		global $_gallery;

		if (is_null($this->objectsThumb)) {
			$img = '/' . getSuffix($this->filename) . 'Default.png';
			$imgfile = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($_gallery->getCurrentTheme()) . '/images/' . $img;
			if (!file_exists($imgfile)) {
				$imgfile = SERVERPATH . "/" . USER_PLUGIN_FOLDER . '/' . substr(basename(__FILE__), 0, -4) . $img;
			}
		} else {
			$imgfile = dirname($this->localpath) . '/' . $this->objectsThumb;
		}
		return $imgfile;
	}

	/**
	 * Returns the content of the text file
	 *
	 * @param int $w optional width
	 * @param int $h optional height
	 * @param dummy $container not used
	 * @return string
	 */
	function getContent($w = NULL, $h = NULL) {
		$this->updateDimensions();
		if (is_null($w))
			$w = $this->getWidth();
		if (is_null($h))
			$h = $this->getHeight();
		$s = min($w, $h);
		/*
		 * just return the thumbnail image as we do not know how to render the file.
		 */
		return '<iframe src="' . file_get_contents($this->localpath) . '" class="anyfile_default" width=' . $s . ' height=' . $s . '>';
	}

}
