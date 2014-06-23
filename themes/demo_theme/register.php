<?php
/**
 * Custom theme page to display a user registration form if the register_user plugin is enabled
 */
if (!defined('WEBPATH'))
	die();
if (function_exists('printRegistrationForm')) {
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
			<?php echo gettext_th('User Registration') ?>
			<?php printRegistrationForm(); ?>
			<?php printZenphotoLink(); ?>
			<?php zp_apply_filter('theme_body_close'); ?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>