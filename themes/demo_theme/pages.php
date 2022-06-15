<?php
/**
 * Theme page for pages of the Zenpage CMS plugin
 */
if (!defined('WEBPATH'))
	die();
if (class_exists('Zenpage')) { // Wrapper to cause a 404 error in case the Zenpage CMS plugin is not enabled as this theme page otherwise would throw errors
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<?php zp_apply_filter('theme_head'); ?>
			<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
			<?php printRSSHeaderLink("Pages", "", "Zenpage news", ""); ?>
		</head>
		<body>
			<?php zp_apply_filter('theme_body_open'); ?>
			<?php printHomeLink('', ' | '); ?><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo ('Gallery Index'); ?>"><?php echo getGalleryTitle(); ?></a> | <?php printZenpageItemsBreadcrumb(" » ", ""); ?>
			<?php
			if (getOption('Allow_search')) {
				printSearchForm("", "search", "", gettext("Search gallery"));
			}
			printPageTitle();
			printPageContent();
			printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', ');
			printRSSLink('Gallery', '', 'RSS', ' | ');
			printRSSLink("News", "", "", gettext("News"), '');
			//comment form plugin support
			if (function_exists('printCommentForm')) {
				printCommentForm();
			}
			zp_apply_filter('theme_body_close');
			?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>
