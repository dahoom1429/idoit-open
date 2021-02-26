[{if $viewContent}]
[{foreach from=$viewContent item=items key=usageType}]
<fieldset class="overview" style="margin-top:15px;">

	<legend><span>[{isys type="lang" ident=$usageType}]</span></legend>

		<table class="mainTable">
	<thead>
		<tr>
			<th>[{isys type="lang" ident="LC__CATG__CABLE"}]</th>
			<th>[{isys type="lang" ident="LC__REPORT__VIEW__CABLE_CONNECTIONS__CABLE_CONNECTED_OBJECTS"}]</th>
		</tr>
	</thead>
	<tbody>
		[{foreach from=$items item=cable key=cableId}]
		<tr>
			<td>[{$cable.quickInfoLink}]</a></td>
			<td>[{$cable.connectedObjects|implode:'<br />'}]</td>
		</tr>
		[{/foreach}]
	</tbody>
</table>

</fieldset>

[{/foreach}]
[{/if}]
