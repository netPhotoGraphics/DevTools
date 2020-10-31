<?php

/**
 * Pseudo mailing handler for localhost testing
 *
 * A "mail" file named by the <i>subject</i> is created in the <var>%DATA_FOLDER%</var> folder. Multiple mailings with the
 * same <i>subject</i> will overwrite.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/pseudomail

 * @pluginCategory mail
 * @Copyright Stephen L Billard
 * permission granted for use in conjunction with netPhotoGraphics. All other rights reserved
 */
// force UTF-8 Ø

$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Pseudo mailing handler for localhost testing.");
$plugin_disable = npgFunctions::pluginDisable(array(
						array(empty(getOption('site_email')), gettext('The general option "Email"—used as the "From" address for all mails sent by the gallery—must be set.')),
						array(npgFilters::has_filter('sendmail') && !extensionEnabled('pseudomail'), sprintf(gettext('Only one Email handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), stripSuffix(npgFilters::script('sendmail'))))
				));

if ($plugin_disable) {
	enableExtension('pseudomail', 0);
} else {
	npgFilters::register('sendmail', 'pseudo_sendmail');
}

function pseudo_sendmail($result, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $bcc_addresses, $replyTo) {
	$filename = str_replace(array('<', '>', ':', '"' . '/' . '\\', '|', '?', '*'), '_', $subject);
	$path = SERVERPATH . '/' . DATA_FOLDER . '/' . $filename;
	$filelist = safe_glob($path . '*.htm');
	$mod = count($filelist);
	if ($mod) {
		$mod = '[' . $mod . ']';
	} else {
		$mod = '';
	}

	$f = fopen($path . $mod . '.htm', 'w');
	fwrite($f, '<div style="width: 800px;">');
	fwrite($f, '<fieldset><legend>' . gettext('Mail header') . "</legend><br  />\n");
	$tolist = '';
	foreach ($email_list as $name => $email) {
		if ($name) {
			$email = '"' . $name . '" &lt;' . $email . '&gt;';
		}
		$tolist .= ',' . $email;
	}
	fwrite($f, sprintf(gettext('To: %s'), substr($tolist, 1)) . "<br  />\n");
	fwrite($f, sprintf('From: %1$s &lt;%2$s&gt;', $from_name, $from_mail) . "<br  />\n");
	if ($replyTo) {
		$names = array_keys($replyTo);
		fwrite($f, sprintf('Reply-To: %1$s <%2$s>', array_shift($names), array_shift($replyTo)) . "<br  />\n");
	}
	if (count($cc_addresses) > 0) {
		$cclist = '';
		foreach ($cc_addresses as $name => $email) {
			if ($name) {
				$email = '"' . $name . '" &lt;' . $email . '&gt;';
			}
			$cclist .= ',' . $email;
		}
		fwrite($f, sprintf(gettext('Cc: %s'), substr($cclist, 1)) . "<br  />\n");
	}
	if (count($bcc_addresses) > 0) {
		$bcclist = '';
		foreach ($bcc_addresses as $name => $email) {
			if ($name) {
				$email = '"' . $name . '" &lt;' . $email . '&gt;';
			}
			$bcclist .= ',' . $email;
		}
		fwrite($f, sprintf(gettext('Bcc: %s'), substr($bcclist, 1)) . "<br  />\n");
	}
	fwrite($f, sprintf(gettext('Subject: %s'), $subject) . "<br  />\n");
	fwrite($f, '</fieldset>' . "<br  />\n");

	fwrite($f, $message . "\n");
	fwrite($f, '</div>');
	fclose($f);
	clearstatcache();
	return $result;
}

?>