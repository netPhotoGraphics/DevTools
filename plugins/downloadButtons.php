<?php
/*
 * Provides support for the ZenPhoto20 website
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage ZenPhoto20

 */
$plugin_is_filter = 5 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext("Provides support for the ZenPhoto20 website.");
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'downloadButtons';

zp_register_filter('content_macro', 'downloadButtons::macro');

if (OFFSET_PATH && OFFSET_PATH != 2 && zp_loggedin()) {
	$prior = explode('.', getOption('downloadButtons_release'));
	$current = explode('.', ZENPHOTO_VERSION);
	//ignore build
	unset($prior[3]);
	unset($current[3]);
	if ($prior != $current) {
		setOption('downloadButtons_release', ZENPHOTO_VERSION);
		downloadButtons::makeArticle(implode('.', $current));
	}
}

class downloadButtons {

	function getOptionsSupported() {
		$options = array(gettext('Download release') => array('key'		 => 'downloadButtons_release', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext('The version number of the release download'))
		);
		return $options;
	}

	static function printGitHubButtons() {
		$release = getOption('downloadButtons_release');
		$current = explode('.', $release);
		$current[3] = 0;
		$current = implode('.', $current);
		?>
		<span class="buttons">
			<a href="https://github.com/ZenPhoto20/ZenPhoto20/releases/download/ZenPhoto20-<?php echo $current; ?>/setup-<?php echo $release; ?>.zip" title="download the release"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/arrow_down.png" />ZenPhoto20 <?php echo $release; ?></a>
		</span>
		<br />
		<br />
		<?php
	}

	static function releaseNotes() {
		$f = file_get_contents(SERVERPATH . '/docs/release notes.htm');
		$i = strpos($f, '<body>');
		$j = strpos($f, '</body>');
		$f = substr($f, $i + 6, $j - $i - 6);
		echo $f;
	}

	static function macro($macros) {
		$my_macros = array(
						'RELEASENOTES' => array('class'	 => 'procedure',
										'params' => array(),
										'value'	 => 'downloadButtons::releaseNotes',
										'owner'	 => 'downloadButtons',
										'desc'	 => gettext('Places release notes on a page.'))
		);
		return array_merge($macros, $my_macros);
	}

	static function announce($object) {

		$content = $object->getContent();
		preg_match_all("/<a.*?href=\"'?(.*?)\".*?>(.*?)<\/a>/i", $content, $matches);

		if (!empty($matches[0])) {
			foreach ($matches[0] as $key => $match) {
				if (!empty($match)) {
					$content = str_replace($match, $matches[2][$key] . ': ' . $matches[1][$key], $content);
				}
			}
		}

		$content = str_replace("\n", '', $content);
		$content = str_replace("\r", '', $content);
		$content = str_replace("&nbsp;", ' ', $content);
		$content = preg_replace('|<[/]*p[^>]*>?|i', "\r\n", $content);
		$content = preg_replace('|<[/]*ul[^>]*>?|i', "\r\n", $content);
		$content = preg_replace('|<li[^>]*>?|i', " - ", $content);
		$content = preg_replace('|</li>?|i', "\r\n", $content);
		$content = preg_replace('|<br[^>]*/>?|i', "\r\n", $content);
		$content = html_entity_decode($content, ENT_QUOTES, 'ISO-8859-1');
		$content = trim(strip_tags($content), "\r\n");
		$content = str_replace('  ', ' ', $content);


		$result = zp_apply_filter('sendmail', '', array('zenphoto20' => 'zenphoto20@googlegroups.com'), strip_tags($object->getTitle()), $content, 'no-reply@zenphoto20.us', 'ZenPhoto20', array(), NULL);
	}

	static function makeArticle($version) {
		//	set prior release posts to un-published
		$sql = 'UPDATE ' . prefix('news') . ' SET `show`=0,`publishdate`=NULL,`expiredate`=NULL WHERE `author`="ZenPhoto20"';
		query($sql);
		//	create new article
		$text = sprintf('<p>ZenPhoto20 %1$s is now available for <a href="https://github.com/ZenPhoto20/ZenPhoto20/releases/download/ZenPhoto20-%2$s">download</a>. For details see the <a href="http://ZenPhoto20.us/pages/release-notes">release notes</a>.</p>', $version, ZENPHOTO_VERSION);

		$article = newArticle('ZenPhoto20 ' . ZENPHOTO_VERSION, true);
		$article->setDateTime(date('Y-m-d H:i:s'));
		$article->setAuthor('ZenPhoto20');
		$article->setTitle('ZenPhoto20 ' . $version);
		$article->setContent($text);
		$article->setCategories(array('announce'));
		$article->setShow(1);
		$article->save();

		self::announce($article);
	}

}
?>
