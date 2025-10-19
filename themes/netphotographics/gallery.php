<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html<?php i18n::htmlLanguageCode(); ?>>
	<head>

		<?php
		npgFilters::apply('theme_head');
		if (getOption('netPhotoGraphics_daily_album_image_effect') && getOption('gallery_index')) {
			setOption('image_custom_images', getOption('netPhotoGraphics_daily_album_image_effect'), false);
		}
		?>

		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', 'Gallery RSS'); ?>
	</head>

	<body onload="blurAnchors()">
		<?php npgFilters::apply('theme_body_open'); ?>

		<!-- Wrap Header -->
		<div id="header">

			<!-- Logo -->
			<div id="gallerytitle">
				<div id="logo">
					<?php
					if (getOption('Allow_search')) {
						$album_list = array('albums' => '1', 'pages' => '0', 'news' => '0');
						printSearchForm(NULL, 'search', $_themeroot . '/images/search.png', gettext('Search albums'), NULL, NULL, $album_list);
					}
					printLogo();
					?>
				</div>
			</div> <!-- gallerytitle -->

			<!-- Crumb Trail Navigation -->
			<div id="wrapnav">
				<div id="navbar">
					<span><?php printHomeLink('', ' | '); ?>
						<?php
						if (getOption('gallery_index')) {
							?>
							<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Main Index'); ?>"><?php printGalleryTitle(); ?></a>
							<?php
							echo ' | ' . gettext('Album index');
						}
						?>
					</span>
				</div>
			</div> <!-- wrapnav -->
		</div> <!-- header -->
		<!-- Random Image -->
		<?php
		printHeadingImage(getRandomImages(getOption('netPhotoGraphics_daily_album_image')));
		?>

		<!-- Wrap Main Body -->
		<div id="content">
			<div id="main">

				<!-- Album List -->
				<ul id="albums">
					<?php
					$firstAlbum = null;
					$lastAlbum = null;
					while (next_album()) {
						if (is_null($firstAlbum)) {
							$lastAlbum = albumNumber();
							$firstAlbum = $lastAlbum;
						} else {
							$lastAlbum++;
						}
						?>
						<li>
							<?php $annotate = annotateAlbum(); ?>
							<div class="imagethumb">
								<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo html_encode($annotate); ?>">
									<?php printCustomAlbumThumbImage($annotate, array('width' => ALBUM_THMB_WIDTH, 'cw' => ALBUM_THMB_WIDTH, 'ch' => ALBUM_THUMB_HEIGHT)); ?>
								</a>
							</div>
							<h4><a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo html_encode($annotate); ?>"><?php printBareAlbumTitle(25); ?></a></h4>
						</li>
					<?php } ?>
				</ul>
				<div class="clearage"></div>
				<?php printNofM('Album', $firstAlbum, $lastAlbum, getNumAlbums()); ?>

			</div> <!-- main -->
			<!-- Page Numbers -->
			<div id="pagenumbers">
				<?php printPageListWithNav("« " . gettext('prev'), gettext('next') . " »"); ?>
			</div>
		</div> <!-- content -->

		<br style="clear:all" />

		<?php
		printFooter();
		?>

	</body>
	<?php npgFilters::apply('theme_body_close'); ?>
</html>