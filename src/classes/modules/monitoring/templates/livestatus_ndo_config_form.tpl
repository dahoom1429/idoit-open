<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MONITORING"}]</h2>

<h3 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MONITORING__LIVESTATUS_NDO__CONFIGURATION_EDIT"}]</h3>

[{isys type="f_text" p_bInvisible=true p_bInfoIconSpacer=0 name="config_id"}]

<div id="monitoring_config">
	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__UI_TITLE" name="C__MONITORING__CONFIG__TITLE"}]</td>
			<td class="value">[{isys type="f_text" name="C__MONITORING__CONFIG__TITLE"}]</td>
		</tr>
		<tr>
			<td colspan="2"><hr /></td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__ACTIVE" name="C__MONITORING__CONFIG__ACTIVE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__MONITORING__CONFIG__ACTIVE" p_bDbFieldNN=true}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__TYPE" name="C__MONITORING__CONFIG__TYPE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__MONITORING__CONFIG__TYPE" p_bDbFieldNN=true}]</td>
		</tr>
		<tr>
			<td colspan="2"><hr /></td>
		</tr>
		<tr class="livestatus">
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__CONNECTION" name="C__MONITORING__CONFIG__CONNECTION"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__MONITORING__CONFIG__CONNECTION" p_bDbFieldNN=true}]</td>
		</tr>
		<tr class="livestatus unix">
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__PATH" name="C__MONITORING__CONFIG__PATH"}]</td>
			<td class="value">[{isys type="f_text" name="C__MONITORING__CONFIG__PATH"}]</td>
		</tr>
		<tr class="tcp">
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__ADDRESS" name="C__MONITORING__CONFIG__ADDRESS"}]</td>
			<td class="value">[{isys type="f_text" name="C__MONITORING__CONFIG__ADDRESS"}]</td>
		</tr>
		<tr class="tcp">
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__PORT" name="C__MONITORING__CONFIG__PORT"}]</td>
			<td class="value">[{isys type="f_text" name="C__MONITORING__CONFIG__PORT"}]</td>
		</tr>
		<tr class="ndo">
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__DBNAME" name="C__MONITORING__CONFIG__DBNAME"}]</td>
			<td class="value">[{isys type="f_text" name="C__MONITORING__CONFIG__DBNAME"}]</td>
		</tr>
		<tr class="ndo">
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__DBPREFIX" name="C__MONITORING__CONFIG__DBPREFIX"}]</td>
			<td class="value">[{isys type="f_text" name="C__MONITORING__CONFIG__DBPREFIX"}]</td>
		</tr>
		<tr class="ndo">
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__USERNAME" name="C__MONITORING__CONFIG__USERNAME"}]</td>
			<td class="value">[{isys type="f_text" name="C__MONITORING__CONFIG__USERNAME"}]</td>
		</tr>
		<tr class="ndo">
			<td class="key">[{isys type="f_label" ident="LC__MONITORING__PASSWORD" name="C__MONITORING__CONFIG__PASSWORD"}]</td>
			<td class="value">[{isys type="f_password" name="C__MONITORING__CONFIG__PASSWORD"}]</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	$('C__MONITORING__CONFIG__CONNECTION').on('change', function (ev) {
		var el = ev.findElement('select'),
			table = el.up('table');

		table.select('tr.tcp,tr.unix').invoke('hide');
		table.select('tr.' + el.getValue()).invoke('show');
	});

	$('C__MONITORING__CONFIG__TYPE').on('change', function (ev) {
		var el = ev.findElement('select'),
			table = el.up('table');

		table.select('tr.livestatus,tr.ndo').invoke('hide');
		table.select('tr.' + el.getValue()).invoke('show');
	});

	$('C__MONITORING__CONFIG__TYPE', 'C__MONITORING__CONFIG__CONNECTION').invoke('simulate', 'change');
</script>