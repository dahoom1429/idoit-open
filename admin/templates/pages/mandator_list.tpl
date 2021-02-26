<div>
	<button type="button" class="btn bold" onclick="$('mandators').fade({duration:0.3});new Effect.SlideDown('add-new',{duration:0.4});"><img src="../images/icons/silk/add.png" class="mr5" /><span>Add new tenant</span></button>
	<button type="button" class="btn bold ml15" onclick="edit_mandator();"><img src="../images/icons/silk/pencil.png" class="mr5" /><span>Edit</span></button>
	<button type="button" class="btn bold" onclick="submit_mandators('activate');"><img src="../images/icons/silk/bullet_green.png" class="mr5" /><span>Activate</span></button>
	<button type="button" class="btn bold" onclick="submit_mandators('deactivate');"><img src="../images/icons/silk/bullet_red.png" class="mr5" /><span>Deactivate</span></button>
	<button type="button" class="btn bold ml15" onclick="delete_mandators();"><img src="../images/icons/silk/delete.png" class="mr5" /><span>Remove</span></button>
	<button type="button" class="btn bold ml15" onclick="submit_mandators('list');"><img src="../images/icons/silk/page_save.png" class="mr5" /><span>Save license settings</span></button>

	<img src="../images/ajax-loading.gif" style="margin-top:1px;margin-left:5px;display:none;" id="toolbar_loading" />
</div>

<hr class="separator" />


<input type="hidden" name="action" value="updateLicenseInformation">

<b>Tenant licenses</b>: [{$totalTenants}], In use: [{$totalTenants - $remainingTenants}], Free: [{$remainingTenants}]<br />
<b>Object licenses</b>: <span id="total_license_objects">[{$totalLicenseObjects}]</span>, In use: [{$totalLicenseObjects - $remaningLicenseObjects}], Free: [{$remaningLicenseObjects}]<br />
<b>Automatic object license distribution</b>: <input type="checkbox" name="active_license_distribution" [{if $activeLicenseDistribution}]checked[{/if}] /><br />

<table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10" id="list">
	<colgroup>
		<col width="30" />
		<col width="30" />
		<col width="100" />
		<col width="400" />
		<col width="100" />
	</colgroup>
	<thead>
		<tr>
			<th>&nbsp;[ ]</th>
			<th>ID</th>
			<th>Tenant Name</th>
			<th>Database Name</th>
			<th>Database Host</th>
			<th>Active</th>
            <th>Assigned object licenses</th>
		</tr>
	</thead>
	<tbody>
	[{while $row = $mandators->get_row()}]
		<tr class="[{cycle values="even,odd"}] mandator-row[{if $mandatorObjectCount[$row.isys_mandator__id] > $row.isys_mandator__license_objects && $row.isys_mandator__active}] invalid[{/if}]">
			<td><input type="checkbox" name="id[]" value="[{$row.isys_mandator__id}]" /></td>
			<td>[{$row.isys_mandator__id}]</td>
			<td>[{$row.isys_mandator__title}]</td>
			<td class="bold">[{$row.isys_mandator__db_name}]</td>
			<td>[{$row.isys_mandator__db_host}]</td>
			<td>[{if $row.isys_mandator__active}]<span class="green">Yes[{else}]<span class="red">No[{/if}]</span></td>
            <td>
                Object licenses in use: [{$mandatorObjectCount[$row.isys_mandator__id]}], Assigned licenses: <input type="number" class="mandator_license_objects" data-mandator-id="[{$row.isys_mandator__id}]" name="license_objects[]" value="[{$row.isys_mandator__license_objects}]" [{if $activeLicenseDistribution}]disabled="disabled"[{/if}]/>
            </td>
		</tr>
	[{/while}]
	</tbody>
</table>

<style>
    .invalid {
        background-color: #ffdddd !important;
    }
</style>
