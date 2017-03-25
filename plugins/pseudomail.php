<?php

/**
 * Pseudo mailing handler for localhost testing
 *
 * A "mail" file named by the <i>subject</i> is created in the <var>%DATA_FOLDER%</var> folder. Multiple mailings with the
 * same <i>subject</i> will overwrite.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage mail
 * @category ZenPhoto20Tools
 * Copyright Stephen L Billard
 * permission granted for use in conjunction with ZenPhoto20. All other rights reserved
 */
// force UTF-8 Ø
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Pseudo mailing handler for localhost testing.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (zp_has_filter('sendmail') && !extensionEnabled('pseudomail')) ? sprintf(gettext('Only one Email handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), stripSuffix(get_filterScript('sendmail'))) : '';

if ($plugin_disable) {
	enableExtension('pseudomail', 0);
} else {
	zp_register_filter('sendmail', 'pseudo_sendmail');
}

function pseudo_sendmail($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $bcc_addresses, $replyTo, $html = false) {
	$filename = str_replace(array('<', '>', ':', '"' . '/' . '\\', '|', '?', '*'), '_', $subject);
	$path = SERVERPATH . '/' . DATA_FOLDER . '/' . $filename;
	if ($html) {
		$suffix = '.htm';
		$newln = '<br />';
	} else {
		$suffix = '.txt';
		$newln = "\n";
	}
	$filelist = safe_glob($path . '*' . $suffix);
	$mod = count($filelist);
	if ($mod) {
		$mod = '_' . $mod;
	} else {
		$mod = '';
	}

	$f = fopen($path . $mod . $suffix, 'w');
	fwrite($f, str_pad('*', 49, '-') . $newln);
	$tolist = '';
	foreach ($email_list as $to) {
		$tolist .= ',' . $to;
	}
	fwrite($f, sprintf(gettext('To: %s'), substr($tolist, 1)) . $newln);
	fwrite($f, sprintf('From: %1$s <%2$s>', $from_name, $from_mail) . $newln);
	if ($replyTo) {
		$names = array_keys($replyTo);
		fwrite($f, sprintf('Reply-To: %1$s <%2$s>', array_shift($names), array_shift($replyTo)) . $newln);
	}
	if (count($cc_addresses) > 0) {
		$cclist = '';
		foreach ($cc_addresses as $cc_name => $cc_mail) {
			$cclist .= ',' . $cc_mail;
		}
		fwrite($f, sprintf(gettext('Cc: %s'), substr($cclist, 1)) . $newln);
	}
	if (count($bcc_addresses) > 0) {
		$bcclist = '';
		foreach ($bcc_addresses as $bcc_name => $bcc_mail) {
			$bcclist .= ',' . $bcc_mail;
		}
		fwrite($f, sprintf(gettext('Cc: %s'), substr($bcclist, 1)) . $newln);
	}
	fwrite($f, sprintf(gettext('Subject: %s'), $subject) . $newln);
	fwrite($f, str_pad('*', 49, '-') . $newln);
	fwrite($f, $message . $newln);
	fclose($f);
	clearstatcache();
	return $msg;
}

?>