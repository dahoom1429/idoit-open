<table cellspacing="0" class="[{$list_object->get_class()}]">
	[{if count($list_object->get_colgroups()) > 0}]
	<colgroup>
		[{foreach from=$list_object->get_colgroups() item="col"}]
			<col width="[{$col}]" />
		[{/foreach}]
	</colgroup>
	[{/if}]
	<thead>
		<tr>
			[{if $list_object->checkbox_enabled()}]
			<th><input type="checkbox" onClick="[{$list_object->get_js_check_all()}]" value="X" /></th>
			[{/if}]

			[{foreach from=$list_object->get_headers() item="header" key="header_key"}]

			<th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
				<a href="javascript:" onclick="document.isys_form.dir.value='[{$list_object->get_order()}]'; document.isys_form.sort.value='[{$header_key}]'; form_submit();">[{$header}]</a>
			</th>
			[{/foreach}]
		</tr>
	</thead>
	<tbody>
	[{assign var="id_element" value=$list_object->get_id_element()}]

	[{foreach from=$list_object->get_data() item="data" key="data_key"}]
		[{assign var="fulldata" value=$list_object->get_fulldata($data_key)}]

		<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
		[{if $list_object->checkbox_enabled()}]
			<td><input type="checkbox" class="checkbox" name="id[]" value="[{$fulldata.$id_element}]" /></td>
		[{/if}]

		[{foreach from=$data item="content" key="content_key"}]

			[{if $content_key != '__link'}]
			<td onclick="document.location='[{$data.__link}]'">[{$content}]</td>
			[{/if}]

		[{/foreach}]

		</tr>

	[{/foreach}]
	</tbody>
</table>

<script type="text/javascript"></script>