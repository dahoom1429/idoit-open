<div class="content" id="content">
	<h2>Migration</h2>

	[{foreach from=$migration_log key=db item=log}]
	<h3>[{$db}]</h3>
	<table cellpadding="2" cellspacing="0" width="100%" class="listing" style="margin-top:15px;">
	<thead>
		<tr>
			<th>Log-Message</th>
		</tr>
	</thead>
	<tbody>
		[{foreach from=$log item=l}]
		[{foreach from=$l item=message}]
		<tr class="[{cycle values="even,odd"}]">
			[{if $message == "-"}]
			<td bgcolor="#ccc"></td>
			[{else}]
			<td><span>[{$message}]</span></td>
			[{/if}]
		</tr>
		[{/foreach}]
		[{/foreach}]
	</tbody>
	</table>
[{/foreach}]
</div>

<input type="hidden" name="config_backup" value="[{$config_backup}]" />

<div id="loadingTable" style="display:none;top:18%" class="loadingTable">
	<p>
		<img src="[{$g_config.www_dir}]setup/images/main_installing.gif" style="vertical-align:middle;" />
		<strong id="init_text">Initializing property migration...</strong><br />
	</p>
	<p>
		Depending on your database size and operating system, <strong>the property migration can take several minutes</strong>.<br />
		It is highly recommended to not abort the migration!
		Aborting the migration can result in data inconsistency!
	</p>
</div>