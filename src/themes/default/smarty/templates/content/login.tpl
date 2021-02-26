[{* @todo  Where is this template used? *}]

[{include file="content/form.tpl"}]

<script type="text/javascript" language="JavaScript">
	document.isys_form.action='[{$HTTP_GOTO}]';

	function load_mandants(p_form, p_target) {

		var l_url		= document.location.href;
		var l_method	= 'post';

		if ($(p_form)) {

			var	l_params = $(p_form).serialize(true);

			if($('ajaxLoading')) $('ajaxLoading').show();
			$('login_error').hide();

			$('login_username').readOnly = true;
 			$('login_password').readOnly = true;

			new Ajax.Updater(	p_target,
								l_url,
								{
									method:l_method,
									asynchronous:true,
									onSuccess: function(transport) {
										$('ajaxLoading').hide();
									},
									onFailure: function() {
										$('login_error').show();
										$('login_error_message').update('Error.');
										$('ajaxLoading').hide();
									},
									parameters:l_params,
									evalScripts:true
								});
		} else {
			alert('Form:' + p_form + ' does not exist.\n - Check your load_mandants() parameters.');
		}

	}
</script>


<div id="loginMessages">
	<div class="login_error" id="login_error"[{if empty($login_error)}] style="display:none;"[{/if}]>
		<div><img src="[{$dir_images}]icons/infoicon/error.png" style="vertical-align:middle;" alt="" /> <strong id="login_error_header">[{$login_header|default:"i-doit system error"}]:</strong></div>
		<p id="login_error_message">[{$login_error}]</p>
	</div>

	<div id="ajaxLoading" style="width:200px;margin:10px auto;display:none;z-index:1000;">
			<img src="[{$dir_images}]/ajax-loading.gif" style="vertical-align:middle;" />
			<strong>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</strong>
	</div>
</div>

<div id="loginInnerArea">

	<table id="loginInnerTable" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<div id="loginContent">
					<table cellpadding="2" cellspacing="0" id="loginTable" align="right">
						<tr>
							<td>[{isys type="lang" ident="Username"}]: </td>
							<td>
								<input type="hidden" name="HTTP_GOTO" value="[{$HTTP_GOTO}]" />
								<input class="input input-small" value="" type="input" [{$username_disable}] name="login_username" id="login_username" value="[{$login_username}]" onkeypress="if(event.keyCode==13||event.keyCode==3) { load_mandants('isys_form', 'login_submit'); return false; }" />
							</td>
						</tr>
						<tr>
							<td>[{isys type="lang" ident="Password"}]: </td>
							<td><input class="input input-small" value="" type="password" [{$password_disable}]  name="login_password" id="login_password" onkeypress="if(event.keyCode==13||event.keyCode==3) { load_mandants('isys_form', 'login_submit'); return false; }" /></td>
						</tr>
						<tr>
							<td colspan="2">
								<div id="login_submit">
									[{include file="content/mandants.tpl"}]
								</div>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<table id="log_footer" align="right" cellpadding="0" cellspacing="0" border="0" style="margin:0;width:100%;" class="">
					<tr>
						<td align="left">
							<p class="login_msg">
								<strong>i-do<span class="red">it</span></strong> - <strong>[{isys_application::instance()->info->get('version')}] [{isys_application::instance()->info->get('step')}] <span title="[{isys_application::instance()->info->get('type')}]">[{isys_application::instance()->info->get('type')|truncate:4}]</span></strong>
							</p>
						</td>
						<td align="right">
							<p class="login_msg">
								[{if $showAdminCenterLink}]<a href="./admin/">Admin-Center</a> -[{/if}]
			   					<a class="bold" href="https://www.i-doit.com" target="_blank">i-doit.com</a> - &copy;  synetics gmbh
			   				</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
 $('login_username').focus();
</script>

</form>
