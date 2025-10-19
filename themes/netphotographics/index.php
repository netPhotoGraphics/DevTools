<?php

// force UTF-8 Ø
if (class_exists('CMS')) {
	$zenpage = true;
	$imagereduction = 1 / 2;
	Controller::load_zenpage_pages('npgHome.htm');
	require('pages.php');
} else {
	require('gallery.php');
}
?>