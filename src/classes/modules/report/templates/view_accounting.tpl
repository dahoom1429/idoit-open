<table class="contentTable">
	<tr>
		<td></td>
		<td class="pl20">
			<p><strong>Bitte wählen Sie einen Zeitraum aus, für den der Report dargestellt werden soll</strong></p>
		</td>
	</tr>
	<tr>
		<td class="key"><label for="C__CALENDAR_FROM">[{isys type="lang" ident="LC__UNIVERSAL__FROM"}]</label></td>
		<td class="value">[{isys type="f_popup" name="C__CALENDAR_FROM" p_strPopupType="calendar" p_strValue=$from p_strClass="input-mini"}]</td>
	</tr>
	<tr>
		<td class="key"><label for="C__CALENDAR_TO">[{isys type="lang" ident="LC__UNIVERSAL__TO"}]</label></td>
		<td class="value">[{isys type="f_popup" name="C__CALENDAR_TO" p_strPopupType="calendar" p_strValue=$to p_strClass="input-mini"}]</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<button type="button" class="ml20 btn" onclick="this.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif'); $('isys_form').submit();">
				<img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="mr5" />
				<span>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</span>
			</button>
		</td>
	</tr>
</table>

<h3 class="mt10 gradient text-shadow p5 border-top border-bottom">Report</h3>

[{if count($data)}]
	<div class="p5">
		<table class="listing border">
			<thead>
			<tr style="padding-bottom:5px;">
				<th>[{isys type='lang' ident='Objekt ID'}]</th>
				<th>[{isys type='lang' ident='Objektlink'}]</th>
				<th>[{isys type='lang' ident='Änderungsdatum'}]</th>
				<th>[{isys type='lang' ident='Benutzer'}]</th>
				<th>[{isys type='lang' ident='Wert vorher'}]</th>
				<th>[{isys type='lang' ident='Wert nachher'}]</th>
			</tr>
			</thead>
			<tbody>
			[{foreach $data as $row}]
				<tr>
					<td>[{$row[0]}]</td>
					<td>[{$row[1]}]</td>
					<td>[{$row[2]}]</td>
					<td>[{$row[3]}]</td>
					<td>[{$row[4]}]</td>
					<td>[{$row[5]}]</td>
				</tr>
			[{/foreach}]
			</tbody>
		</table>
	</div>
[{else}]
	<p class="m5 p5 box-blue">
		<img src="[{$dir_images}]icons/silk/information.png" class="vam mr5"><span class="vam">[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
	</p>
[{/if}]