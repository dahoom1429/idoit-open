[{isys_group name="login"}]
<div id="loginArea">
	<div id="loginMessages">
		<div class="login_error box-red" id="login_error" [{if empty($login_error)}]style="display:none;"[{/if}]>
			<div class="p5">
				<img src="[{$dir_images}]icons/infoicon/error.png" class="vam mr5" alt="" />
				<strong id="login_error_header" class="vam">[{$login_header|default:"i-doit system error"}]:</strong>
			</div>
			<p class="m5" id="login_error_message">[{$login_error}]</p>
		</div>

		<div id="ajaxLoading" style="width:200px;margin:10px auto;display:none;z-index:1000;">
			<img src="[{$dir_images}]/ajax-loading.gif" class="vam mr5" /><strong>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</strong>
		</div>
	</div>

	<div id="loginInnerArea">
		<h2 class="gradient text-shadow p10">
			<img class="fr" src="[{$dir_images}]logo.png" alt="i-doit" height="18" />
			<span>Login</span>
			[{assign var="welcomeMessage" value=isys_settings::get('system.login.welcome-message', isys_application::instance()->container->get('language')->get('LC__SYSTEM_SETTINGS__LOGIN_WELCOME_MESSAGE_DEFAULT'))}]
			[{if !empty($welcomeMessage)}]
			<br />
			<br />
			<span id="welcome-message">
				[{isys_settings::get('system.login.welcome-message', isys_application::instance()->container->get('language')->get('LC__SYSTEM_SETTINGS__LOGIN_WELCOME_MESSAGE_DEFAULT'))}]
			</span>
			[{/if}]
		</h2>

		<div id="loginContent">
			<input type="hidden" name="HTTP_GOTO" value="[{$HTTP_GOTO}]" />

			<label for="login_username">[{isys type="lang" ident="Username"}]</label><br />
			<input class="input input-block" type="text" [{$username_disable}] name="login_username" id="login_username" value="[{$login_username}]" />

			<br />

			<label for="login_password">[{isys type="lang" ident="Password"}]</label><br />
			<input class="input input-block" type="password" [{$password_disable}] name="login_password" id="login_password" value="" />

			<div id="login_mandator_selection" class="mt10" style="display:none;">
				[{include file="content/mandants.tpl"}]
			</div>

			<div class="mt10">
				[{isys type="f_button" name="login_submit" p_strClass="btn-large" p_strValue="Login" p_bDisabled=0}]
			</div>

			<table id="" cellpadding="0" cellspacing="0" border="0"
			       style="margin-top:15px;width:100%; background-color:#eee;padding:5px;;">
				<tr>
					<td align="left">
						<p class="login_msg">
							<strong>i-do<span class="text-red">it</span></strong> -
							<strong>[{isys_application::instance()->info->get('version')}] [{isys_application::instance()->info->get('step')}] <span title="[{isys_application::instance()->info->get('type')}]">[{isys_application::instance()->info->get('type')|truncate:4}]</span></strong>
						</p>
					</td>
					<td align="right">
						<p class="login_msg">
							<a href="./admin/">Admin-Center</a> - <a class="text-bold" href="https://www.i-doit.com" target="_blank">i-doit.com</a> - &copy; synetics gmbh
						</p>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<script type="text/javascript">
		(function () {
			'use strict';

			var $form           = $('isys_form'),
			    $login_username = $('login_username'),
			    $login_password = $('login_password'),
			    $login_submit   = $('login_submit'),
			    $login_error    = $('login_error'),
			    $ajax_loading   = $('ajaxLoading');

			// Do the initial setup
			$form.writeAttribute('action', '[{$HTTP_GOTO}]');
			$login_username.focus();

			$login_username.on('keypress', function (ev) {
				if (ev.keyCode == Event.KEY_RETURN)
				{
					ev.preventDefault();
					load_mandants('isys_form', 'login_mandator_selection');
				}
			});

			$login_password.on('keypress', function (ev) {
				if (ev.keyCode == Event.KEY_RETURN)
				{
					ev.preventDefault();
					load_mandants('isys_form', 'login_mandator_selection');
				}
			});

			$login_submit.on('click', function () {
				load_mandants('isys_form', 'login_mandator_selection');
			});

			function load_mandants(p_form, p_target) {
				var formdata = $(p_form).serialize(true);

				$login_submit.hide();

				if ($(p_form))
				{
					$ajax_loading.show();
					$login_error.hide();

					new Ajax.Updater(p_target, document.location.href, {
						method:       'post',
						asynchronous: true,
						onSuccess:    function (xhr) {
							$login_username.disable();
							$login_password.disable();
							$ajax_loading.hide();
							$(p_target).appear();
						},
						onFailure:    function () {
							$login_error.show();
							$('login_error_message').update('Error.');
							$ajax_loading.hide();
						},
						parameters:   formdata,
						evalScripts:  true
					});
				}
				else
				{
					alert('Form:' + p_form + ' does not exist.\n - Check your load_mandants() parameters.');
				}
			}
		})();
	</script>
</div>
[{/isys_group}]
