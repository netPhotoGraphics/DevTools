<?php
/**
 * Theme page for the news article loop (either all or for a cateogory) and single news articles of the Zenpage CMS plugin
 */
if (!defined('WEBPATH'))
	die();
if (class_exists('Zenpage')) { // Wrapper to cause a 404 error in case the Zenpage CMS plugin is not enabled as this theme page otherwise would throw errors
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css">
			<?php printRSSHeaderLink("News", "", "Zenpage news", ""); ?>
			<?php zp_apply_filter('theme_head'); ?>
		</head>
		<body>
			<?php zp_apply_filter('theme_body_open'); ?>
			<?php printHomeLink('', ' | '); ?><a href="<?php echo getGalleryIndexURL(false); ?>"><?php echo gettext("Index"); ?></a> <?php printNewsIndexURL(gettext('News'), ' » '); ?>
			<?php
			printZenpageItemsBreadcrumb(' » ', '');
			printCurrentNewsCategory(" » ");
			printNewsTitle(" » ");
			printCurrentNewsArchive(" » ");
			if (getOption('Allow_search')) {
				printSearchForm("", "search", "", gettext("Search gallery"));
			}
			?>
			<br />
			<?php
			if (is_NewsArticle()) { // single news article
				if (getPrevNewsURL()) {
					printPrevNewsLink();
				}
				if (getNextNewsURL()) {
					printNextNewsLink();
				}

				printNewsTitle();
				echo " ";

				printNewsDate();
				echo " ";
				printNewsCategories(", ", gettext("Categories: "), "newscategories");

				printNewsContent();
				printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', ');
				//comment form plugin support
				if (function_exists('printCommentForm')) {
					printCommentForm();
				}
			} else { // news article loop
				while (next_news()):

					printNewsURL();
					echo " ";
					printNewsDate();
					printNewsCategories(", ", gettext("Categories: "), "newscategories");
					printNewsContent();
					printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', ');
				endwhile;
				printNewsPageListWithNav(gettext('next &raquo;'), gettext('&laquo; prev'), true, 'pagelist', true);
			}
			?>
			<?php printRSSLink('Gallery', '', 'RSS', ' | '); ?>
			<?php printRSSLink("News", "", "", gettext("News"), ''); ?>
			<?php zp_apply_filter('theme_body_close'); ?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>