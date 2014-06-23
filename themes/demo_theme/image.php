<?php
/**
 * Theme standard page for the single (sized) image
 */
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext_th('Gallery RSS')); ?>
  </head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<?php printHomeLink('', ' | '); ?><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext_th('Albums Index'); ?>"><?php echo getGalleryTitle(); ?></a> | <?php printParentBreadcrumb(); ?><?php printAlbumTitle(); ?>
		<?php
		if (getOption('Allow_search')) {
			printSearchForm("", "search", "", gettext_th("Search gallery"));
		}
		?>
		<?php if (hasPrevImage()) { ?>
			<a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext_th("Previous Image"); ?>"><?php echo gettext_th("prev"); ?></a>
		<?php } if (hasNextImage()) { ?>
			<a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext_th("Next Image"); ?>"><?php echo gettext_th("next"); ?></a>
		<?php } ?>
		<?php printHomeLink('', ' | '); ?><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php gettext_th('Albums Index'); ?>"><?php echo getGalleryTitle(); ?></a> | <?php printParentBreadcrumb("", " | ", " | ");
		printAlbumBreadcrumb("", " | ");
		?><?php printImageTitle(true); ?>
		<a href="<?php echo html_encode(getFullImageURL()); ?>" title="<?php echo getBareImageTitle(); ?>">
		<?php printDefaultSizedImage(getImageTitle()); // the single sizeed image  ?>
		</a>
		<?php
		printImageDesc(); // the image description
		if (getImageMetaData()) {
			printImageMetadata('', false); // the image meta data like Exif
		}
		printTags('links', gettext_th('<strong>Tags:</strong>') . ' ', 'taglist', '');
		if (class_exists('RSS'))
			printRSSLink('Gallery', '', 'RSS', ' | ');
		//support for the comment form plugin
		if (function_exists('printCommentForm')) {
			printCommentForm();
		}
		printZenphotoLink();
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>