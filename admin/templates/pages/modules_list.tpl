<form action="?req=modules" method="post" id="modules_form">
	<input type="hidden" name="action" id="action" value="na" />

	[{foreach $modules as $mandator}]
		<fieldset class="overview" style="margin-top:10px;">
			<legend><span>[{$mandator.title}] [{if $mandator.expires}]- Valid until [{$mandator.expires|date_format:"%A, %B %e, %Y"}][{/if}]</span></legend>

			<table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10">
				<colgroup>
					<col width="30" />
					<col width="200" />
					<col width="100" />
					<col width="100" />
					<col width="200" />
					<col width="150" />
					<col width="160" />
				</colgroup>
				<thead>
					<tr>
						<th></th>
						<th>Add-on Title</th>
						<th>Version</th>
						<th>Type</th>
						<th>Author</th>
						<th>Active</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				[{foreach $mandator.modules as $row}]
				<tr class="[{cycle values="even,odd"}]">
					<td>
						[{if $row.type != 'core' || !$row.active}]
						<input type="checkbox" name="module[[{$mandator.id}]][]" value="[{$row.identifier}]" />
						[{else}]
						<input type="checkbox" name="disabled_checkbox" disabled="disabled" title="You cannot disable or uninstall core add-ons." />
						[{/if}]
					</td>
					<td>[{if $row.title}][{$row.title}][{else}][{$row.name}][{/if}]</td>
					<td>[{$row.version}]</td>
					<td>[{if $row.type == 'addon'}]Add-on[{else}][{$row.type}][{/if}]</td>
					<td>[{$row.author}]</td>
					<td>
						[{if $row.installed == 0}]
							<span class="red"><img src="../images/icons/silk/bullet_red.png" class="vam" border="0" alt="-" /> Not installed</span>
						[{elseif $row.installed > 0}]
							<span class="[{if $row.active}]green"><img src="../images/icons/silk/bullet_green.png" class="vam" border="0" alt="+" /> Active[{else}]red"><img src="../images/icons/silk/bullet_red.png" class="vam" border="0" alt="-" /> Inactive[{/if}]</span>
						[{/if}]

					</td>
					<td>
						[{if $row.installed == 0 || $row.installed == 2}]
							<button type="button" class="install-module btn bold" data-type="install" data-tenant="[{$mandator.id}]" data-tenant-name="[{$mandator.title}]" data-identifier="[{$row.identifier}]">
								<img src="../images/icons/silk/brick_add.png" class="mr5"><span>Install</span>
							</button>
						[{elseif $row.update}]
							<button type="button" class="install-module btn" data-type="update" data-tenant="[{$mandator.id}]" data-version="[{$row.version}]" data-tenant-name="[{$mandator.title}]" data-identifier="[{$row.identifier}]">
								<img src="../images/icons/silk/arrow_refresh.png" class="mr5"><span>Update / Re-Install</span>
							</button>
						[{/if}]
					</td>
				</tr>
				[{/foreach}]
				</tbody>
			</table>
		</fieldset>

	[{/foreach}]
</form>

<script type="text/javascript">
    "use strict";

    $('modules_form').on('click', 'button.install-module', function (event, element) {
        var module = element.getAttribute('data-identifier'),
            tenant = element.getAttribute('data-tenant'),
            tenantName = element.getAttribute('data-tenant-name'),
            confirmation = 'Are you sure you want to {0} add-on "{1}" in tenant "{2}"?'
	            .replace('{0}', element.getAttribute('data-type'))
                .replace('{1}', module)
                .replace('{2}', tenantName);

        if (confirm(confirmation)) {
            new Ajax.Request('?req=modules&action=lazyinstall', {
                method:     'POST',
                parameters: {
                    'module': module,
                    'tenant': tenant
                },
                onComplete: function (xhr) {
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.success) {
                            alert(xhr.responseJSON.message);

	                        document.location.reload(true);
                        } else {
                            alert(response.responseJSON.error);
                        }
                    } else {
                        alert('Unknown error');
                    }
                }
            });
        }
    });
</script>