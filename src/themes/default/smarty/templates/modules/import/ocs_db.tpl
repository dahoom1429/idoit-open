<h3 class="p5 gradient">OCS [{isys type="lang" ident="LC__UNIVERSAL__DATABASE"}]</h3>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__DATABASE"}]</span></legend>
	<br/>

	<script type="text/javascript">
		[{include file="modules/import/ocs.js"}]
	</script>

	<input type="hidden" name="dbID" value="[{$dbID}]">

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__OCS_HOST" ident="IP"}]</td>
			<td class="value">[{isys type="f_text" name="C__MODULE__IMPORT__OCS_HOST"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__OCS_PORT" ident="Port"}]</td>
			<td class="value">[{isys type="f_text" name="C__MODULE__IMPORT__OCS_PORT"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__OCS_SCHEMA" ident="LC__MODULE__NAGIOS__NDODB_SCHEMA"}]</td>
			<td class="value">[{isys type="f_text" name="C__MODULE__IMPORT__OCS_SCHEMA"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__OCS_USER" ident="LC__LOGIN__USERNAME"}]</td>
			<td class="value">[{isys type="f_text" name="C__MODULE__IMPORT__OCS_USER"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__MODULE__IMPORT__OCS_PASS" ident="LC__LOGIN__PASSWORD"}]</td>
			<td class="value">[{isys type="f_password" name="C__MODULE__IMPORT__OCS_PASS"}]</td>
		</tr>
	</table>
</fieldset>