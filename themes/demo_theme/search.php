<?php
/**
 * Search page extended for search results of Zenpage CMS items
 */
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
		<?php printRSSHeaderLink('Gallery', gettext_th('Gallery RSS')); ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<?php printGalleryTitle(); ?>
		<?php
		$zenpage = getOption('zp_plugin_zenpage');
		$numimages = getNumImages();
		$numalbums = getNumAlbums();
		$total = $numimages + $numalbums;
		if ($zenpage && !isArchive()) {
			$numpages = getNumPages();
			$numnews = getNumNews();
			$total = $total + $numnews + $numpages;
		} else {
			$numpages = $numnews = 0;
		}
		if ($total == 0) {
			$_zp_current_search->clearSearchWords();
		}
		if (getOption('Allow_search')) {
			// this is special code to allow category specific searches+results. It is actually optional.
			$categorylist = $_zp_current_search->getCategoryList();
			if (is_array($categorylist)) {
				$catlist = array('news' => $categorylist, 'albums' => '0', 'images' => '0', 'pages' => '0');
				printSearchForm(NULL, 'search', NULL, gettext_th('Search category'), NULL, NULL, $catlist);
			} else {
				// this is special code to allow album specific searches+results. It is actually optional.
				$albumlist = $_zp_current_search->getAlbumList(); // this is special code to allow category specific searches+results
				if (is_array($albumlist)) {
					$album_list = array('albums' => $albumlist, 'pages' => '0', 'news' => '0');
					printSearchForm(NULL, 'search', NULL, gettext_th('Search album'), NULL, NULL, $album_list);
				} else {
					// this part is the only required part
					printSearchForm("", "search", "", gettext_th("Search gallery"));
				}
			}
		}
		?>
		<a href="<?php echo getGalleryIndexURL(false); ?>" title="<?php gettext_th('Index'); ?>"><?php echo gettext_th("Index"); ?></a> &raquo; <?php echo "<strong>" . gettext_th("Search") . "</strong>"; ?>
		<?php
		$searchwords = getSearchWords();
		$searchdate = getSearchDate();
		if (!empty($searchdate)) {
			if (!empty($searchwords)) {
				$searchwords .= ": ";
			}
			$searchwords .= $searchdate;
		}
		if ($total > 0) {
			printf(ngettext_th('%1$u Hit for <em>%2$s</em>', '%1$u Hits for <em>%2$s</em>', $total), $total, html_encode($searchwords));
		}
		if ($_zp_page == 1) { //test of zenpage searches
			if ($numpages > 0) {
				$number_to_show = 5;
				$c = 0;
				printf(gettext_th('Pages (%s)'), $numpages);
				printZDSearchShowMoreLink("pages", $number_to_show);
				while (next_page()) {
					$c++;
					printZDToggleClass('pages', $c, $number_to_show);
					printPageTitlelink();
					shortenContent(strip_tags(getPageContent()), 80, getOption("zenpage_textshorten_indicator"));
				}
			}
			if ($numnews > 0) {
				$number_to_show = 5;
				$c = 0;
				printf(gettext_th('Articles (%s)'), $numnews);
				printZDSearchShowMoreLink("news", $number_to_show);
				while (next_news()) {
					printNewsTitleLink();
					echo shortenContent(strip_tags(getNewsContent()), 80, getOption("zenpage_textshorten_indicator"));
				}
			}
		}
		if (getOption('search_no_albums')) {
			if (!getOption('search_no_images') && ($numpages + $numnews) > 0) {
				printf(gettext_th('Images (%s)'), $numimages);
			}
		} else {
			if (getOption('search_no_images')) {
				if (($numpages + $numnews) > 0) {
					printf(gettext_th('Albums (%s)'), $numalbums);
				}
			} else {
				printf(gettext_th('Albums (%1$s) &amp; Images (%2$s)'), $numalbums, $numimages);
			}
		}
		if (getNumAlbums() != 0) {
			while (next_album()):
				?>
				<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext_th('View album:'); ?> <?php echo getBareAlbumTitle(); ?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, 95, 95, 95, 95); ?></a>
				<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext_th('View album:'); ?> <?php echo getBareAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a>
				<?php
				printAlbumDate("");
				echo truncate_string(getAlbumDesc(), 45);
			endwhile;
		}
		if (getNumImages() > 0) {
			while (next_image()) {
				?>
				<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo getBareImageTitle(); ?>"><?php printImageThumb(getBareImageTitle()); ?></a>
				<?php
			}
		}
		if ($total == 0) {
			echo "<p>" . gettext_th("Sorry, no matches found. Try refining your search.") . "</p>";
		}
		printPageListWithNav("&laquo; " . gettext_th("prev"), gettext_th("next") . " &raquo;");
		printAdminToolbox();
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>