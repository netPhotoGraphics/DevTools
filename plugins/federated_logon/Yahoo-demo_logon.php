<?php

/**
 * Yahoo accounts logon handler.
 *
 * This just supplies the Yahoo URL to OpenID_try.php. The rest is normal OpenID handling
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */
require_once('OpenID_common.php');
zp_session_start();

if (isset($_GET['redirect'])) {
	$redirect = sanitizeRedirect($_GET['redirect']);
} else {
	$redirect = '';
}
$_SESSION['OpenID_redirect'] = $redirect;
//The following three lines are the provider dependent items
$_SESSION['OpenID_cleaner_pattern'] = '/me.yahoo.com\/.*\/(.*)/';
$_SESSION['provider'] = 'Yahoo-demo';
$_GET['openid_identifier'] = 'https://Yahoo.com';

// if the extension included with the standard federation login extensions
// require 'consumer/try_auth.php';
// otherwise we need to redirect to the consumer/try_auth.php script
header('location:' . WEBPATH . '/' . USER_PLUGIN_FOLDER . '/' . '/federated_logon/OpenID_try_auth.php?openid_identifier=' . $_GET['openid_identifier'] . '&action=verify');
exitZP();
?>