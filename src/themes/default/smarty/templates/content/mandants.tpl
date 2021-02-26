[{if is_array($mandant_options) && count($mandant_options)}]
	<div id="mandants">
		<table cellpadding="2" cellspacing="0" id="loginTable">
			<tr>
				<td class="right">Tenant</td>
				<td>
					<select class="input input-small" name="login_mandant_id">
						[{html_options options=$mandant_options selected=$requiredTenantId}]
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" nowrap="nowrap">
					<input type="hidden" name="mode" value="hypergate" /><br />
					[{isys type="f_button" name="login_submit" p_strClass="btn-large" p_strValue="LC__UNIVERSAL__BUTTON_NEXT" p_bDisabled=0}]
					[{isys type="f_button" name="login_cancel" p_strClass="btn-large" p_strValue="LC__UNIVERSAL__BUTTON_CANCEL" p_bDisabled=0}]
				</td>
			</tr>
		</table>
	</div>
	<script type="text/javascript">
		(function () {
			'use strict';

			var $login_submit = $('login_submit'),
			    $login_cancel = $('login_cancel');

			$login_submit.on('click', function () {
				$('login_username').enable();
				$('login_password').enable();
				$('isys_form').writeAttribute('action', '').submit();
			});

			$login_cancel.on('click', function () {
				window.location.href = 'index.php?logout=1';
			});

			// Focus Next button.
			setTimeout(function () {
				$login_submit.focus();
			}, 250);
		})();
	</script>
[{else}]
	[{if !empty($login_error)}]
		<script type="text/javascript">
			 [{if isset($login_header)}]$('login_error_header').update('[{$login_header}]');[{/if}]

			 $('login_error_message').update('[{$login_error|nl2br}]');
			 $('login_error').show();

			 // Make input fields writable.
			 $('login_username').enable();
			 $('login_password').enable();
			 if ($('login_submit')) {
				 $('login_submit').show();
			 }
		</script>
	[{/if}]
[{/if}]

[{if $directlogin}]
<script type="text/javascript">
	$('login_username').enable();
	$('login_password').enable();
	$('isys_form').submit();
</script>
[{/if}]
