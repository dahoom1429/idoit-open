<h2 class="p5 gradient">LOGINventory</h2>

<input type="hidden" name="dbID" value="[{$dbID}]">

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_DATABASES"}]</span></legend>
	<br/>

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__LOGINVENTORY_HOST" ident="IP"}]</td>
			<td class="value">[{isys type="f_text" name="C__MODULE__IMPORT__LOGINVENTORY_HOST"}]</td>
		</tr>

		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__LOGINVENTORY_PORT" ident="Port"}]</td>
			<td class="value">[{isys type="f_text" name="C__MODULE__IMPORT__LOGINVENTORY_PORT"}]</td>
		</tr>

		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__LOGINVENTORY_SCHEMA" ident="LC__MODULE__NAGIOS__NDODB_SCHEMA"}]</td>
			<td class="value">[{isys type="f_text" name="C__MODULE__IMPORT__LOGINVENTORY_SCHEMA"}]</td>
		</tr>

		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__LOGINVENTORY_USER" ident="LC__LOGIN__USERNAME"}]</td>
			<td class="value">[{isys type="f_text" name="C__MODULE__IMPORT__LOGINVENTORY_USER"}]</td>
		</tr>

		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__LOGINVENTORY_PASS" ident="LC__LOGIN__PASSWORD"}]</td>
			<td class="value">[{isys type="f_password" name="C__MODULE__IMPORT__LOGINVENTORY_PASS"}]</td>
		</tr>
	</table>
</fieldset>