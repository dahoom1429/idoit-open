<div id="duplicate_popup">
	<h3 class="popup-header">
		<span class="fr mouse-pointer popup-closer">&times;</span>
		[{isys type='lang' ident='LC__MODULE__JDISC__DUPLICATE_PROFILES'}]
	</h3>

	[{if $error}]
		<div id="msgbox_error" class="popup-content p5 bg-red">[{$error}]</div>
	[{else}]
		<div class="popup-content">
			<table class="contentTable">
				<thead>
					<tr>
						<th class="key bold pb15" style="text-align: right;">[{isys type='lang' ident='LC__MODULE__JDISC__ORIGINAL_NAME'}]</th>
						<th class="value pb15 pl20">[{isys type='lang' ident='LC__MODULE__JDISC__NEW_NAME'}]</th>
					</tr>
				</thead>
				<tbody>
					[{foreach item=profile from=$profiles}]
					<tr>
						<td class="key vat">[{isys type='f_label' name=$profile.id ident=$profile.title}]</td>
						<td class="value">[{isys type='f_text' name=$profile.id p_strValue=$profile.title p_strClass="input-small"}]</td>
					</tr>
					[{/foreach}]
				</tbody>
			</table>
		</div>
	[{/if}]

	<div class="popup-footer">
		[{if !$error}]
		[{isys
			name="save_button"
			type="f_button"
            p_strClass="mr5"
			p_strValue="LC__NAVIGATION__NAVBAR__DUPLICATE"
			icon="`$dir_images`icons/silk/tick.png"}]
		[{/if}]

		[{isys
			name="cancel_button"
			type="f_button"
			p_strClass="popup-closer"
			p_strValue="LC__UNIVERSAL__BUTTON_CANCEL"
			icon="`$dir_images`icons/silk/cross.png"}]
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $duplicate_popup = $('duplicate_popup'),
			$save_button = $('save_button');

		if($save_button)
		{
			$save_button.on('click', function () {
				$save_button
						.disable()
						.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
						.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

				$('navMode').setValue('[{$smarty.const.C__NAVMODE__DUPLICATE}]');
				$('isys_form').submit();
			});
		}

		$duplicate_popup.on('click', '.popup-closer', function () {
			popup_close();
		});
	})();
</script>
