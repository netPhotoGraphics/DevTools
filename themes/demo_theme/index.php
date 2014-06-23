<?php
/**
 * The gallery index (home page) of a theme. Usually prints the top level albums
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
		<?php printGalleryTitle(); ?>
		<?php
		if (getOption('Allow_search')) {
			printSearchForm("", "search", "", gettext_th("Search gallery"));
		}
		?>
		<?php printGalleryDesc(); // the main gallery description ?>
		<?php
		if (extensionEnabled('zenpage')) {
			?>
			<h1><?php echo gettext_th('News link'); ?></h1>
			<?php
			printNewsIndexURL();
			?>
			<h1><?php echo gettext_th('Pages'); ?></h1>
			<?php
			printPageMenu();
			?>
			<br />
			<?php
		}
		?>
		<?php while (next_album()): // the loop of the top level albums  ?>
			<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext_th('View album:'); ?> <?php echo getAnnotatedAlbumTitle(); ?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
			<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext_th('View album:'); ?> <?php echo getAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a>
			<?php printAlbumDate(""); ?>
			<?php printAlbumDesc(); ?>
		<?php endwhile; ?>
		<?php printPageListWithNav("« " . gettext_th("prev"), gettext_th("next") . " »"); ?>
		<?php if (class_exists('RSS')) printRSSLink('Gallery', '', 'RSS', ' | '); ?>
		<?php printZenphotoLink(); ?>
		<?php zp_apply_filter('theme_body_close'); ?>
	</body>
</html>