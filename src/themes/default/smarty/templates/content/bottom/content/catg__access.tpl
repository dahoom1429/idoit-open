<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCESS_TITLE" ident="LC__CMDB__CATG__ACCESS_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__ACCESS_TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCESS_TYPE" ident="LC__CMDB__CATG__ACCESS_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__ACCESS_TYPE" p_strTable="isys_access_type"}]</td>
	</tr>
	<tr>
		<td class="key vat">[{isys type="f_label" name="C__CATG__ACCESS_URL" ident="LC__CMDB__CATG__ACCESS_URL"}]</td>
		<td class="value">
			<div class="ml20 [{if isys_glob_is_edit_mode()}]input-group input-size-normal[{/if}]">
	            [{isys type="f_link" name="C__CATG__ACCESS_URL"}]
				[{if isys_glob_is_edit_mode()}]
				<div class="input-group-addon input-group-addon-clickable">
					<img src="[{$dir_images}]icons/silk/help.png" onclick="Effect.toggle('accessPlaceholders', 'slide', {duration:0.25});" />
				</div>
				[{/if}]
			</div>

			<br class="cb" />

			<div id="accessPlaceholders" class="box-white ml20 mt5 overflow-auto mouse-pointer input-size-normal" style="display:none;height:200px;">
				<table class="listing hover" style="border:none;">
					[{foreach from=$accessPlaceholders item="plholder" key="plkey"}]
						<tr>
							<td>
								<code>[{$plkey}]</code>
							</td>
							<td>
								[{$plholder}]
							</td>
						</tr>
					[{/foreach}]
				</table>
			</div>
        </td>
	</tr>
	<tr>
        <td class="key">[{isys type="f_label" name="C__CATG__ACCESS_PRIMARY" ident="LC__CMDB__CATG__ACCESS_PRIMARY"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATG__ACCESS_PRIMARY" p_bDbFieldNN="1"}]</td>
    </tr>
</table>

<style>
	#accessPlaceholders td {
		border-collapse: collapse;
	}

	#accessPlaceholders td:first-of-type {
		width: 230px;
		text-align: right;
	}
</style>

<script type="text/javascript">
	(function(){
		'use strict';

		var $urlField = $('C__CATG__ACCESS_URL'),
			$placeholderDiv = $('accessPlaceholders');

		if ($urlField && $placeholderDiv) {
			$placeholderDiv.on('click', 'td', function(ev) {
				var $placeholder = ev.findElement('tr').down('code');

				$urlField.setValue($urlField.getValue() + ($placeholder.textContent || $placeholder.innerText || $placeholder.innerHTML));
			});
		}
	})();
</script>