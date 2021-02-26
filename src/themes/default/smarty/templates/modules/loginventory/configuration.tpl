<h2 class="p5 gradient">LOGINventory</h2>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__CONFIGURATION"}]</span></legend>

	<p class="p5 mt15 m5 box-blue"><img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span>[{isys type="lang" ident="LC__LOGINVENTORY__VERSION_COMPABILITY" p_bHtmlEncode=false}]</span></p>

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" name="C__LOGINVENTORY__OBJTYPE" ident="LC__OCS__DEFAULT_OBJ_TYPE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__LOGINVENTORY__OBJTYPE" p_bDbFieldNN="1"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__LOGINVENTORY__DEFAULT_DB" ident="LC__OCS__DEFAULT_DEFAULT_DB"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__LOGINVENTORY__DEFAULT_DB" p_bDbFieldNN="1"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__LOGINVENTORY__APPLICATION" ident="LC__OCS__REGGED_APPLICATIONS"}]</td>
			<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__LOGINVENTORY__APPLICATION"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__LOGINVENTORY__LOGBOOK" ident="LC__OCS__LOGBOOK_ACTIVE_IN_IMPORT"}]</td>
			<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__LOGINVENTORY__LOGBOOK"}]</td>
		</tr>
	</table>
</fieldset>