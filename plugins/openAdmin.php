<?php
/**
 * Use this plugin to allow anonymous visitors to "experience" the administrative
 * pages of a netPhotoGraphics installation.
 *
 * Any actions which might changes to the state of the installation are suppressed.
 * Some sensitive content will be hidden, for instance the <i>security log</i> and the site
 * <i>master user</i>.
 * But in general, no attempt is made to filter what the user sees, so be careful what
 * plugins you enable.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/openAdmin
 * @pluginCategory netPhotoGraphics
 *
 * @Copyright 2018 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics} and derivatives
 */
// force UTF-8 Ø

$plugin_is_filter = 1000 | FEATURE_PLUGIN;
$plugin_description = gettext("Allow visitors to view the Administrative pages.");

$option_interface = 'openAdmin';

define('OPENADMIN_USER', 'Visitor');

class openAdminAuthority extends _Authority {

	static function setAdmin() {
		global $_current_admin_obj, $_authority;
		$masterid = $_current_admin_obj->getID();
		$_authority->admin_users[$masterid] = $_current_admin_obj->getData();
	}

}

class openAdmin extends _Administrator {

	function __construct($user = NULL, $valid = NULL, $id = NULL) {
		global $_authority;

		if (OFFSET_PATH == 2) {
			setOptionDefault('openAdmin_logging', 0);
		}

		parent::__construct('', 1, false);

		$master = $_authority->getMasterUser();
		$this->setUser($user);
		$this->setName('Site ' . $user);
		$this->exists = true;
		$this->transient = 3; //	in case some one needs to know
		$this->set('id', $this->id = $master->getID());
		$this->set('lastaccess', time());
		$this->set('pass', NULL);
		$this->set('passhash', PASSWORD_FUNCTION_DEFAULT);
		$this->setRights($master->getRights());
		$this->setEmail('visitor@netphotographics.org');
	}

	function setPolicyACK($v) {
		parent::setPolicyACK($v);
		if ($v) {
			setNPGCookie('policyACK', getOption('GDPR_cookie'), FALSE, ['secure' => FALSE]); //	since the object is not persistent
		}
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Log access') => array('key' => 'openAdmin_loging', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Log administrative pages visited.'))
		);
		return $options;
	}

	/**
	 * removes upload capability from tinyMCE
	 *
	 * @global type $MCEspecial
	 */
	static function tinyMCE() {
		global $MCEspecial;
		unset($MCEspecial['images_upload_url']);
		unset($MCEspecial['file_picker_callback']);
	}

	private static function setDefaultLink($tab) {
		global $_admin_menu;
		$_admin_menu[$tab]['default'] = $default = current(array_keys($_admin_menu[$tab]['subtabs']));
		$parts = explode('?', $_admin_menu[$tab]['subtabs'][$default]);
		$link = getAdminLink($parts[0]);
		if (isset($parts[1])) {
			$link .= '?' . $parts[1];
		}
		$_admin_menu[$tab]['link'] = $link;
	}

	static function access($allow, $url) {
		global $_admin_menu, $_current_admin_obj;
		openAdminAuthority::setAdmin();
		if (!(isset($_POST['policy_acknowledge']) && $_POST['policy_acknowledge'] == md5(getUserID() . getOption('GDPR_cookie')))) {
			if (class_exists('GDPR_required') && !policyACKCheck() < getOption('GDPR_cookie')) {
				GDPR_required::page(NULL, NULL);
			}
		}
		setNPGCookie('policyACK', getOption('GDPR_cookie'), FALSE, ['secure' => FALSE]);

		//	limit security logging for "visitor"
		npgFilters::remove('admin_allow_access', 'security_logger::adminGate');
		npgFilters::remove('authorization_cookie', 'security_logger::adminCookie', 0);
		npgFilters::remove('admin_managed_albums_access', 'security_logger::adminAlbumGate');
		npgFilters::remove('save_user_complete', 'security_logger::UserSave');
		npgFilters::remove('admin_XSRF_access', 'security_logger::admin_XSRF_access');
		npgFilters::remove('admin_log_actions', 'security_logger::log_action');
		npgFilters::remove('log_setup', 'security_logger::log_setup');
		npgFilters::remove('security_misc', 'security_logger::security_misc');

		if (isset($_admin_menu['logs']['subtabs'])) {
			//	hide sensitive logs
			foreach ($_admin_menu['logs']['subtabs'] as $subtab) {
				$masterlog = $subtab = substr($subtab, strpos($subtab, 'tab=') + 4);
				$j = strpos($subtab, '-');
				if ($j !== FALSE) {
					$masterlog = substr($subtab, 0, $j);
				}
				switch ($masterlog) {
					case 'security':
					case 'openAdmin':
					case 'debug':
						unset($_admin_menu['logs']['subtabs'][$subtab]);
						unset($_admin_menu['logs']['alert'][$subtab]);
						break;
				}
			}
		}

		if (empty($_admin_menu['logs']['subtabs'])) {
			$_admin_menu['logs']['link'] = getAdminLink('admin-tabs/logs.php') . '?page=logs';
			$_admin_menu['logs']['default'] = NULL;
		} else {
			self::setDefaultLink('logs');
		}
		//	protect against un-monitored uploading
		if (isset($_admin_menu['upload'])) {
			foreach ($_admin_menu['upload']['subtabs'] as $key => $link) {
				if (strpos($link, '/elFinder/') !== false) {
					unset($_admin_menu['upload']['subtabs'][$key]);
					break;
				}
			}
			if (empty($_admin_menu['upload']['subtabs'])) {
				unset($_admin_menu['upload']);
			} else {
				self::setDefaultLink('upload');
			}
		}
		if (isset($_admin_menu['development'])) {
			$allowedDebugTabs = array('tokens', 'locale', 'http', 'checkdeprecated', 'rewrite', 'macros', 'filters', 'locale', 'deprecated');
			foreach ($_admin_menu['development']['subtabs'] as $key => $link) {
				preg_match('~tab=(.*)~', $link, $matches);
				if (!in_array($matches[1], $allowedDebugTabs)) {
					unset($_admin_menu['development']['subtabs'][$key]);
				}
			}
			if (empty($_admin_menu['development']['subtabs'])) {
				unset($_admin_menu['development']);
			} else {
				self::setDefaultLink('development');
			}
		}
		return $allow;
	}

	static function head() {
		global $_get_original;

		if (getOption('openAdmin_logging')) {
			$uri = explode('?', getRequestURI());
			$uri = trim(str_replace(WEBPATH, '', $uri[0]), '/');
			$uri = trim(str_replace(CORE_FOLDER, '', $uri), '/');
			$uri = trim(str_replace(CORE_PATH, '', $uri), '/');
			$uri = trim(str_replace(PLUGIN_FOLDER, '', $uri), '/');
			$uri = trim(str_replace(PLUGIN_PATH, '', $uri), '/');
			self::Logger($uri, @$_get_original['page'], @$_get_original['tab'], @$_get_original['action']);
		}
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			window.addEventListener('load', function () {
				$(".overview_utility_buttons").attr("action", "#");
				$(".overview_utility_buttons .XSRFToken").remove();
				$("#admin_logout").attr("href", "<?php echo getAdminLink('admin.php'); ?>?userlog=0");
				$("#admin_logout").attr("title", "<?php echo gettext('Show admin login form'); ?>");
				$('#login').before('<p class="notebox"><?php echo gettext('Login with valid user credentials to bypass the <em>openAdmin</em> plugin.'); ?></p>');
				$('#auth').remove();	//	disable any auth passing, currently only for uploader stuff
				$('.reconfigbox').remove();	//	remove any reconfigure messages as we don't want the visitor running setup
			}, false);
			// ]]> -->
		</script>
		<?php
	}

	static function close() {
		openAdminAuthority::setAdmin();
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			window.addEventListener('load', function () {
				$("#toolbox_logout").attr("href", "<?php echo getAdminLink('admin.php'); ?>?userlog=0");
				$("#toolbox_logout").attr("title", "<?php echo gettext('Show admin login form'); ?>");
			}, false);
			// ]]> -->
		</script>
		<?php
	}

	static function query($result, $sql) {
		$action = substr($sql, 0, strpos($sql, ' '));
		switch (strtolower($action)) {
			case 'create':
				if (strpos($sql, 'CREATE TEMPORARY TABLE') === 0) {
					return $result; //	getAllTagsUnique needs the temp table
				}
				break;
			case 'select':
			case 'show':
			case 'use':
			case 'describe':
			case 'set':
				//	"read" type commands let it pass
				return $result;
		}
		return true; //	pretend the query was successsful
	}

	static function notice($html) {
		?>

		<div class="notebox">
			<br />
			<strong>
				<?php echo gettext('The administrative pages are available for demonstration purposes only. Actions that would change the state of the installation will be suppressed.');
				?>
			</strong>
			<br />
			<br />
		</div>

		<?php
	}

	static function Logger($link, $page, $tab, $action) {
		global $_authority, $_mutex;
		$ip = sanitize($_SERVER['REMOTE_ADDR']);
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$proxy_list = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
			$forwardedIP = trim(sanitize(end($proxy_list)));
			if ($forwardedIP) {
				$ip .= ' {' . $forwardedIP . '}';
			}
		}

		$file = SERVERPATH . '/' . DATA_FOLDER . '/openAdmin.log';
		$max = getOption('security_log_size'); // we are lazy, we will use this
		$_mutex->lock();
		if (file_exists($file) && $max && filesize($file) > $max) {
			switchLog('openAdmin');
		}
		$preexists = file_exists($file) && filesize($file) > 0;
		$f = fopen($file, 'a');
		if ($f) {
			if (!$preexists) { // add a header
				chmod($file, LOG_MOD);
				fwrite($f, gettext('date' . "\t" . 'requestor’s IP' . "\t" . 'link' . "\t" . 'page' . "\t" . 'tab' . "\t" . 'action' . "\n"));
			}
			$message = date('Y-m-d H:i:s') . "\t";
			$message .= $ip . "\t";
			$message .= $link . "\t";
			$message .= $page . "\t";
			$message .= $tab . "\t";
			$message .= $action;

			fwrite($f, $message . "\n");
			fclose($f);
			clearstatcache();
		}
		$_mutex->unlock();
	}

}

if (!npg_loggedin()) {

	npgFilters::register('admin_head', 'openAdmin::head', 9999);
	npgFilters::register('tinymce_config', 'openAdmin::tinyMCE');
	if (!isset($_GET['fromlogout']) && (!isset($_GET['userlog']) || $_GET['userlog'] != 0)) {
		npgFilters::register('admin_allow_access', 'openAdmin::access', 9999);
		npgFilters::register('theme_body_close', 'openAdmin::close', 9999);
		$_current_admin_obj = new openAdmin(OPENADMIN_USER, 1);
		$_loggedin = $_current_admin_obj->getRights();
		setNPGCookie('user_auth', $_loggedin);
		unset($master);
		if (OFFSET_PATH) {
			$_get_original = $_GET;
			npgFilters::register('database_query', 'openAdmin::query', 9999);
			npgFilters::register('admin_note', 'openAdmin::notice', 9999);
			if (isset($_GET['action'])) {
				$allowedActions = array('save', 'sorttags', 'sortorder', 'saveoptions', 'external');
				if (!in_array($_GET['action'], $allowedActions)) {
					$_GET['action'] = 'NULL'; // block the action
				}
			}
		}
	}
}
