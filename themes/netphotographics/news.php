<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
if (class_exists('CMS')) {
	?>
	<!DOCTYPE html>
	<html<?php i18n::htmlLanguageCode(); ?>>
		<head>

			<?php npgFilters::apply('theme_head'); ?>

			<?php if (class_exists('RSS')) printRSSHeaderLink("News", NEWS_LABEL, ""); ?>
		</head>

		<body onload="blurAnchors()">
			<?php npgFilters::apply('theme_body_open'); ?>
			<!-- Wrap Header -->
			<div id="header">
				<div id="gallerytitle">

					<!-- Logo -->
					<div id="logo">
						<?php
						if (getOption('Allow_search')) {
							if (is_NewsCategory()) {
								$catlist = array('news' => array($_CMS_current_category->getTitlelink()), 'albums' => '0', 'images' => '0', 'pages' => '0');
								printSearchForm(NULL, 'search', $_themeroot . '/images/search.png', gettext('Search category'), NULL, NULL, $catlist, true);
							} else {
								$catlist = array('news' => '1', 'albums' => '0', 'images' => '0', 'pages' => '0');
								printSearchForm(NULL, 'search', $_themeroot . '/images/search.png', gettext('Search'), NULL, NULL, $catlist);
							}
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
							} else {
								?>
								<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a>
								<?php
							}
							?></a></span>
						<?php
						printNewsIndexURL(NULL, ' | ');
						printZenpageItemsBreadcrumb(' | ', '');
						printCurrentNewsCategory(" | ");
						printNewsTitle(" | ");
						printCurrentNewsArchive(" | ");
						?>
					</div>
				</div> <!-- wrapnav -->

				<!-- Random Image -->
				<?php printHeadingImage(getRandomImages(getOption('netPhotoGraphics_daily_album_image'))); ?>
			</div> <!-- header -->

			<!-- Wrap Main Body -->
			<div id="content">

				<small>&nbsp;</small>
				<div id="main2">
					<div id="content-left">
						<?php
						if (is_NewsArticle()) { // single news article
							?>
							<?php if ($prev = getPrevNewsURL()) { ?><div class="singlenews_prev"><?php printPrevNewsLink(); ?></div><?php } ?>
							<?php if ($next = getNextNewsURL()) { ?><div class="singlenews_next"><?php printNextNewsLink(); ?></div><?php } ?>
							<?php if ($prev || $next) { ?><br class="clearall" /><?php } ?>
							<h3><?php printNewsTitle(); ?></h3>

							<div class="newsarticlecredit">
								<span class="newsarticlecredit-left">
									<?php
									if (function_exists('getCommentCount')) {
										$count = getCommentCount();
									} else {
										$count = 0;
									}
									$cat = getNewsCategories();
									printNewsDate();
									if ($count > 0) {
										echo ' | ';
										printf(gettext("Comments: %d"), $count);
									}
									if (!empty($cat)) {
										echo ' | ';
										printNewsCategories(", ", gettext("Categories: "), "newscategories");
									}
									?>
								</span>
								<br />
								<?php printCodeblock(1); ?>
								<?php printNewsContent(); ?>
								<?php printCodeblock(2); ?>
							</div>
							<?php
							commonComment();
						} else { // news article loop
							commonNewsLoop(true);
						}
						?>

					</div><!-- content left-->
					<div id="sidebar">
						<?php include("sidebar.php"); ?>
					</div><!-- sidebar -->
					<br style="clear:both" />
				</div> <!-- main2 -->

			</div> <!-- content -->

			<?php
			printFooter();
			?>

		</body>
		<?php npgFilters::apply('theme_body_close'); ?>
	</html>
	<?php
} else {
	include(CORE_SERVERPATH . '404.php');
}
?>