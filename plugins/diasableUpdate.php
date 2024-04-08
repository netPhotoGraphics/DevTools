<?php
/**
 * Disable the release install buttons
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/disableUpdate
 * @pluginCategory development
 */
$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN);
$plugin_description = gettext('Disables the release install button(s) to prevent accidentally overriding development code.');

npgFilters::register('admin_close', 'DisableUpdate');

function DisableUpdate() {
	?>
	<script type="text/javascript">

		var buttons = [
			'download_update',
			'download_Dev_update',
			'install_update'
		];
		var messages = [
			'<?php echo gettext('Abort download and installing this release?'); ?>',
			'<?php echo gettext('Abort download and installing this Development release?'); ?>',
			'<?php echo gettext('Abort installing this release?'); ?>'
		];

		function editButton(item, index) {
			$('#' + item + ' .font_icon').replaceWith('<?php echo PROHIBITED; ?>');
			$('#' + item).submit(function (e) {
				if (confirm(messages[index])) {
					e.preventDefault(e);
				}
			}
			)
		}
		buttons.forEach(editButton);

	</script>
	<?php
}
