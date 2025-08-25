<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
if (class_exists('CMS')) {
	$npgHome = strtolower(stripSuffix($_CMS_current_page->getTitleLink())) == 'npghome';
	?>
	<!DOCTYPE html>
	<html<?php i18n::htmlLanguageCode(); ?>>
		<head>
			<?php npgFilters::apply('theme_head'); ?>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<?php
			scriptLoader($_themeroot . '/style.css');
			jqm_loadScripts();
			if (class_exists('RSS')) {
				if ($npgHome) {
					printRSSHeaderLink('Gallery', 'netPhotoGraphics');
				} else {
					printRSSHeaderLink("Pages", "CMS pages", "");
				}
			}
			?>
		</head>

		<body>
			<?php npgFilters::apply('theme_body_open'); ?>

			<div data-role="page" id="mainpage">
				<?php jqm_printMainHeaderNav(); ?>

				<div class="ui-content" role="main">
					<img src="<?php echo $_gallery->getSiteLogo(); ?>" />
					<br />
					<?php echo gettext('A multi-media oriented Content Management System'); ?>
					<br /><br />
					<div class="content-primary">
						<?php
						if (!$npgHome) {
							?>
							<h2 class="breadcrumb"><a href="<?php echo getPagesLink(); ?>"><?php echo gettext('Pages'); ?></a> <?php
								printZenpageItemsBreadcrumb('', '  ');
								printPageTitle('');
								?></strong></h2>
							<?php
						}

						printPageContent();
						printCodeblock(2);
						$subpages = $_CMS_current_page->getPages();
						if ($subpages) {
							?>
							<ul data-role="listview" data-inset="true" data-theme="a" class="ui-listview ui-group-theme-a">
								<?php
								foreach ($subpages as $subpage) {
									$obj = new Page($subpage['titlelink']);
									?>
									<li><a href="<?php echo html_encode($obj->getLink()); ?>" title="<?php echo html_encode($obj->getTitle()); ?>"><?php echo html_encode($obj->getTitle()); ?></a></li>
									<?php
								}
								?>
							</ul>
							<?php
						}
						printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', ');
						?>
						<?php
						if (function_exists('printCommentForm')) {
							printCommentForm();
						}
						?>
					</div>
					<div class="content-secondary">
						<?php jqm_printMenusLinks(); ?>
					</div>
				</div><!-- /content -->
				<?php jqm_printBacktoTopLink(); ?>
				<?php jqm_printFooterNav(); ?>
			</div><!-- /page -->
	</body>
	<?php npgFilters::apply('theme_body_close'); ?>
	</html>
	<?php
} else {
	include(CORE_SERVERPATH . '404.php');
}
?>