<?php
// force UTF-8 Ø

rem_context(NPG_ALBUM | NPG_IMAGE);
$archivlinktext = gettext('Gallery');
if (extensionEnabled('npgCMS')) {
	if ($news = hasNews()) {
		$archivlinktext = gettext('Both');
	}
	$pages = hasPages();
} else {
	$news = $pages = NULL;
}

if (function_exists('printCustomMenu') && ($menu = getOption('netPhotoGraphics_menu'))) {
	?>
	<div class="menu">
		<?php
		printCustomMenu($menu, 'list', '', "menu-active", "submenu", "menu-active", 2);
		?>
	</div>
	<?php
} else { //	"standard sidebar menus
	if ($news) {
		?>
		<div class="menu">
			<h3><?php echo NEWS_LABEL; ?></h3>
			<?php printAllNewsCategories(gettext("All"), true, "", "menu-active", true, "submenu", "menu-active"); ?>
			<div class="menu_rule"></div>
		</div>
		<?php
	}
	?>
	<?php
	if (function_exists("printAlbumMenu")) {
		?>
		<div class="menu">
			<?php
			if (extensionEnabled('npgCMS')) {
				if ($_gallery_page == 'index.php' || $_gallery_page != 'gallery.php') {
					?>
					<h3>
						<a href="<?php echo html_encode(getCustomPageURL('gallery')); ?>" title="<?php echo gettext('Album index'); ?>"><?php echo gettext("Gallery"); ?></a>
					</h3>
					<?php
				}
			} else {
				?>
				<h3><?php echo gettext("Gallery"); ?></h3>
				<?php
			}
			printAlbumMenu("list", "count", "album_menu", "menu", "menu_sub", "menu_sub_active", '');
			?>
		</div>
		<?php
	} else {
		if (extensionEnabled('npgCMS')) {
			?>
			<div class="menu">
				<h3><?php echo gettext("Albums"); ?></h3>
				<ul id="album_menu">
					<li>
						<a href="<?php echo html_encode(getCustomPageURL('gallery')); ?>" title="<?php echo gettext('Album index'); ?>"><?php echo gettext('Gallery'); ?></a>
					</li>
				</ul>
			</div>
			<?php
		}
	}
	?>

	<?php
	if ($pages) {
		?>
		<div class="menu">
			<h3><?php echo gettext("Pages"); ?></h3>
			<?php printPageMenu("list", "", "menu-active", "submenu", "menu-active"); ?>
			<div class="menu_rule"></div>
		</div>
		<?php
	}
	?>

	<div class="menu">
		<h3>
			<?php
			if ($_gallery_page == "archive.php") {
				?>
				<?php echo gettext("Archive"); ?>
				<?php
			} else {
				?>
				<?php printCustomPageURL(gettext("Archive"), "archive"); ?>
				<?php
			}
			?>
		</h3>
		<?php
		if (extensionEnabled('daily-summary')) {
			?>
			<h3>
				<?php
				if ($_gallery_page == "summary.php") {
					echo gettext("Daily summary");
				} else {
					printDailySummaryLink(gettext('Daily summary'), '', '', '');
				}
				?>
			</h3>
			<?php
		}
		?>

		<div class="menu_rule"></div>
	</div>

	<?php
	if (class_exists('RSS') && (getOption('RSS_album_image') || getOption('RSS_articles'))) {
		?>
		<div class="menu">
			<h3><?php echo gettext("RSS"); ?></h3>
			<ul>
				<?php
				if (class_exists('RSS')) {
					printRSSLink('Gallery', '<li>', gettext('Gallery'), '</li>');
					if ($news) {
						printRSSLink("News", "<li>", NEWS_LABEL, '</li>');
					}
				}
				?>
			</ul>
		</div>
		<?php
	}
}
?>
