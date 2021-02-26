<h2>Step 6: Installation</h2>

<table class="installingTable">
	<tr>
		<td><img src="setup/images/main_installing.gif"/></td>
		<td>
			<strong>Installation in progress, please wait ...</strong>
		</td>
	</tr>
</table>

<script language="JavaScript" type="text/javascript">
	function finishInstall() {
		document.forms.install_form.install_step.value = 6;
		document.forms.install_form.install_now.value = 1;
		document.forms.install_form.submit();
	}

	window.setTimeout('finishInstall()', 2000);
</script>