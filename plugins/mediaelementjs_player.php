<?php
/**
 * Support for the MediaElement.js video and audio player by John Dyer (http://mediaelementjs.com - license: MIT).
 * It will play natively via HTML5 in capable browsers and is responsive.
 *
 * Audio: <var>.mp3</var>, <var>.m4a</var> - Counterpart formats <var>.oga</var> and <var>.webma</var> supported (see note below!)<br>
 * Video: <var>.m4v</var>/<var>.mp4</var>, <var>.flv</var> - Counterpart formats <var>.ogv</var> and <var>.webmv</var> supported (see note below!)
 *
 * IMPORTANT NOTE ON OGG AND WEBM COUNTERPART FORMATS:
 * The counterpart formats are not valid formats for netPhotoGraphics itself and not recognized as items as that would confuse the management.
 * Therefore these formats can be uploaded via FTP only.
 * The files need to have the same file name (beware the character case!). In single player usage, the player
 * will check via the file system if a counterpart file exists and if counterpart support is enabled.
 * Firefox seems to prefer the <var>.oga</var> and <var>.ogv</var> while Chrome <var>.webma</var> and <var>.webmv</var>
 *
 * Since the flash fallback covers all essential formats this is not much of an issue for visitors though.
 *
 * If you have problems with any format being recognized, you might need to tell your server about the mime types first:
 * See examples on {@link http://mediaelementjs.com} under "installation".
 *
 * Subtitle and chapter support for videos (NOTE: NOT IMPLEMENTED YET!):
 * It supports .srt files. Like the counterpart formats MUST be uploaded via FTP! They must follow this naming convention:
 * subtitles file: <nameofyourvideo>_subtitles.srt
 * chapters file: <name of your video>_chapters.srt
 *
 * Example: yourvideo.mp4 with yourvideo_subtitles.srt and yourvideo_chapters.srt
 *
 * CONTENT MACRO:<br>
 * Mediaelementjs attaches to the content_macro MEDIAPLAYER you can use within normal text of Zenpage pages or articles for example.
 *
 * Usage:
 * [MEDIAPLAYER <albumname> <imagefilename> <number> <width> <height>]
 *
 * Example:
 * [MEDIAPLAYER album1 video.mp4 400 300]
 *
 * <b>NOTE:</b> This player does not support external albums!
 *
 * Playlist (beta):
 * Basic playlist support (adapted from Andrew Berezovsky – https://github.com/duozersk/mep-feature-playlist):
 * Enable the option to load the playlist script support. Then call on your theme's album.php the method $_zp_multimedia_extension->playlistPlayer();
 * echo $_zp_multimedia_extension->playlistPlayer('video','',''); //video playlist using all available .mp4, .m4v, .flv files only
 * echo $_zp_multimedia_extension->playlistPlayer('audio','',''); //audio playlist using all available .mp3, .m4a files only
 * Additionally you can set a specific albumname on the 2nd parameter to call a playlist outside of album.php
 *
 * Notes: Mixed audio and video playlists are not possible. Counterpart formats are also not supported. Also the next playlist item does not automatically play.
 *
 * @author Malte Müller (acrylian) <info@maltem.de>
 * @copyright 2014 Malte Müller
 * @license GPL v3 or later

 * @pluginCategory media
 * @package plugins/mediaelementjs_player
 */
if (defined('SETUP_PLUGIN')) { //	gettext debugging aid
	$plugin_is_filter = 5 | CLASS_PLUGIN;
	$plugin_description = gettext("Enable <strong>mediaelement.js</strong> to handle multimedia files.");
	$plugin_notice = gettext("<strong>IMPORTANT</strong>: Only one multimedia player plugin can be enabled at the time and the class-video plugin must be enabled, too.") . '<br /><br />' . gettext("Please see <a href='http://http://mediaelementjs.com'>mediaelementjs.com</a> for more info about the player and its license.");
	$plugin_disable = zpFunctions::pluginDisable(array(array(!extensionEnabled('class-video'), gettext('This plugin requires the <em>class-video</em> plugin')), array(class_exists('Video') && Video::multimediaExtension() != 'mediaelementjs_player' && Video::multimediaExtension() != 'pseudoPlayer', sprintf(gettext('mediaelementjs_player not enabled, <a href="#%1$s"><code>%1$s</code></a> is already instantiated.'), class_exists('Video') ? Video::multimediaExtension() : false)), array(getOption('album_folder_class') === 'external', gettext('This player does not support <em>External Albums</em>.'))));
}

$plugin_version = '1.1.1';
$option_interface = 'mediaelementjs_options';

Gallery::addImageHandler('flv', 'Video');
Gallery::addImageHandler('mp3', 'Video');
Gallery::addImageHandler('mp4', 'Video');
Gallery::addImageHandler('m4v', 'Video');
Gallery::addImageHandler('m4a', 'Video');

$_zp_multimedia_extension = new mediaelementjs_player(); // claim to be the flash player.
zp_register_filter('content_macro', 'mediaelementjs_player::macro');
zp_register_filter('theme_body_close', 'mediaelementjs_player::js');
if (getOption('mediaelementjs_playlist')) {
	zp_register_filter('theme_body_close', 'mediaelementjs_player::playlist_js');
}

class mediaelementjs_options {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('mediaelementjs_playpause', 1);
			setOptionDefault('mediaelementjs_progress', 1);
			setOptionDefault('mediaelementjs_current', 1);
			setOptionDefault('mediaelementjs_duration', 1);
			setOptionDefault('mediaelementjs_tracks', 0);
			setOptionDefault('mediaelementjs_volume', 1);
			setOptionDefault('mediaelementjs_fullscreen', 1);
			setOptionDefault('mediaelementjs_videowidth', '100%');
			setOptionDefault('mediaelementjs_videoheight', 270);
			setOptionDefault('mediaelementjs_audiowidth', '100%');
			setOptionDefault('mediaelementjs_audioheight', 30);
			setOptionDefault('mediaelementjs_preload', 0);
			setOptionDefault('mediaelementjs_poster', 1);
			setOptionDefault('mediaelementjs_videoposterwidth', 640);
			setOptionDefault('mediaelementjs_videoposterheight', 360);
			setOptionDefault('mediaelementjs_audioposter', 1);
			setOptionDefault('mediaelementjs_audiopostercrop', 0);
			setOptionDefault('mediaelementjs_audioposterwidth', 640);
			setOptionDefault('mediaelementjs_audioposterheight', 360);
			setOptionDefault('mediaelementjs_playlist', 0);
		}
	}

	function getOptionsSupported() {
		//$skins = self::getSkin();
		return array(
				gettext('Control bar') => array(
						'key' => 'mediaelementjs_controlbar',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'order' => 0,
						'checkboxes' => array(// The definition of the checkboxes
								gettext('Play/Pause') => 'mediaelementjs_playpause',
								gettext('Progress') => 'mediaelementjs_progress',
								gettext('Current') => 'mediaelementjs_current',
								gettext('Duration') => 'mediaelementjs_duration',
								gettext('Tracks (Video only)') => 'medialementjs_tracks',
								gettext('Volume') => 'mediaelementjs_volume',
								gettext('Fullscreen') => 'mediaelementjs_fullscreen',
								gettext('Always show controls') => 'mediaelementjs_showcontrols'
						),
						'desc' => gettext('Enable what should be shown in the player control bar.')),
				gettext('Video width') => array(
						'key' => 'mediaelementjs_videowidth', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('Pixel value or percent for responsive layouts')),
				gettext('Video height') => array(
						'key' => 'mediaelementjs_videoheight', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext('Pixel value or percent for responsive layouts')),
				gettext('Video Poster') => array(
						'key' => 'mediaelementjs_poster', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3,
						'desc' => gettext('If a poster of the videothumb should be shown. This is cropped to fit the player size as the player would distort image not fitting the player dimensions otherwise.')),
				gettext('Video poster width') => array(
						'key' => 'mediaelementjs_videoposterwidth', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 4,
						'desc' => gettext('Max width of the video poster (px). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
				gettext('Video poster height') => array(
						'key' => 'mediaelementjs_videoposterheight', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 5,
						'desc' => gettext('Height of the video poster (px). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
				gettext('Audio width') => array(
						'key' => 'mediaelementjs_audiowidth', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 6,
						'desc' => gettext('Pixel value or set 100% for responsive layouts (default).')),
				gettext('Audio height') => array(
						'key' => 'mediaelementjs_audioheight', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 7,
						'desc' => gettext('Pixel value or 100% for responsive layouts (default).')),
				gettext('Audio poster') => array(
						'key' => 'mediaelementjs_audioposter', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 8,
						'desc' => gettext('If an image of the videothumb should be shown with audio files. You need to set the width/height. This is cropped to fit the size.')),
				gettext('Audio poster width') => array(
						'key' => 'mediaelementjs_audioposterwidth', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 9,
						'desc' => gettext('Max width of the audio poster (px). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
				gettext('Audio poster height') => array(
						'key' => 'mediaelementjs_audioposterheight', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 10,
						'desc' => gettext('Height of the audio poster (px). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
				gettext('Playlist support') => array(
						'key' => 'mediaelementjs_playlist', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 11,
						'desc' => gettext('If enabled the script for playlist support is loaded. For playlists either use the macro or modify your theme.')),
				gettext('Preload') => array(
						'key' => 'mediaelementjs_preload', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 12,
						'desc' => gettext('If the files should be preloaded (Note if this works is browser dependent and might not work in all!).'))
		);
	}

	/** NOT USED YET
	 * Gets the skin names and css files
	 *
	 */
	static function getSkin() {
		$all_skins = array();
		$default_skins_dir = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/';
		$user_skins_dir = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/';
		$filestoignore = array('.', '..', '.DS_Store', 'Thumbs.db', '.htaccess', '.svn');
		$skins = array_diff(scandir($default_skins_dir), array_merge($filestoignore));
		$default_skins = self::getSkinCSS($skins, $default_skins_dir);
		//echo "<pre>";print_r($default_skins);echo "</pre>";
		$skins2 = @array_diff(scandir($user_skins_dir), array_merge($filestoignore));
		if (is_array($skins2)) {
			$user_skins = self::getSkinCSS($skins2, $user_skins_dir);
			//echo "<pre>";print_r($user_skins);echo "</pre>";
			$default_skins = array_merge($default_skins, $user_skins);
		}
		return $default_skins;
	}

	/** NOT USED YET
	 * Gets the css files for a skin. Helper function for getSkin().
	 *
	 */
	static function getSkinCSS($skins, $dir) {
		$skin_css = array();
		foreach ($skins as $skin) {
			$css = safe_glob($dir . '/' . $skin . '/*.css');
			if ($css) {
				$skin_css = array_merge($skin_css, array($skin => $css[0])); // a skin should only have one css file so we just use the first found
			}
		}
		return $skin_css;
	}

}

class mediaelementjs_player {

	public $mode = '';
	public $name = 'mediaelementjs_player';

	function __construct() {

	}

	static function getFeatureOptions() {
		$array = array();
		if (getOption('mediaelementjs_playpause'))
			$array[] = 'playpause';
		if (getOption('mediaelementjs_progress'))
			$array[] = 'progress';
		if (getOption('mediaelementjs_current'))
			$array[] = 'current';
		if (getOption('mediaelementjs_duration'))
			$array[] = 'duration';
		if (getOption('medialementjs_tracks'))
			$array[] = 'tracks';
		if (getOption('mediaelementjs_volume'))
			$array[] = 'volume';
		if (getOption('mediaelementjs_fullscreen'))
			$array[] = 'fullscreen';
		$count = '';
		$featurecount = count($array);
		$features = '';
		foreach ($array as $f) {
			$count++;
			$features .= "'" . $f . "'";
			if ($count != $featurecount) {
				$features .= ',';
			}
		}
		return $features;
	}

	static function js() {
		/*
		  $skin = getOption('mediaelementjs_skin');
		  if(file_exists($skin)) {
		  $skin = replaceScriptPath(Sskin,FULLWEBPATH); //replace SERVERPATH as that does not work as a CSS link
		  } else {
		  $skin = FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/mediaelementplayer.css';
		  }
		 */
		$features = mediaelementjs_player::getFeatureOptions();
		if (getOption('mediaelementjs_showcontrols')) {
			$showcontrols = 'true';
		} else {
			$showcontrols = 'false';
		}
		scriptLoader(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/mediaelementplayer.css');
		scriptLoader(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/mediaelement-and-player.min.js');
		?>
		<script>
			window.addEventListener('load', function () {
				$('audio.mep_player,video.mep_player').mediaelementplayer({
					alwaysShowControls: <?php echo $showcontrols; ?>,
					features: [<?php echo $features; ?>]
				});
			}, false);
		</script>
		<?php
	}

	static function playlist_js() {
		//$features = mediaelementjs_player::getFeatureOptions();
		$playlistfeatures = "'playlistfeature', 'prevtrack', 'playpause', 'nexttrack', 'loop', 'shuffle', 'playlist', 'current', 'progress', 'duration', 'volume'";
		//if(!empty($features)) {
		//	$playlistfeatures .= $playlistfeatures.','.$features;
		//}
		if (getOption('mediaelementjs_showcontrols')) {
			$showcontrols = 'true';
		} else {
			$showcontrols = 'false';
		}
		scriptLoader(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/mep-feature-playlist.css');
		scriptLoader(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/mep-feature-playlist.js');
		?>
		<script>
			window.addEventListener('load', function () {
				$('audio.mep_playlist,video.mep_playlist').mediaelementplayer({
					alwaysShowControls: <?php echo $showcontrols; ?>,
					loop: false,
					shuffle: false,
					playlist: true,
					playlistposition: 'top',
					features: [<?php echo $playlistfeatures; ?>],
				});
			}, false);
		</script>
		<?php
	}

	/**
	 * Get the JS configuration of Mediaelementjs_player
	 *
	 * @param mixed $movie the image object
	 * @param string $movietitle the title of the movie
	 * @param string $width Not used, set via plugin options.
	 * @param string $height Not used, set via plugin options.
	 *
	 */
	function getPlayerConfig($movie, $movietitle = '', $width = NULL, $height = NULL) {
		if (is_null($w)) {
			$width = $this->getWidth();
		}
		if (is_null($h)) {
			$height = $this->getHeight();
		}
		$moviepath = $movie->getFullImageURL(FULLWEBPATH);
		$ext = getSuffix($moviepath);
		if (!in_array($ext, array('m4a', 'm4v', 'mp3', 'mp4', 'flv'))) {
			echo '<p>' . gettext('This multimedia format is not supported by mediaelement.js.') . '</p>';
			return NULL;
		}
		switch ($ext) {
			case 'm4a':
			case 'mp3':
				$this->mode = 'audio';
				break;
			case 'mp4':
			case 'm4v':
			case 'flv':
				$this->mode = 'video';
				break;
		}

		if ($width == '100%') {
			$style = ' style="max-width: 100%"';
			$posterwidth = 600;
		} else {
			$style = '';
		}

		$count = $movie->getID();

		if (getOption('mediaelementjs_preload')) {
			$preload = ' preload="preload"';
		} else {
			$preload = ' preload="none"';
		}
		$playerconfig = '';
		$counterparts = $this->getCounterpartFiles($moviepath, $ext);
		switch ($this->mode) {
			case 'audio':
				$poster = '';
				if (getOption('mediaelementjs_audioposter')) {
					$posterwidth = getOption('mediaelementjs_audioposterwidth');
					$posterheight = getOption('mediaelementjs_audioposterheight');
					if (empty($posterwidth)) {
						$posterwidth = 640;
					}
					if (empty($posterheight)) {
						$posterheight = 360;
					}
					$playerconfig .= '<img class="mediaelementjs_audioposter" src="' . $movie->getCustomImage(NULL, $posterwidth, $posterheight, $posterwidth, $posterheight, NULL, NULL, true, NULL) . '" alt=""' . $style . '>' . "\n";
				}
				$playerconfig .= '
					<audio id="mediaelementjsplayer' . $count . '" class="mep_player" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . $style . '>
						<source type="audio/mp3" src="' . pathurlencode($moviepath) . '" />';
				if (!empty($counterparts)) {
					$playerconfig .= $counterparts;
				}
				$playerconfig .= '
						<object width="' . $width . '" height="' . $height . '" type="application/x-shockwave-flash" data="' . FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/flashmediaelement.swf">
							<param name="movie" value="' . FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/flashmediaelement.swf" />
							<param name="flashvars" value="controls=true&amp;file=' . pathurlencode($moviepath) . '" />
							<p>' . gettext('Sorry, no playback capabilities.') . '</p>
						</object>
					</audio>
				';
				break;
			case 'video':
				$poster = '';
				if (getOption('mediaelementjs_poster')) {
					$posterwidth = getOption('mediaelementjs_videoposterwidth');
					$posterheight = getOption('mediaelementjs_videoposterheight');
					if (empty($posterwidth)) {
						$posterwidth = 640;
					}
					if (empty($posterheight)) {
						$posterheight = 360;
					}
					$poster = ' poster="' . $movie->getCustomImage(null, $posterwidth, $posterheight, $posterwidth, $posterheight, null, null, true) . '"';
				}
				$playerconfig .= '
					<video id="mediaelementjsplayer' . $count . '" class="mep_player" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . $poster . $style . '>
						<source type="video/mp4" src="' . pathurlencode($moviepath) . '" />' . "\n";
				if (!empty($counterparts)) {
					$playerconfig .= $counterparts;
				}
				$playerconfig .= '
						<!-- <track kind="subtitles" src="subtitles.srt" srclang="en" /> -->
						<!-- <track kind="chapters" src="chapters.srt" srclang="en" /> -->
						<object width="' . $width . '" height="' . $height . '" type="application/x-shockwave-flash" data="' . FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/flashmediaelement.swf">
							<param name="movie" value="' . FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/flashmediaelement.swf" />
							<param name="flashvars" value="controls=true&amp;file=' . pathurlencode($moviepath) . '" />
							<p>' . gettext('Sorry, no playback capabilities.') . '</p>
						</object>
					</video>
				';
				break;
		}
		return $playerconfig;
	}

	/**
	 * outputs the player configuration HTML
	 *
	 * @param mixed $movie the image object if empty (within albums) the current image is used
	 * @param string $movietitle the title of the movie. if empty the Image Title is used
	 */
	function printPlayerConfig($movie = NULL, $movietitle = NULL) {
		global $_zp_current_image;
		if (empty($movie)) {
			$movie = $_zp_current_image;
		}
		echo $this->getPlayerConfig($movie, $movietitle);
	}

	/**
	 * Gets the counterpart formats (webm,ogg) for html5 browser compatibilty
	 * NOTE: These formats need to be uploaded via FTP as they are not valid file types for netPhotoGraphics, to avoid confusion
	 *
	 * @param string $moviepath full link to the multimedia file to get counterpart formats from.
	 * @param string $ext the file format extention to search the counterpart for (as we already have fetched that)
	 */
	function getCounterpartFiles($moviepath, $ext) {
		$counterparts = '';
		switch ($this->mode) {
			case 'audio':
				$suffixes = array('oga', 'webma');
				break;
			case 'video':
				$suffixes = array('ogv', 'webmv');
				break;
		}
		//$filesuffix ='';
		foreach ($suffixes as $suffix) {
			$counterpart = str_replace("." . $ext, "." . $suffix, $moviepath); // in case the letters of the extension are also part of the filename (e.g. ogv_video.ogv)
			if (file_exists(str_replace(FULLWEBPATH, SERVERPATH, $counterpart))) {
				switch ($suffix) {
					case 'oga':
						$type = 'audio/ogg';
						break;
					case 'ogv':
						$type = 'video/ogg';
						break;
					case 'webma':
						$type = 'audio/webm';
						break;
					case 'webmv':
						$type = 'video/webm';
						break;
				}
				$counterparts .= '<source type="' . $type . '" src="' . pathurlencode($counterpart) . '" />' . "\n";
				//array_push($counterparts,$source);
			}
		}
		return $counterparts;
	}

	/**
	 * Returns the width of the player
	 * @param object $image the image for which the width is requested (not used)
	 *
	 * @return mixed
	 */
	function getWidth($image = NULL) {
		switch ($this->mode) {
			case 'audio':
				$width = getOption('mediaelementjs_audiowidth');
				if (empty($width)) {
					return '100%';
				} else {
					return $width;
				}
				break;
			case 'video':
				$width = getOption('mediaelementjs_videowidth');
				if (empty($width)) {
					return '100%';
				} else {
					return $width;
				}
				break;
		}
	}

	/**
	 * Returns the height of the player
	 * @param object $image the image for which the height is requested (not used!)
	 *
	 * @return mixed
	 */
	function getHeight($image = NULL) {
		switch ($this->mode) {
			case 'audio':
				$height = getOption('mediaelementjs_audioheight');
				if (empty($height)) {
					return '30';
				} else {
					return $height;
				}
				break;
			case 'video':
				$height = getOption('mediaelementjs_videoheight');
				if (empty($height)) {
					return 'auto';
				} else {
					return $height;
				}
				break;
		}
	}

	static function getMacroPlayer($albumname, $imagename, $width = NULL, $height = NULL) {
		global $_zp_multimedia_extension;
		$movie = newImage(NULL, array('folder' => $albumname, 'filename' => $imagename), true);
		if ($movie->exists) {
			return $_zp_multimedia_extension->getPlayerConfig($movie, NULL, $width, $height);
		} else {
			return '<span class = "error">' . sprintf(gettext('%1$s::%2$s not found.'), $albumname, $imagename) . '</span>';
		}
	}

	static function macro($macros) {
		$macros['MEDIAPLAYER'] = array(
				'class' => 'function',
				'params' => array('string', 'string', 'int*', 'int*'),
				'value' => 'mediaelementjs_player::getMacroPlayer',
				'owner' => 'mediaelementjs_player',
				'desc' => gettext('Provide the album name (%1), media file name (%2), optional width (%3) and optional height (%4)')
		);
		return $macros;
	}

	/*	 * Experimental
	 * Returns the width of the player
	 * @param object $image the image for which the height is requested (not used!)
	 *
	 * @return mixed
	 */

	function playlistPlayer($mode, $albumfolder = '', $count = '') {
		global $_zp_current_album;

		if (empty($albumfolder)) {
			$albumobj = $_zp_current_album;
		} else {
			$albumobj = newAlbum($albumfolder);
		}
		if (empty($count)) {
			$multiplayer = false;
			$count = '1';
		} else {
			$multiplayer = true; // since we need extra JS if multiple players on one page
			$count = $count;
		}
		$playerconfig = '';
		if (getOption('mediaelementjs_preload')) {
			$preload = ' preload="preload"';
		} else {
			$preload = ' preload="none"';
		}
		$counteradd = '';
		switch ($mode) {
			case 'audio':
				$width = getOption('mediaelementjs_audiowidth');
				$height = 'auto';
				if ($width == '100%') {
					$style = ' style="max-width: 100%;clear: both;"';
				} else {
					$style = '';
				}
				$playerconfig = '
					<audio id="mediaelementjsplayer' . $count . '" class="mep_playlist" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . $style . '>';
				$files = $albumobj->getImages(0);
				$counter = '';
				foreach ($files as $file) {
					$ext = getSuffix($file);
					if (in_array($ext, array('m4a', 'mp3'))) {
						$counteradd = '';
						$counter++;
						if ($counter < 10)
							$counteradd = '0';
						$obj = newImage($albumobj, $file);
						$playerconfig .= '<source type="audio/mpeg" src="' . pathurlencode($obj->getFullImageURL()) . '" title="' . $counteradd . $counter . '. ' . html_encode($obj->getTitle()) . '" />';
						/* Does not work with this playlist script
						  $counterparts = $this->getCounterpartFiles($moviepath,$ext);
						  if(count($counterparts) != 0) {
						  foreach($counterparts as $counterpart) {
						  $playerconfig .= $counterpart;
						  }
						  }
						 */
					}
				}
				$playerconfig .= '
					</audio>
				';
				break;
			case 'video':
				$width = getOption('mediaelementjs_videowidth');
				$height = getOption('mediaelementjs_videoheight');
				if ($width == '100%') {
					$style = ' style="max-width: 100%;display:block;"';
				} else {
					$style = '';
				}
				$playerconfig = '
					<video id="mediaelementjsplayer' . $count . '" class="mep_playlist" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . $style . '>';
				$files = $albumobj->getImages(0);
				$counter = '';
				foreach ($files as $file) {
					$ext = getSuffix($file);
					if (in_array($ext, array('m4v', 'mp4', 'flv'))) {
						$counteradd = '';
						$counter++;
						if ($counter < 10)
							$counteradd = '0';
						$obj = newImage($albumobj, $file);
						$playerconfig .= '<source type="video/mp4" src="' . pathurlencode($obj->getFullImageURL()) . '" title="' . $counteradd . $counter . '. ' . html_encode($obj->getTitle()) . ')" />';
						/* Does not work with this playlist script
						  $counterparts = $this->getCounterpartFiles($moviepath,$ext);
						  if(count($counterparts) != 0) {
						  foreach($counterparts as $counterpart) {
						  $playerconfig .= $counterpart;
						  }
						  }
						 */
					}
				}
				$playerconfig .= '
					</video>
				';
				break;
		}
		return $playerconfig;
	}

}
?>