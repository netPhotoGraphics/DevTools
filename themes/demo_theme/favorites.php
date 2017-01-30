<?php
/**
 * A page to display user favorites if the favorites plugin is enabled
 */
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
		<?php if (class_exists('RSS')) printRSSHeaderLink('Album', getAlbumTitle()); ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<?php printHomeLink('', ' | '); ?><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext_th('Albums Index'); ?>"><?php echo getGalleryTitle(); ?></a> | <?php printParentBreadcrumb(); ?><?php printAlbumTitle(); ?>
		<?php
		if (getOption('Allow_search')) {
			printSearchForm("", "search", "", gettext_th("Search gallery"));
		}
		?>
		<?php printAlbumDesc(); ?>
		<?php while (next_album()): // the loop of the sub albums within the album  ?>
			<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext_th('View album:'); ?> <?php echo getAnnotatedAlbumTitle(); ?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
			<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext_th('View album:'); ?> <?php echo getAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a>
			<?php printAlbumDate(""); ?>
			<?php printAlbumDesc(); // the album description?>
			<?php printAddToFavorites($_zp_current_album, '', gettext_th('Remove')); // button to remove itmes from favorites ?>
		<?php endwhile; ?>
		<?php while (next_image()): // the loop of the image within the album  ?>
			<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo getBareImageTitle(); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
			<?php printAddToFavorites($_zp_current_image, '', gettext_th('Remove')); // button to remove itmes from favorites?>
		<?php endwhile; ?>
		<?php printPageListWithNav("« " . gettext_th("prev"), gettext_th("next") . " »"); ?>
		<?php printTags('links', gettext_th('<strong>Tags:</strong>') . ' ', 'taglist', ''); ?>
		<?php if (class_exists('RSS')) printRSSLink('Gallery', '', 'RSS', ' | '); ?>
		<?php printZenphotoLink(); ?>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>