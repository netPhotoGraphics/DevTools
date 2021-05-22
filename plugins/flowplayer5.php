<?php
/**
 * Support for the Flowplayer jQuery/Flash 5 video player (flowplayer.org). It will play video natively via HTML5 in capable browser
 * if the appropiate multimedia formats are provided. The player size is responsive to the browser size. This is an adaption of the existing jPlayer plugin.

 * Audio: This plugin does not play audio files.<br>
 * Video: <var>.m4v</var>/<var>.mp4</var>, <var>.flv</var> - Counterpart formats <var>.ogv</var> and <var>.webm</var> supported (see note below!)
 *
 * IMPORTANT NOTE ON OGG AND WEBM COUNTERPART FORMATS:
 *
 * The counterpart formats are not valid formats for saveLayoutSelection itself as that would confuse the management.
 * Therefore these formats can be uploaded via ftp only.
 * The files needed to have the same file name (beware the character case!).
 *
 * NOTE ON PLAYER SKINS:<br>
 * The look of the player is determined by a pure HTML/CSS based skin (theme). There may occur display issues with themes.
 * So you might need to adjust the skin yourself to work with your theme. It is recommended that
 * you place your custom skins within the root /plugins folder like:
 *
 * plugins/flowplayer5/skins/<i>skin name1</i><br>
 * plugins/flowplayer5/skins/<i>skin name2</i> ...
 *
 * You can select the skin then via the plugin options. <b>NOTE:</b> A skin may have only one CSS file.
 *
 * <b>NOTE:</b> This player does not support external albums!
 *
 * @author Jim Brown (based on work by Malte Müller)
 *
 * @package plugins/flowplayer5
 * @pluginCategory media
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
if (defined('SETUP_PLUGIN')) { //	gettext debugging aid
	$plugin_description = gettext("Enable <strong>Flowplayer5</strong> to handle video files.");
	$plugin_notice = gettext("<strong>IMPORTANT</strong>: Only one multimedia extension plugin can be enabled at the time and the class-video plugin must be enabled, too.") . '<br /><br />' . gettext("Please see <a href='http://flowplayer.org'>flowplayer.org</a> for more info about the player and its license.");
	$plugin_disable = npgFunctions::pluginDisable(array(array(!extensionEnabled('class-video'), gettext('This plugin requires the <em>class-video</em> plugin')), array(class_exists('Video') && Video::multimediaExtension() != 'flowplayer5' && Video::multimediaExtension() != 'html5Player', sprintf(gettext('Flowplayer5 not enabled, <a href="#%1$s"><code>%1$s</code></a> is already instantiated.'), class_exists('Video') ? Video::multimediaExtension() : false)), array(getOption('album_folder_class') === 'external', gettext('This player does not support <em>External Albums</em>.'))));
}

$option_interface = 'flowplayer5';

require_once(PLUGIN_SERVERPATH . 'class-video.php');

Gallery::addImageHandler('flv', 'Video');
Gallery::addImageHandler('mp4', 'Video');
Gallery::addImageHandler('m4v', 'Video');

class Flowplayer5 extends html5Player {

	public $width = '';
	public $height = '';
	public $playersize = '';
	public $mode = '';
	public $supplied = '';
	public $supplied_counterparts = '';
	public $name = 'flowplayer5';

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('flowplayer5_autoplay', '');
			setOptionDefault('flowplayer5_poster', 1);
			setOptionDefault('flowplayer5_skin', 'minimalist');
		}
		$this->width = 1280;
		$this->height = 720;
	}

	function getOptionsSupported() {
		$skins = getPluginFiles('*', 'jPlayer/skin/', FALSE, GLOB_ONLYDIR);
		foreach ($skins as $skin => $path) {
			$skins[$skin] = $skin;
		}

		return array(gettext('Player skin') => array('key' => 'flowplayer5_skin', 'type' => OPTION_TYPE_SELECTOR,
						'selections' => $skins,
						'desc' => gettext("Select the skin (theme) to use. <br />NOTE: Since the skin is pure HTML/CSS only there may be display issues with certain themes that require manual adjustments. Place custom skin within the root plugins folder. See plugin documentation for more info.")),
				gettext('Poster (Videothumb)') => array('key' => 'flowplayer5_poster', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("If the videothumb should be shown (Flowplayer calls it poster).")),
				gettext('Autoplay') => array('key' => 'flowplayer5_autoplay', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("Disabled automatically if several players on one page"))
		);
	}

	static function headJS() {
		$skin = array_shift(getPluginFiles('*.css', '/flowplayer5/skins/' . getOption('flowplayer5_skin')));
		if (file_exists($skin)) {
			$skin = replaceScriptPath($skin, FULLWEBPATH); //replace SERVERPATH as that does not work as a CSS link
		} else {
			$skin = WEBPATH . '/' . USER_PLUGIN_FOLDER . '/flowplayer5/skins/minimalist/minimalist.css';
		}
		?>
		<link type="text/css" rel="stylesheet" href="<?php echo $skin; ?>" />
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . USER_PLUGIN_FOLDER; ?>/flowplayer5/flowplayer.min.js"></script>
		<?php
	}

	/**
	 * Get the JS configuration of flowplayer
	 *
	 * @param mixed $movie the image object
	 * @param string $movietitle the title of the movie
	 *
	 */
	function getPlayerConfig($movie, $movietitle = NULL, $count = NULL, $w = NULL, $h = NULL) {
		if (is_null($w)) {
			$w = $this->getWidth();
		}
		if (is_null($h)) {
			$h = $this->getHeight();
		}
		$moviepath = $movie->getImagePath(FULLWEBPATH);
		$ext = getSuffix($moviepath);
		if (!in_array($ext, array('m4v', 'mp4', 'flv'))) {
			return parent::getPlayerConfig($movie, $movietitle, $count, $w, $h);
		}
		$autoplay = '';
		if (getOption('flowplayer5_autoplay')) {
			$autoplay = '$(".fp-engine").attr("autoplay","");';
		}
		$videoThumb = '';
		if (getOption('flowplayer5_poster')) {
			$videoThumb = '$(".fp-engine").attr("poster","' . $movie->getCustomImage(array('sitdh' => $w, 'height' => $h, 'cw' => $w, 'ch' => $h, 'thumb' => TRUE)) . '");';
		}
		$metadata = getImageMetaData(NULL, false);
		$vidWidth = $metadata['VideoResolution_x'];
		$vidHeight = $metadata['VideoResolution_y'];
		$playerconfig = '
			<div id="vidstage" style="margin-left: auto; margin-right: auto; max-width: ' . $vidWidth . '; max-height: ' . $vidHeight . ';">
				<div id="player" ></div>
			</div>
			<script type="text/javascript">
			// <!-- <![CDATA[
				var viewportwidth;
				var viewportheight;
				var maxvidheight = ' . $vidHeight . '
				var maxvidwidth = ' . $vidWidth . '
				var vidwidth
				var vidheight
				var vidratio = maxvidheight / maxvidwidth
				function setStage(){
					if (typeof window.innerWidth != "undefined") {
						viewportwidth = window.innerWidth,
						viewportheight = window.innerHeight
					} else if (typeof document.documentElement != "undefined" && typeof document.documentElement.clientWidth != "undefined" && document.documentElement.clientWidth != 0) {
						viewportwidth = document.documentElement.clientWidth,
						viewportheight = document.documentElement.clientHeight
					} else {
						viewportwidth = document.getElementsByTagName("body")[0].clientWidth,
						viewportheight = document.getElementsByTagName("body")[0].clientHeight
					}
					vidheight = viewportheight - 50
					if (vidheight > maxvidheight) {
						vidheight = maxvidheight
					};
					vidwidth = vidheight / vidratio,
					$("#vidstage").css({"max-width" : vidwidth + "px"})
					$("#vidstage").css({"max-height"  : vidheight + "px"})
				};
				$(document).ready(function() {
					$("#player").flowplayer({
						playlist: [
							[
								' . $this->getCounterpartFile($moviepath, "webm") . ',
								' . $this->getCounterpartFile($moviepath, "mp4") . ',
								' . $this->getCounterpartFile($moviepath, "ogv") . '
							]
						]
					});
					$(".fp-embed").remove();
					' . $autoplay . '
					' . $videoThumb . '
					$(".fp-ratio").css({"padding-top" : vidratio * 100 + "%"});
					setStage()
				});
				var resizeTimer;
				$(window).resize(function() {
					clearTimeout(resizeTimer);
					resizeTimer = setTimeout(setStage, 100);
				});
			// ]]> -->
			</script>';
		return $playerconfig;
	}

	/**
	 * outputs the player configuration HTML
	 *
	 * @param mixed $movie the image object if empty (within albums) the current image is used
	 * @param string $movietitle the title of the movie. if empty the Image Title is used
	 * @param string $count unique text for when there are multiple player items on a page
	 */
	function printPlayerConfig($movie = NULL, $movietitle = NULL) {
		global $_current_image;
		if (empty($movie)) {
			$movie = $_current_image;
		}
		echo $this->getPlayerConfig($movie, $movietitle);
	}

	/**
	 * Returns the width of the player
	 *
	 * @return int
	 */
	function getWidth() {
		return $this->width;
	}

	/**
	 * Returns the height of the player
	 *
	 * @return int
	 */
	function getHeight() {
		return $this->height;
	}

	function getCounterpartfile($moviepath, $ext) {
		$counterpartFile = '';
		$counterpart = str_replace("mp4", $ext, $moviepath);
		if (file_exists(str_replace(FULLWEBPATH, SERVERPATH, $counterpart))) {
			$counterpartFile = '{' . $ext . ': "' . pathurlencode($counterpart) . '" }';
		}

		return $counterpartFile;
	}

}

$_multimedia_extension = new Flowplayer5(); // claim to be the flash player.
npgFilters::register('theme_head', 'flowplayer5::headJS');
