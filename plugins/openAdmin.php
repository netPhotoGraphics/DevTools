<?php
/*
 * Use this plugin to allow anonymous visitors to "experience" the administrative
 * pages of a ZenPhoto20 installation.
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
 * @pluginCategory ZenPhoto20
 * @category ZenPhoto20Tools
 *
 * Copyright 2018 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */

// force UTF-8 Ø

$plugin_is_filter = 1000 | FEATURE_PLUGIN;
$plugin_description = gettext("Allow visitors to view the ZenPhoto20 Administrative pages.");

if (!(zp_loggedin() || isset($_GET['userlog']) && $_GET['userlog'] == 0)) {
	zp_register_filter('admin_allow_access', 'openAdmin::access');
	zp_register_filter('admin_head', 'openAdmin::head');
	zp_register_filter('theme_body_close', 'openAdmin::close');
	$master = $_zp_authority->getMasterUser();
	$_zp_current_admin_obj = new openAdmin('Visitor', true, $master->getID());
	$_zp_current_admin_obj->setRights($master->getRights());
	$_zp_loggedin = $_zp_current_admin_obj->getRights();
	if (OFFSET_PATH) {
		zp_register_filter('database_query', 'openAdmin::query');
		zp_register_filter('admin_note', 'openAdmin::notice');
		if (isset($_GET['action'])) {
			$allowedActions = array('save', 'sorttags', 'sortorder', 'saveoptions', 'external');
			if (!in_array($_GET['action'], $allowedActions)) {
				$_GET['action'] = 'NULL'; // block the action
			}
		}
	}
}

class openAdmin extends _Administrator {

	function __construct($user, $valid, $id) {
		$template = new _administrator('', 1, false);
		$data = $template->getData();
		foreach ($data as $key => $value) {
			$this->set($key, NULL);
		}
		$this->setUser($user);
		$this->setName('Site ' . $user);
		$this->setEmail($user . '@zenphoto20.com');
		$this->exists = true;
		$this->valid = $valid;
		$this->set('id', $id);
	}

	static function setAdmin() {
		global $_zp_current_admin_obj, $_zp_authority;
		$masterid = $_zp_current_admin_obj->getID();
		$_zp_authority->admin_all[$masterid] = $_zp_current_admin_obj->getData();
		$_zp_authority->admin_users[$masterid] = $_zp_current_admin_obj->getData();
	}

	static function access($allow, $url) {
		global $zenphoto_tabs;

		self::setAdmin();

		unset($zenphoto_tabs['logs']['subtabs']['security']);
		if (empty($zenphoto_tabs['logs']['subtabs'])) {
			$zenphoto_tabs['logs']['link'] = WEBPATH . '/' . ZENFOLDER . '/admin-logs.php?page=logs';
			$zenphoto_tabs['logs']['default'] = NULL;
		} else {
			$zenphoto_tabs['logs']['default'] = $default = current(array_keys($zenphoto_tabs['logs']['subtabs']));
			$zenphoto_tabs['logs']['link'] = $zenphoto_tabs['logs']['subtabs'][$default];
		}

		foreach ($zenphoto_tabs['upload']['subtabs'] as $key => $link) {
			if (strpos($link, '/elFinder/') !== false) {
				unset($zenphoto_tabs['upload']['subtabs'][$key]);
				break;
			}
		}
		if (empty($zenphoto_tabs['upload']['subtabs'])) {
			unset($zenphoto_tabs['upload']);
		} else {
			$zenphoto_tabs['upload']['default'] = $default = current(array_keys($zenphoto_tabs['upload']['subtabs']));
			$zenphoto_tabs['upload']['link'] = $zenphoto_tabs['upload']['subtabs'][$default];
		}

		$allowedDebugTabs = array('tokens', 'locale', 'http', 'checkdeprecated', 'rewrite', 'macros', 'filters', 'locale', 'deprecated');
		foreach ($zenphoto_tabs['development']['subtabs'] as $key => $link) {
			preg_match('~tab=(.*)~', $link, $matches);
			if (!in_array($matches[1], $allowedDebugTabs)) {
				unset($zenphoto_tabs['development']['subtabs'][$key]);
			}
		}
		if (empty($zenphoto_tabs['development']['subtabs'])) {
			unset($zenphoto_tabs['development']);
		} else {
			$zenphoto_tabs['development']['default'] = $default = current(array_keys($zenphoto_tabs['development']['subtabs']));
			$zenphoto_tabs['development']['link'] = $zenphoto_tabs['development']['subtabs'][$default];
		}
		return $allow;
	}

	static function head() {
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			window.addEventListener('load', function () {
				$("#file_upload_datum").attr("action", "#");	// disable uploads
				$(".overview_utility_buttons").attr("action", "#");
				$("#admin_logout").attr("href", "<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php?userlog=0");
			}, false);
			// ]]> -->
		</script>
		<?php
	}

	static function close() {

		self::setAdmin();
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			window.addEventListener('load', function () {

			}, false);
			// ]]> -->
		</script>
		<?php
	}

	static function query($result, $sql) {
		$action = substr($sql, 0, strpos($sql, ' '));
		switch (strtolower($action)) {
			case 'select':
			case 'show':
			case 'use':
			case 'describe':
			case 'set':
				//	"read" type commands let it pass
				return $result;
				break;
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

}
