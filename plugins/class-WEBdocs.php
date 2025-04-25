<?php

/**
 * Plugin handler for certain standard document formats.
 * These are displayed a <i>WEBdocs</i> provider e.g. <i>Google Docs viewer</i>. The item is displayed in an iFrame sized based on the image size option.
 * Of course, your site must be accessible by provider and your viewer must have the required account credentials for this to work.
 *
 * The plugin is an extension of <var>TextObject</var>. For more details see the <i>class-textobject</i> plugin.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/class-WEBdocs
 * @pluginCategory media
 *
 * @deprecated since 2.00.08 GoogleDocs and Zoho appear to no longer support the iframe rendering of these documents
 */
$plugin_is_filter = 990 | CLASS_PLUGIN;
if (defined('SETUP_PLUGIN')) { //	gettext debugging aid
	$plugin_description = gettext('Provides a means for showing documents using <em>WEBdocs</em> for the document rendering.');
}

$option_interface = 'WEBdocs';

if (getOption('WEBdocs_pps_provider')) {
	Gallery::addImageHandler('pps', 'WEBdocs');
	Gallery::addImageHandler('ppt', 'WEBdocs');
}
if (getOption('WEBdocs_tif_provider')) {
	Gallery::addImageHandler('tif', 'WEBdocs');
	Gallery::addImageHandler('tiff', 'WEBdocs');
}


require_once(CORE_SERVERPATH . PLUGIN_FOLDER . '/class-textobject/class-textobject_core.php');

/**
 * creates a WEBdocs (image standin)
 *
 * @param object $album the owner album
 * @param string $filename the filename of the text file
 * @return TextObject
 */
class WEBdocs extends TextObject_core {

	function __construct($album = NULL, $filename = NULL, $quiet = false) {

		if (OFFSET_PATH == 2) {
			setOptionDefault('WEBdocs_pps_provider', 'google');
			setOptionDefault('WEBdocs_tif_provider', 'zoho');
		}
		$this->watermark = getOption('WEBdocs_watermark');
		$this->watermarkDefault = getOption('WEBdocs_watermark_default_images');

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
		return array(
				gettext('Watermark default images') => array('key' => 'WEBdocs_watermark_default_images', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 1,
						'desc' => gettext('Check to place watermark image on default thumbnail images.')),
				gettext('PowerPoint') => array('key' => 'WEBdocs_pps_provider', 'type' => OPTION_TYPE_RADIO,
						'buttons' => array(gettext('Disabled') => '',
								gettext('GoogleDocs') => 'google',
								gettext('Zoho') => 'zoho',
								gettext('Browser default') => 'local'
						),
						'order' => 2,
						'desc' => gettext("Choose the WEB service to use for rendering PowerPoint document.")),
				gettext('Tiff') => array('key' => 'WEBdocs_tif_provider', 'type' => OPTION_TYPE_RADIO,
						'buttons' => array(gettext('Disabled') => '',
								gettext('Zoho') => 'zoho',
								gettext('Browser default') => 'local'
						),
						'order' => 2,
						'desc' => gettext("Choose the WEB service to use for rendering TIFF images."))
		);
	}

	/**
	 * Returns the image file name for the thumbnail image.
	 *
	 * @return string
	 */
	function getThumbImageFile() {
		global $_gallery;

		if (is_null($this->objectsThumb)) {
			switch (getSuffix($this->filename)) {
				case "pdf":
					$img = '/pdfDefault.png';
					break;
				case 'ppt':
				case 'pps':
					$img = '/ppsDefault.png';
					break;
				case 'tif':
				case 'tiff':
					$img = '/tifDefault.png';
					break;
			}
			$imgfile = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($_gallery->getCurrentTheme()) . '/images/' . $img;
			if (!file_exists($imgfile)) {
				$imgfile = SERVERPATH . "/" . USER_PLUGIN_FOLDER . '/' . substr(basename(__FILE__), 0, -4) . '/' . $img;
			}
		} else {
			$imgfile = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb;
		}
		return $imgfile;
	}

	/**
	 * Returns the content of the text file
	 *
	 * @param int $w optional width
	 * @param int $h optional height
	 * @return string
	 */
	function getContent($w = NULL, $h = NULL) {
		$this->updateDimensions();
		if (is_null($w))
			$w = $this->getWidth();
		if (is_null($h))
			$h = $this->getHeight();

		$providers = array(
				'google' => '<iframe src="http://docs.google.com/viewer?url=%s&amp;embedded=true" width="' . $w . 'px" height="' . $h . 'px" frameborder="0" border="none" scrolling="auto" class="WEBdocs_google"></iframe>',
				'zoho' => '<iframe src="http://viewer.zoho.com/api/urlview.do?url=%s&amp;embed=true" width="' . $w . 'px" height="' . $h . 'px" frameborder="0" border="none" scrolling="auto" class="WEBdocs_zoho"></iframe>',
				'local' => '<iframe src="%s" width="' . $w . 'px" height="' . $h . 'px" frameborder="0" border="none" scrolling="auto" class="WEBdocs_local"></iframe>'
		);

		switch ($suffix = getSuffix($this->filename)) {
			case 'ppt':
				$suffix = 'pps';
			case 'tiff':
				$suffix = substr($suffix, 0, 3);
			case 'tif':
			case 'pps':
				$provider = 'WEBdocs_' . $suffix . '_provider';
				$iframe = sprintf($providers[getOption($provider)], html_encode($this->getFullImageURL(FULLWEBPATH)));
				break;
			default: // catches all others
				$iframe = '';
				break;
		}

		$html = '<div style="background-image: url(\'' . html_encode($this->getCustomImage(array('size' => min($w, $h), 'thumb' => 3, 'WM' => 'err-broken-page'))) . '\'); background-repeat: no-repeat; background-position: center;" >' .
						$iframe .
						'</div>';
		return $html;
	}

}

?>