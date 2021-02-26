<script type="text/javascript">
	var $LC_SURE_DELETE_LICENCE = 'Are you sure you want to delete this/these licence(s)?';
	var $LC_SURE_DELETE_MULTI_LICENCE = 'All corresponding Client (multi-tenant) licences will also be removed. Are you sure you want to delete this/these licence(s)?';

	function subtraction(el, min) {
		el = $(el);

		if ((parseInt(el.value) - 1) >= min) {
			el.value = parseInt(el.value) - 1;
		}

	}
	function addition(el, key, hostingKey) {
		el = $(el);

		if(hosting_count.hasOwnProperty(hostingKey))
        {
            var max = hosting_count[hostingKey];

            if (max != 0)
            {
                $$('input.object_count').each(function (input) {

                    if(input.getAttribute('data-parent-licence') == hostingKey)
                    {
                        max -= input.value;
                    }
                });
            }
            else max = 1;

            if (max > 0) el.value = parseInt(el.value) + 1;
        }

	}
	function lic_attach(p_lic_id) {

		new Ajax.Request('?req=licences&licence_id=' + p_lic_id,
		{
		  parameters: { mandator: $('mandator_' + p_lic_id).value, action: 'attach' },
		  onSuccess: function(response)  {
			  if (response.responseJSON.success)
			  {
			    var new_location = window.location.href;
			    new_location = new_location.split('&');
			    window.location = new_location[0];
			  }
			  else
			  {
				  alert(response.responseJSON.error);
			  }
		  }
		});
	}

	function licenceRemoval()
	{
	    if($$('#listHosts input[type="checkbox"]:checked').length === 0 && $$('#listTenants input[type="checkbox"]:checked').length === 0)
        {
            return;
        }

        $('licence_action').value = 'delete';
        if ($$('#listHosts input[type="checkbox"]:checked').length)
        {
            if (confirm($LC_SURE_DELETE_MULTI_LICENCE))
            {
                $('licence_action_multi_licence').value = '1';
                $('licence_form').submit();
            }
        }
        else if (confirm($LC_SURE_DELETE_LICENCE))
        {
            $('licence_form').submit();
        }
        $('licence_action').value = 'na';
    }

	var hosting_count =  {};

</script>


<div>
	<button type="button" class="btn bold" onclick="$('licences').fade({duration:0.3});new Effect.SlideDown('add-new',{duration:0.4});"><img src="../images/icons/silk/add.png" class="mr5" /><span>Install new licence</span></button>
	<button type="button" class="btn bold" onclick="licenceRemoval();"><img src="../images/icons/silk/delete.png" class="mr5" /><span>Remove selected licence</span></button>
	<img src="../images/ajax-loading.gif" style="margin-top:1px;margin-left:5px;display:none;" id="toolbar_loading" />
</div>

<hr class="separator" />

<form action="?req=licences" method="post" id="licence_form">
<input type="hidden" name="action" id="licence_action" value="na" />
<input type="hidden" name="multiLicenceAction" id="licence_action_multi_licence" value="0" />

[{if empty($licenseToken)}]
    <h3>No weblicense token</h3><br />
    <input type="text" name="license_token" id="license_token" />
    <button type="button" class="btn bold ml15" onclick="$('licence_action').value = 'web_license_set_token';$('licence_form').submit();"><span>Save</span></button>
    <button type="button" class="btn bold ml15" onclick="$('licence_action').value = 'web_license_save_token';$('licence_form').submit();"><span>Save & Check</span></button>
[{else}]
    <h3>Weblicense token installed</h3><br />
    <input type="password" value="[{$licenseToken}]" disabled=disabled />
    <button type="button" class="btn bold ml15" onclick="$('licence_action').value = 'web_license_check_licenses';$('licence_form').submit();"><span>Check for licenses</span></button>
    <button type="button" class="btn bold ml15" onclick="if (confirm('This will remove all weblicenses, are you sure to permit this operation?')) { $('licence_action').value = 'web_license_remove_token';$('licence_form').submit(); }"><span>Remove</span></button>
<br /><br />[{$lastCommunicationLog}]
[{/if}]

[{if is_array($licences) && count($licences) > 0}]
    <h3>Licenses</h3><br />

    <b>Tenant licenses</b>: [{$totalTenants}]<br />
    <b>Object licenses</b>: <span id="total_license_objects">[{$totalLicenseObjects}]</span>, In use: [{$licenseObjectsUsed}]<br /><br />

    <b>Licensed Add-ons:</b><br /><br/>

    [{foreach from=$licensedAddOns key=$addOnKey item=$addOn}]
        [{if $addOn.licensed}] <img src="../images/icons/silk/tick.png" /> [{$addOn.label}]&nbsp;&nbsp;&nbsp;[{/if}]
    [{/foreach}]
    <br/>
    <br/>

    <table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10" id="listTenants">
        <colgroup>
            <col width="30" />
            <col width="30" />
            <col width="120" />
            <col width="120" />
            <col width="180" />
            <col width="180" />
            <col width="180" />
            <col width="100" />
            <col width="100" />
            <col width="100" />
            <col width="100" />
        </colgroup>
        <thead>
        <tr>
            <th>Product</th>
            <th>&nbsp;[ ]</th>
            <th>License</th>
            <th>Created</th>
            <th>Valid until</th>
            <th>Tenants</th>
            <th>Objects</th>
            <th>Environment</th>
        </tr>
        </thead>
        <tbody>

        [{foreach from=$licences key=licenseType item=licensesDetailed name="mainIteration"}]
            <tr>
                <td rowspan="[{count($licensesDetailed)}]"[{if !$smarty.foreach.mainIteration.last}] class="group-border"[{/if}]>[{$licenseType}]</td>
            [{foreach name="licenseIteration" from=$licensesDetailed key=licenseId item=license}]
            [{if $smarty.foreach.licenseIteration.first}]
                <td[{if $license.invalid}] class="invalid"[{/if}]><input type="checkbox" name="id[]" value="0,[{$licenseId}]" /></td>
                <td[{if $license.invalid}] class="invalid"[{/if}]>[{$license.label}]</td>
                <td[{if $license.invalid}] class="invalid"[{/if}]>[{$license.start}]</td>
                <td[{if $license.invalid}] class="invalid"[{/if}]>[{$license.end}]</td>
                <td[{if $license.invalid}] class="invalid"[{/if}]>[{$license.tenants}]</td>
                <td[{if $license.invalid}] class="invalid"[{/if}]>[{$license.objects}]</td>
                <td[{if $license.invalid}] class="invalid"[{/if}]>[{$license.environment}]</td>
            </tr>
            [{else}]
            <tr>
                <td class="[{if $license.invalid}] invalid[{/if}][{if $smarty.foreach.licenseIteration.last}] group-border[{/if}]"><input type="checkbox" name="id[]" value="0,[{$licenseId}]" /></td>
                <td class="[{if $license.invalid}] invalid[{/if}][{if $smarty.foreach.licenseIteration.last}] group-border[{/if}]">[{$license.label}]</td>
                <td class="[{if $license.invalid}] invalid[{/if}][{if $smarty.foreach.licenseIteration.last}] group-border[{/if}]">[{$license.start}]</td>
                <td class="[{if $license.invalid}] invalid[{/if}][{if $smarty.foreach.licenseIteration.last}] group-border[{/if}]">[{$license.end}]</td>
                <td class="[{if $license.invalid}] invalid[{/if}][{if $smarty.foreach.licenseIteration.last}] group-border[{/if}]">[{$license.tenants}]</td>
                <td class="[{if $license.invalid}] invalid[{/if}][{if $smarty.foreach.licenseIteration.last}] group-border[{/if}]">[{$license.objects}]</td>
                <td class="[{if $license.invalid}] invalid[{/if}][{if $smarty.foreach.licenseIteration.last}] group-border[{/if}]">[{$license.environment}]</td>
            </tr>
            [{/if}]

            [{/foreach}]
        [{/foreach}]
        </tbody>
    </table>
[{/if}]

</form>

<style>
    table.sortable tr:hover {
        background-color: transparent !important;
    }

    #listTenants td.invalid {
        background-color: #ffdddd !important;
    }

    #listTenants td.group-border {
        border-bottom: 1px solid #aaa;
    }
</style>

<script type="text/javascript">
	$$('input.object_count').invoke('on', 'keypress', function (ev) {
		if (ev.keyCode == Event.KEY_RETURN)
		{
			ev.preventDefault();
			$('licence_action').value='save';
			$('licence_form').submit();
		}
	});
</script>
