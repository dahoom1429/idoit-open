<script type="text/javascript">
	function check(el)
	{
		if (el.checked == "") el.checked="checked";
		else el.checked="";
	}

	function select_all(me)
	{
		var checkboxes = $A(document.getElementsByClassName('checkbox'));

		if (checkboxes.length > 0)
		{
			checkboxes.each(function (el)
			{
				if (me.checked == "") el.checked = "";
				else el.checked = "checked";
			});
		}
	}

</script>
<h2>System database:</h2>
<table cellpadding="2" cellspacing="0" width="100%" class="listing" style="margin-top:15px;">
	<colgroup>
		<col width="100" />
		<col width="400" />
		<col width="100" />
	</colgroup>
	<thead>
		<tr>
			<th>Update</th>
			<th>Name</th>
			<th>Type</th>
			<th>Version</th>
		</tr>
	</thead>
	<tbody>

	<tr class="[{cycle values="even,odd"}]" onclick="check(getElementById('system_database'));">
		<td>
			<input type="checkbox" class="checkbox" onclick="check(getElementById('system_database'));"
				id="system_database" name="system_database"
				value="[{$g_system_database}]"[{if ($smarty.session.system_database != -1)}] checked="checked"[{/if}]/></td>
		<td><strong>[{$g_system_database}]</strong></td>
		<td>System</td>
		<td>[{$g_info.version|default:"n/a"}] - rev [{$g_info.revision|default:0}]</td>
	</tr>
	</tbody>
</table>

<br />

<h2>Tenant database(s):</h2>

<table cellpadding="2" cellspacing="0" width="100%" class="listing" style="margin-top:15px;">
	<colgroup>
		<col width="100" />
		<col width="400" />
		<col width="100" />
	</colgroup>
	<thead>
		<tr>
			<th>Update</th>
			<th>Name</th>
			<th>Type</th>
			<th>Version</th>
		</tr>
	</thead>
	<tbody>
	[{foreach from=$g_databases item=l_db}]
	[{counter print=false assign="i"}]
	<tr class="[{cycle values="even,odd"}]" onclick="check(getElementById('databases_[{$i}]'));">
		<td><input type="checkbox" class="checkbox" onclick="check(getElementById('databases_[{$i}]'));" id="databases_[{$i}]" name="databases[[{$i}]]" value="[{$l_db.name}]"[{if isset($smarty.session.mandant_databases.$i)}] checked="checked"[{/if}]/></td>
		<td><strong>[{$l_db.name}]</strong></td>
		<td>[{$l_db.type|default:"Mandant"}]</td>
		<td>[{$l_db.version|default:"n/a"}] - rev [{$l_db.revision|default:0}]</td>
	</tr>
	[{/foreach}]
	</tbody>
</table>

<p style="margin-left:5px;">
	<label><input type="checkbox" id="sel_all" onclick="select_all(this);" /> <strong>Select all</strong></label>
</p>

<p style="padding-left:7px;">
	Select the database(s) you want to update.
	It is highly recommended to update <strong>all</strong> databases.
</p>

[{if $php_version_message}]
<p class="box-[{$php_version_message_color}] bold p5">[{$php_version_message}]</p>
[{/if}]

[{if $addon_version_notification}]
<p class="box-yellow bold p5">[{$addon_version_notification}]</p>
[{/if}]

[{if $sql_mode}]
<div style="font-weight: bold; padding: 4px; margin: 30px 5px; background-color: rgb(255, 221, 221); border: 1px solid rgb(255, 67, 67); display: block;">
    Warning: SQL-Strictmode is active!
    <br>
    <br>
    We recommend to disable the SQL-Strictmode for better compatibility. To prevent problems during the update process i-doit will disable it for you.
</div>
[{/if}]

<script type="text/javascript">$('sel_all').checked=true;select_all($('sel_all'));</script>

