<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__OBJTYPE__DATABASE_INSTANCE" name="C__CMDB__CATS__DB_SCHEMA__RUNS_ON"}]</td>
		<td class="value">
			[{if isys_glob_is_edit_mode()}]
				[{isys type="f_dialog" name="C__CMDB__CATS__DB_SCHEMA__RUNS_ON" tab="1"}]
			[{else}]
			<div class="ml20">[{$runsOnStrValue|default:''}]</div>
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__UNIVERSAL__TITLE" name="C__CMDB__CATS__DB_SCHEMA__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__DB_SCHEMA__TITLE" tab="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="Storage Engine" name="C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE" default=""}]</td>
	</tr>
</table>