<?php
/*
 * Provides support for the ZenPhoto20 website
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage ZenPhoto20
 * @category ZenPhoto20Tools
 */
$plugin_is_filter = 5 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext("Provides support for the ZenPhoto20 website.");
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'downloadButtons';

zp_register_filter('content_macro', 'downloadButtons::macro');
zp_register_filter('admin_utilities_buttons', 'downloadButtons::button');

class downloadButtons {

	function getOptionsSupported() {
		$options = array(gettext('Download release') => array('key' => 'downloadButtons_release', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('The version number of the release download'))
		);
		return $options;
	}

	static function printGitHubButtons() {
		$newestVersionURI = getOption('getUpdates_latest');
		$currentVersion = str_replace('setup-', '', stripSuffix(basename($newestVersionURI)));
		?>
		<span class="buttons">
			<a href="<?php echo $newestVersionURI; ?>" title="download the release"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/arrow_down.png" />ZenPhoto20 <?php echo $currentVersion; ?></a>
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
				'RELEASENOTES' => array('class' => 'procedure',
						'params' => array(),
						'value' => 'downloadButtons::releaseNotes',
						'owner' => 'downloadButtons',
						'desc' => gettext('Places release notes on a page.'))
		);
		return array_merge($macros, $my_macros);
	}

	static function announce($title, $content) {
		$result = zp_mail($title, $content, array('zenphoto20' => 'zenphoto20@googlegroups.com'), array(), array(), NULL, false, array('ZenPhoto20' => 'no-reply@zenphoto20.us'));
	}

	static function makeArticle() {
		$newestVersionURI = getOption('getUpdates_latest');
		$currentVersion = str_replace('setup-', '', stripSuffix(basename($newestVersionURI)));

		setOption('downloadButtons_release', $currentVersion);
		$current = explode('.', $currentVersion);
		unset($current[3]);
		$version = implode('.', $current);
		//	set prior release posts to un-published
		$sql = 'UPDATE ' . prefix('news') . ' SET `show`=0,`publishdate`=NULL,`expiredate`=NULL WHERE `author`="ZenPhoto20"';
		query($sql);
		//	create new article
		$text = sprintf('<p>ZenPhoto20 %1$s is now available for <a href="%2$s">download</a>.</p>', $version, $newestVersionURI);

		$f = file_get_contents(SERVERPATH . '/docs/release notes.htm');
		$i = strpos($f, '<body>');
		$j = strpos($f, '<hr />');
		$doc = substr($f, $i + 6, $j - $i - 6);
		$doc = preg_replace('~\<h1\>.+\</h1\>\s*\<h2\>Version.+?\</h2\>~i', '', $doc);
		$doc = preg_replace('~\<p\> \</p\>~i', '', $doc);

		$text.= $doc;

		$article = newArticle('ZenPhoto20-' . $version, true);
		$article->setDateTime(date('Y-m-d H:i:s'));
		$article->setPublishDate(date('Y-m-d H:i:s'));
		$article->setAuthor('ZenPhoto20');
		$article->setTitle('ZenPhoto20 ' . $version);
		$article->setContent($text);
		$article->setCategories(array('announce'));
		$article->setShow(1);
		$article->save();

		$text = sprintf('ZenPhoto20 %1$s is now available: zenphoto20.us/news/ZenPhoto20-%2$s', $version, $version);
		self::announce('ZenPhoto20 ' . $version, $text);
	}

	static function button($buttons) {
		$prior = explode('.', getOption('downloadButtons_release'));
		$newestVersionURI = getOption('getUpdates_latest');
		$currentVersion = str_replace('setup-', '', stripSuffix(basename($newestVersionURI)));

		$current = explode('.', $currentVersion);
		//ignore build
		unset($prior[3]);
		unset($current[3]);
		$buttons[] = array(
				'category' => gettext('Admin'),
				'enable' => $prior != $current,
				'button_text' => sprintf(gettext('Publish %1$s'), $currentVersion),
				'formname' => 'downloadButtons_button',
				'action' => '',
				'icon' => 'images/cache.png',
				'title' => sprintf(gettext('Publish %1$s'), $currentVersion),
				'alt' => '',
				'hidden' => '<input type="hidden" name="publish_release" value="yes" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'downloadButtons'
		);
		return $buttons;
	}

}

if (isset($_REQUEST['publish_release'])) {
	XSRFdefender('downloadButtons');
	downloadButtons::makeArticle();
}
?>