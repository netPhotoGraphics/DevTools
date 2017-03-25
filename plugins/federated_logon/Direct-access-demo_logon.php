<?php
/*
 * Demo script for co-ordinating credentials with some outside agency.
 *
 */

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/zp-core/admin-functions.php');
if (isset($_GET['redirect'])) {
	$redirect = sanitizeRedirect($_GET['redirect']);
} else {
	$redirect = '';
}
if (isset($_GET['user'])) {
	$user = sanitize($_GET['user']);
} else if (isset($_GET['requestor'])) {
	$user = sanitize($_GET['requestor']);
} else {
	$user = '';
}
if (isset($_GET['email'])) {
	$email = sanitize($_GET['email']);
} else {
	$email = '';
}

$mypath = replaceScriptPath(__FILE__, WEBPATH);
$more = '';
if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'login':
			if (empty($user)) {
				if (empty($email)) {
					$more = gettext('You must supply a user name or an e-mail address.');
					break;
				} else {
					$user = $email;
				}
			}
			if (Zenphoto_Authority::checkCookieCredentials()) {
				if ($_zp_current_admin_obj->getUser() == $user) {
					$more = sprintf(gettext('%s is already logged in.'), $user);
					break;
				} else {
					$more = gettext('Someone else is logged in.');
					break;
				}
			}
			$more = federated_logon::credentials($user, $email, NULL, $redirect);
			break;
	}
}
header('Last-Modified: ' . ZP_LAST_MODIFIED);
header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo gettext('Refered credentials proof of concept'); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<style>
			.warningbox {
				padding: 20px;
				background-color: #FFFF00;
				border-top: 1px solid #996600;
				border-left: 1px solid #996600;
				border-right: 1px solid #996600;
				border-bottom: 5px solid #996600;
				margin-bottom: 10px;
				font-size: 100%;
			}
			#main {
				width: 30em;
			}
		</style>
		<script type="text/javascript">
			function submitUser(action) {
				var user = document.getElementById('user').value;
				var email = document.getElementById('email').value;
				var parms = '<?php echo $mypath; ?>?' + action + '&user=' + user + '&email=' + email;

<?php
if ($redirect) {
	?>
					parms = parms + '&redirect=<?php echo $redirect; ?>';
	<?php
}
?>
				window.location = parms;
			}

		</script>
	</head>

	<body>

		<div id="main">

			<div id="gallerytitle">
				<h2><?php echo gettext("Federated logon proof of concept."); ?></h2>
			</div>
			<p class="warningbox"><?php echo gettext('<strong>WARNING:</strong> this demonstration is NOT secure. You should not leave this script on any public installation.'); ?></p>
			<div id="padbox">
				<form id="password" name="password" action="" method="post" >
					user: <input type="text" name="user" id="user" value="<?php echo html_encode($user); ?>" />
					e-mail: <input type="text" name="email" id="email" value="<?php echo html_encode($email); ?>" />
					<input type="button" name="action" value="verify" onclick="submitUser('action=login');"></input>
				</form>
			</div>
			<p>
				<?php
				if (isset($_GET['action'])) {
					switch ($_GET['action']) {
						case 'login':
							if ($more) {
								printf(gettext('Not logged in.'), $user) . ' ';
								echo $more;
							} else {
								printf(gettext('%s was successfully logged in'), $user);
							}
							break;
						default:
							printf(gettext('%s is an invalid action.'), $action);
							break;
					}
				}
				?>
			</p>
		</div>

	</body>
</html>