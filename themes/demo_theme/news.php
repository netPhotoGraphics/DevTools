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
			<?php printHomeLink('', ' | '); ?><a href="<?php echo getGalleryIndexURL(false); ?>"><?php echo gettext_th("Index"); ?></a> <?php printNewsIndexURL(gettext_th('News'), ' » '); ?>
			<?php
			printZenpageItemsBreadcrumb(' » ', '');
			printCurrentNewsCategory(" » ");
			printNewsTitle(" » ");
			printCurrentNewsArchive(" » ");
			if (getOption('Allow_search')) {
				printSearchForm("", "search", "", gettext_th("Search gallery"));
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
				printNewsCategories(", ", gettext_th("Categories: "), "newscategories");

				printNewsContent();
				printTags('links', gettext_th('<strong>Tags:</strong>') . ' ', 'taglist', ', ');
				//comment form plugin support
				if (function_exists('printCommentForm')) {
					printCommentForm();
				}
			} else { // news article loop
				while (next_news()):

					printNewsURL();
					echo " ";
					printNewsDate();
					printNewsCategories(", ", gettext_th("Categories: "), "newscategories");
					printNewsContent();
					printTags('links', gettext_th('<strong>Tags:</strong>') . ' ', 'taglist', ', ');
				endwhile;
				printNewsPageListWithNav(gettext_th('next &raquo;'), gettext_th('&laquo; prev'), true, 'pagelist', true);
			}
			?>
			<?php printRSSLink('Gallery', '', 'RSS', ' | '); ?>
			<?php printRSSLink("News", "", "", gettext_th("News"), ''); ?>
			<?php zp_apply_filter('theme_body_close'); ?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>