<div id="content" class="content">
	<h2>Overview (Log)</h2>
	
	<table cellpadding="2" cellspacing="0" width="100%" class="listing" style="margin-top:15px;">
		<colgroup>
			<col width="600" />
			<col width="100" />
		</colgroup>
		<thead>
			<tr>
				<th>Process</th>
				<th>Success</th>
			</tr>
		</thead>
		<tbody>
		[{foreach from=$g_log item=l_entry}]
			[{if $l_entry.priority < $smarty.const.C__LOW}]
			<tr class="[{cycle values="even,odd"}]">
				<td><span class="[{$l_entry.class}]">[{$l_entry.message}]</span></td>
				<td><span class="bold" style="color:[{$l_entry.color}]">[{$l_entry.result}]</span></td>
			</tr>
			[{/if}]
		[{/foreach}]
		</tbody>
	</table>
	
	<input type="hidden" name="config_backup" value="[{$config_backup}]" />
</div>

<div id="loadingTable" style="display:none;top:18%" class="loadingTable">

<p>
	<img src="[{$g_config.www_dir}]setup/images/main_installing.gif" style="vertical-align:middle;" />
	<strong id="init_text">Initializing migration...</strong><br />
</p>
<p>
Depending on your database size and operating system, <strong>the migration can take several minutes</strong>.<br />
It is highly recommended to not abort the migration!
Aborting the migration can result in data inconsistency!
</p>

</div>

<script type="text/javascript">
	window.setTimeout(function(){
		$('init_text').update('Migrating databases, please wait until the process is done. ');
	}, 2500);
</script>