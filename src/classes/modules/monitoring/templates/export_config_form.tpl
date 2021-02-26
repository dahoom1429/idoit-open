<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MONITORING"}]</h2>

[{isys type="f_text" p_bInvisible=true p_bInfoIconSpacer=0 name="config_id"}]

<div id="monitoring_config">
	<fieldset class="overview">
		<legend><span>[{isys type="lang" ident="LC__MONITORING__EXPORT__CONFIGURATION_EDIT"}]</span></legend>

		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__UI_TITLE" name="C__MONITORING__CONFIG__TITLE"}]</td>
				<td class="value">[{isys type="f_text" name="C__MONITORING__CONFIG__TITLE"}]</td>
			</tr>
			<tr>
				<td colspan="2"><hr /></td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__EXPORT_PATH" name="C__MONITORING__CONFIG__PATH"}]</td>
				<td class="value">
					[{isys type="f_text" name="C__MONITORING__CONFIG__PATH"}]
					<br class="cb" />
					<strong class="ml20 text-red">* [{isys type="lang" ident="LC__MONITORING__EXPORT_PATH_WARNING"}]</strong>
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__MONITORING_ADDRESS" name="C__MONITORING__CONFIG__ADDRESS"}]</td>
				<td class="value">
					[{isys type="f_text" name="C__MONITORING__CONFIG__ADDRESS"}]
					<br class="cb" />
					<span class="ml20 text-grey">* [{isys type="lang" ident="LC__MONITORING__MONITORING_ADDRESS_INFO"}]</span>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset class="overview mt10">
		<legend><span>[{isys type="lang" ident="LC__MONITORING__EXPORT__CONFIGURATION_OPTIONS"}]</span></legend>

		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__MONITORING_TYPE" name="C__MONITORING__MONITORING_TYPE"}]</td>
				<td class="value">[{isys type="f_dialog" name="C__MONITORING__MONITORING_TYPE"}]</td>
			</tr>
			<tr class="monitoring check_mk">
				<td colspan="2">
					<hr class="mt5 mb5" />
				</td>
			</tr>
			<tr class="monitoring check_mk">
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__CHECK_MK__ROLE_EXPORT" name="C__MONITORING__CHECK_MK__ROLE_EXPORT" description="LC__MONITORING__CHECK_MK__ROLE_EXPORT_DESCRIPTION"}]</td>
				<td class="value">[{isys type="f_dialog_list" name="C__MONITORING__CHECK_MK__ROLE_EXPORT"}]</td>
			</tr>
			<tr class="monitoring check_mk">
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__CHECK_MK__SITE" name="C__MONITORING__CHECK_MK__SITE"}]</td>
				<td class="value">[{isys type="f_text" name="C__MONITORING__CHECK_MK__SITE"}]</td>
			</tr>
			<tr class="monitoring check_mk">
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__CHECK_MK__MULTISITE" name="C__MONITORING__CHECK_MK__MULTISITE"}]</td>
				<td class="value">[{isys type="f_dialog" name="C__MONITORING__CHECK_MK__MULTISITE"}]</td>
			</tr>
			<tr class="monitoring check_mk">
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__CHECK_MK__LOCK_HOSTS" name="C__MONITORING__CHECK_MK__LOCK_HOSTS"}]</td>
				<td class="value">[{isys type="f_dialog" name="C__MONITORING__CHECK_MK__LOCK_HOSTS"}]</td>
			</tr>
			<tr class="monitoring check_mk">
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__CHECK_MK__LOCK_FOLDERS" name="C__MONITORING__CHECK_MK__LOCK_FOLDERS"}]</td>
				<td class="value">[{isys type="f_dialog" name="C__MONITORING__CHECK_MK__LOCK_FOLDERS"}]</td>
			</tr>
			<tr class="monitoring check_mk">
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__CHECK_MK__MASTER_SITE" name="C__MONITORING__CHECK_MK__MASTER_SITE"}]</td>
				<td class="value">[{isys type="f_dialog" name="C__MONITORING__CHECK_MK__MASTER_SITE"}]</td>
			</tr>
			[{if count($config_childs)}]
			<tr class="monitoring check_mk">
				<td class="key vat">[{isys type="lang" ident="LC__MONITORING__CHECK_MK__MASTER_SITE_OF"}]</td>
				<td class="value">
					<ul class="m0 ml20 list-style-none">
						[{foreach $config_childs as $child}]
						<li class="mb5">[{$child.isys_monitoring_export_config__title}]</li>
						[{/foreach}]
					</ul>
				</td>
			</tr>
			[{/if}]
			<tr class="monitoring check_mk">
				<td class="key">[{isys type="f_label" ident="LC__MONITORING__CHECK_MK__UTF8DECODE_EXPORT" name="C__MONITORING__CHECK_MK__UTF8DECODE_EXPORT"}]</td>
				<td class="value">[{isys type="f_dialog" name="C__MONITORING__CHECK_MK__UTF8DECODE_EXPORT"}]</td>
			</tr>
		</table>
	</fieldset>
</div>

<script type="text/javascript">
	(function(){
		"use strict";

		var $table = $('monitoring_config'),
			$typeSelection = $('C__MONITORING__MONITORING_TYPE'),
			$multisiteSelection = $('C__MONITORING__CHECK_MK__MULTISITE'),
			$lockHostsSelection = $('C__MONITORING__CHECK_MK__LOCK_HOSTS'),
			$lockFoldersSelection = $('C__MONITORING__CHECK_MK__LOCK_FOLDERS'),
			$masterSelection = $('C__MONITORING__CHECK_MK__MASTER_SITE'),
			$configPathInput = $('C__MONITORING__CONFIG__PATH'),
			$configAddressInput = $('C__MONITORING__CONFIG__ADDRESS');

		if ($typeSelection) {
			$typeSelection.on('change', function () {
				$table.select('.monitoring').invoke('hide');

				$table.select('.' + $typeSelection.getValue()).invoke('show');
			});

			$typeSelection.simulate('change');
		}

		if ($multisiteSelection && $masterSelection) {
			$masterSelection.on('change', function () {
				if ($masterSelection.getValue() > 0) {
					$multisiteSelection.setValue(1).simulate('change');

					$lockHostsSelection.disable();
					$lockFoldersSelection.disable();
					$configPathInput.disable();
					$configAddressInput.disable();
				} else {
					$lockHostsSelection.enable();
					$lockFoldersSelection.enable();
					$configPathInput.enable();
					$configAddressInput.enable();
				}
			});

			$multisiteSelection.on('change', function () {
				if ($multisiteSelection.getValue() == '0') {
					$masterSelection.setValue('-1').simulate('change');
				}
			});

			$masterSelection.simulate('change');
		}
	})();
</script>