<h3 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__CUSTOMIZATION"}]</h3>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="lang" ident="LC_UNIVERSAL__CATEGORY"}]</td>
		<td class="value pl20">[{$categoryParentTitle}][{isys type="lang" ident=$category.title}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC_UNIVERSAL__PROPERTY"}]</td>
		<td class="value pl20">[{isys type="lang" ident=$property.info.title}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CUSTOMIZE_OBJECT_BROWSER__SHOW_PROPERTY_CATEGORIES" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__SHOW_PROPERTY_CATEGORIES"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CUSTOMIZE_OBJECT_BROWSER__SHOW_PROPERTY_CATEGORIES"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CUSTOMIZE_OBJECT_BROWSER__DEFAULT_OBJECT_TYPE" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__DEFAULT_OBJECT_TYPE"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CUSTOMIZE_OBJECT_BROWSER__DEFAULT_OBJECT_TYPE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CUSTOMIZE_OBJECT_BROWSER__DEFAULT_ATTRIBUTE_SORTING" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__DEFAULT_ATTRIBUTE_SORTING"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CUSTOMIZE_OBJECT_BROWSER__DEFAULT_ATTRIBUTE_SORTING"}]</td>
    </tr>
	<tr>
		<td class="key vat">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__SELECTED_ATTRIBUTES"}]</td>
		<td class="value pl20">
			[{isys type="f_property_selector" name="C__CUSTOMIZE_OBJECT_BROWSER__PROPERTIES"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CUSTOMIZE_OBJECT_BROWSER__OBJECT_TYPES" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__OBJECT_TYPE_FILTER"}]</td>
		<td class="value">
			[{if $catFilter}]
				<p class="ml20 p5 box-yellow text-normal">
					<img src="[{$dir_images}]icons/silk/error.png" class="mr5 vam" />[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__OBJECT_TYPE_FILTER_DESCRIPTION"}] [{$catFilter}].<br />
                    [{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__OBJECT_TYPE_FILTER_DESCRIPTION3"}]: [{$catFilterObjectTypes}]
				</p>
			[{else}]
				[{isys type="f_dialog" name="C__CUSTOMIZE_OBJECT_BROWSER__OBJECT_TYPES[]"}]
				<br class="cb" />
				<p class="ml20 mt5 text-blue text-normal">
					<img src="[{$dir_images}]icons/silk/information.png" class="mr5 vam" />[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__OBJECT_TYPE_FILTER_DESCRIPTION2"}]
				</p>
			[{/if}]
		</td>
	</tr>
</table>
