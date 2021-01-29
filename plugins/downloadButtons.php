<?php
/*
 * Provides support for the netPhotoGraphics website
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/downloadButtons
 * @pluginCategory netPhotoGraphics
 */

require_once(CORE_SERVERPATH . PLUGIN_FOLDER . '/common/gitHubAPI/github-api.php');

use Milo\Github;

$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Provides a button to download the latest version of the software.");

class downloadButtons {

	function __construct() {
		purgeOption('getUpdates_lastCheck');
	}

	static function printGitHubButtons() {
		$newestVersionURI = getOption('getUpdates_latest');
		$currentVersion = str_replace('setup-', '', stripSuffix(basename($newestVersionURI)));
		?>

		<?php npgButton('button', ARROW_DOWN_GREEN . ' netPhotoGraphics ' . str_replace('master-', '', $currentVersion), array('buttonLink' => $newestVersionURI, 'buttonTitle' => gettext('download the release'))); ?>

		<br />
		<br />
		<?php
	}

	static function releaseNotes() {
		$f = file_get_contents(SERVERPATH . '/docs/release notes.htm');
		$i = strpos($f, '<body>');
		$j = strpos($f, '</body>');
		$c = substr($f, $i + 6, $j - $i - 6);
		echo $c;
	}

	static function head() {
		global $_CMS_current_page;
		if (isset($_CMS_current_page) && $_CMS_current_page->getTitleLink() == 'release-notes' . RW_SUFFIX) {
			$f = file_get_contents(SERVERPATH . '/docs/release notes.htm');
			$i = strpos($f, '<style');
			$j = strpos($f, '</style>');
			$s = substr($f, $i, $j - $i + 8) . "\n";
			echo $s;
		}
	}

	static function repositoryLink($text, $sublink = '') {
		echo printNPGgitHubLink($sublink, $text);
	}

	static function macro($macros) {
		$my_macros = array(
				'RELEASENOTES' => array('class' => 'procedure',
						'params' => array(),
						'value' => 'downloadButtons::releaseNotes',
						'owner' => 'downloadButtons',
						'desc' => gettext('Places release notes on a page.')),
				'REPOSITORY' => array('class' => 'procedure',
						'params' => array('string', 'string*'),
						'value' => 'downloadButtons::repositoryLink',
						'owner' => 'downloadButtons',
						'desc' => gettext('Yields a link to the netPhotoGraphics gitHub repository (to the sub-folder %2 if present) with the  %1 as the link text.')),
				'DOWNLOADBUTTON' => array('class' => 'procedure',
						'params' => array(),
						'value' => 'downloadButtons::printGitHubButtons',
						'owner' => 'downloadButtons',
						'desc' => gettext('Emits the NPG software download button.'))
		);
		return array_merge($macros, $my_macros);
	}

	static function makeArticle() {
		$newestVersionURI = getOption('getUpdates_latest');
		$currentVersion = str_replace('setup-', '', stripSuffix(basename($newestVersionURI)));

		$current = explode('.', $currentVersion);
		unset($current[3]);
		$version = implode('.', $current);
		//	set prior release posts to un-published
		$sql = 'UPDATE ' . prefix('news') . ' SET `show`=0,`publishdate`=NULL,`expiredate`=NULL WHERE `owner`="netPhotoGraphics"';
		query($sql);
		//	create new article
		$text = sprintf('<p>netPhotoGraphics %1$s is now available for <a href="%2$s">download</a>.</p>', $version, $newestVersionURI);

		$f = file_get_contents(SERVERPATH . '/docs/release notes.htm');
		$i = strpos($f, '<body>');
		$j = strpos($f, '<hr />');
		$doc = substr($f, $i + 6, $j - $i - 6);
		$doc = preg_replace('~\<h1\>.+\</h1\>\s*\<h2\>Version.+?\</h2\>~i', '', $doc);
		$doc = preg_replace('~\<p\> \</p\>~i', '', $doc);

		$text .= $doc;

		$article = newArticle('netPhotoGraphics-' . $version, true);
		$article->setDateTime(date('Y-m-d H:i:s'));
		$article->setPublishDate(date('Y-m-d H:i:s'));
		$article->setOwner('netPhotoGraphics');
		$article->setTitle('netPhotoGraphics ' . $version);
		$article->setContent($text);
		$article->setCategories(array('announce'));
		$article->setShow(1);
		$article->save();

		setOption('downloadButtons_published', $version);
	}

	static function button($buttons) {
		if (getOption('getUpdates_lastCheck') + 8640 < time()) {
			setOption('getUpdates_lastCheck', time());
			try {
				$api = new Github\Api;
				$fullRepoResponse = $api->get('/repos/:owner/:repo/releases/latest', array('owner' => GITHUB_ORG, 'repo' => 'netPhotoGraphics'));
				$fullRepoData = $api->decode($fullRepoResponse);
				$assets = $fullRepoData->assets;
				if (!empty($assets)) {
					$item = array_pop($assets);
					setOption('getUpdates_latest', $item->browser_download_url);
				}
			} catch (Exception $e) {
				debugLog(gettext('GitHub repository not accessible [downloadButtons]. ') . $e);
			}
		}

		$newestVersionURI = getOption('getUpdates_latest');
		$currentVersion = str_replace('setup-', '', stripSuffix(basename($newestVersionURI)));

		$current = explode('.', $currentVersion);
		//ignore build
		unset($current[3]);
		$v = implode('.', $current);

		$buttons[] = array(
				'category' => gettext('Admin'),
				'enable' => $v != getOption('downloadButtons_published'),
				'button_text' => sprintf(gettext('Publish %1$s'), $v),
				'formname' => 'downloadButtons_button',
				'action' => '',
				'icon' => CIRCLED_BLUE_STAR,
				'title' => sprintf(gettext('Publish %1$s'), $v),
				'alt' => '',
				'hidden' => '<input type="hidden" name="publish_release" value="yes" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'downloadButtons'
		);

		return $buttons;
	}

}

npgFilters::register('content_macro', 'downloadButtons::macro');
npgFilters::register('theme_head', 'downloadButtons::head');

if (isset($_REQUEST['publish_release'])) {
	XSRFdefender('downloadButtons');
	downloadButtons::makeArticle();
}
?>