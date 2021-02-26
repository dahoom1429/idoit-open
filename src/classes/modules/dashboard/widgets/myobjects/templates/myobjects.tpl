<h3 class="gradient p5 text-shadow border-bottom border-grey">[{isys type="f_label" ident="LC__WIDGET__MYOBJECTS"}]</h3>

<div class="p5" style="overflow-x:auto;">
	<table cellspacing="0" cellpadding="2" class="" style="margin:0;text-align: left;width:100%;line-height:18px;">
		<thead>
		<tr style="line-height:20px;">
			<th class="border-bottom border-grey">[{isys type="lang" ident="LC__CMDB__LOGBOOK__TITLE"}]</th>
			<th class="border-bottom border-grey">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__CREATION_DATE"}]</th>
			<th class="border-bottom border-grey">[{isys type="lang" ident="LC__UNIVERSAL__DATE_OF_CHANGE"}]</th>
		</tr>
		</thead>
		<tbody>
		[{foreach from=$tabledata item=row}]
			<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
				<td><div class="cmdb-marker mouse-help" style="background:[{$row.cmdb_color}];" title="[{$row.cmdb_title}]"></div>[{$row.title_link}]</td>
				<td>[{$row.created}]</td>
				<td>[{$row.updated}]</td>
			</tr>
			[{/foreach}]
		</tbody>
	</table>
</div>