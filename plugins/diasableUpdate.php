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
		$('#download_update').submit(function (e) {
			if (!confirm('<?php echo gettext('Do you really want to download and install this release?'); ?>')) {
				e.preventDefault(e);
			}
		}
		)
		$('#download_Dev_update').submit(function (e) {
			if (!confirm('<?php echo gettext('Do you really want to download and install this Development release?'); ?>')) {
				e.preventDefault(e);
			}
		}
		)
		$('#install_update').submit(function (e) {
			if (!confirm('<?php echo gettext('Do you really want to iistall this release and overwrite this project?'); ?>')) {
				e.preventDefault(e);
			}
		}
		)
	</script>
	<?php
}
