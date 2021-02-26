[{strip}]
<div id="navBar">

	[{if $navbar_buttons}]
		[{foreach $navbar_buttons as $key => $button}]
			[{if $key == 'C__NAVBAR_BUTTON__NEW' || $key == 'C__NAVBAR_BUTTON__PRINT'}]
				[{$button}]
			[{/if}]
		[{/foreach}]
	[{/if}]

	<a id="navbar_item_8" title="" class="navbar_item" href="?[{$current_link}]">
		<img src="[{$dir_images}]icons/silk/link.png" width="15px" height="15px" alt="" />
		<span class="navBarLink vam">[{isys type="lang" ident="LC__UNIVERSAL__LINK_TO_THIS_PAGE"}]</span>
	</a>

	<div class="fr">
		<span id="cSpanRecFilter">
            [{include file="content/top/list_paging.tpl"}]

            [{if ($list_display)}]
			[{isys type="f_dialog"
					p_strClass="input input-mini"
					id="cRecStatus"
					name="cRecStatus"
					p_bEditMode="1"
					p_strStyle="width:100px;"
					p_onClick=""
					p_bDbFieldNN="1"
					p_onChange="form_submit();"}]
            [{/if}]
		</span>
	</div>

	<a class="navbar_item" onclick="expandAllLogbookChanges();">
		<img src="[{$dir_images}]icons/silk/text_linespacing.png" alt="" />
		<span class="navBarLink vam">[{isys type="lang" ident="LC__LOGBOOK__EXPAND_ALL_CHANGES"}]</span>
	</a>

	<br class="cb"/>
</div>
[{/strip}]

<script type="text/javascript">
    $$('#cSpanRecFilter .pager-filter').invoke('hide');
</script>
