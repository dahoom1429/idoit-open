<table cellpadding="0" cellspacing="" border="0">
	<tr>
		<th colspan="2" class="navigation">
			<input type="hidden" id="mydoitAction" name="mydoitAction" />


			<table width="100%" cellpadding="0" cellspacing="2" border="0">
				<tr>
					<td nowrap="nowrap" class="bookmarkButton" onClick="search_addCriterias();">
						<img style="vertical-align:middle; padding: 3px;" src="[{$dir_images}]my-doit/bookmark_add.gif" />
						<span style="vertical-align:middle;">[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
					</td>
					<td nowrap="nowrap" class="bookmarkButton" onClick="search_delCriterion();">
						<img style="vertical-align:middle; padding: 3px;" src="[{$dir_images}]my-doit/bookmark_delete.gif" />
						<span style="vertical-align:middle;">[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DELETE"}]</span>
					</td>
					<td nowrap="nowrap" style="width:25px;text-align:center;" class="bookmarkButton" onClick="search_close();">
						<span>X</span>
					</td>
				</tr>
			</table>
		</th>
	</tr>

	[{if $search_addCriterion.bookmarkCount ne 0}]
	[{foreach from=$search_addCriterion.bookmarkList key="id" item="bookmark"}]
	<tr>
		<td width="13%" align="right">
			<input type="checkbox" name="searchSelection[[{$id}]]" />
		</td><td>
			<a title="[{$bookmark.text}]" href="[{$bookmark.link}]">[{$bookmark.text|truncate:61}]</a>
		</td>
	</tr>
	[{/foreach}]
	[{else}]
	<tr>
		<td colspan="2">
			<span style="margin:10px 0 0 8px;">[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
		</td>
	</tr>
	[{/if}]

	[{* Spacer nach unten, um die Table nach unten hin zu füllen *}]


	<tr>
		<td colspan="2"><hr /></td>
	</tr>

	<tr>
		<td colspan="2" style="height:100%;"></td>
	</tr>
</table>