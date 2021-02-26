<script type="text/javascript">
	var LC_PW_UNEQUAL = 'Passwords are unequal.';
	var LC_NO_MANDATOR_SELECTED = 'No tenant selected!';
	var LC_DELETE_CONFIRM = "Do you really want to delete the selected tenant(s)?\n\n" +
	                        "Note that you lose all your data inside this or these tenant(s) !!" +
	                        "\n\nWithout a separate backup is it not possible to recover your data! " +
	                        "If you just want to disable a tenant, use deactivate.";

	/**
	 * Check database name for correctness
	 */
	function CheckDatabaseName($el, message, val) {
		var strVal = $el.getValue();

		if (strVal.search(/\W+/) > 0 || strVal.blank())
		{
			alert(message);
			$el.setValue(val).removeClassName('success');
		}
		else
		{
			$el.addClassName('success');
		}
	}

	/**
	 * Check Auto-Increment start value
	 */
	function checkAutoInc($el, message) {
		var strVal = $el.getValue();

		if (strVal <= 0 || strVal == '')
		{
			alert(message);
			$el.setValue('1').removeClassName('success');
		}
		else
		{
			$el.addClassName('success');
		}
	}

	/**
	 * Plausibility check
	 */
	function formCheck() {
		if ($('mandator_password').getValue() != $('mandator_password2').getValue())
		{
			alert(LC_PW_UNEQUAL);
			return false;
		}

		return true;
	}

	/**
	 * Show editable mandator form
	 */
	function edit_mandator() {
		var $checkbox = $('mandators').down('input[name="id[]"]:checked');

		if ($checkbox) {
			$('ajax_result').hide();
			$('mandators').hide();
			$('toolbar_loading').show();
			new Ajax.Updater('mandator_edit', '?req=mandator&action=edit',
					{
						parameters: {id: $checkbox.getValue()},
						onComplete: function () {
							$('mandator_edit').appear();
							$('toolbar_loading').hide();
						}
					});
		}
		else
		{
			$('ajax_result').update(LC_NO_MANDATOR_SELECTED).appear();
		}
	}

	/**
	 * Submit mandator action (activate, deactivate, delete)
	 */
	function submit_mandators(p_action) {

		$('toolbar_loading').show();
		var arIds = [],
            licenseObjectCounts = {};
		$A(document.getElementsByName('id[]')).each(function (node) {
			if (node.checked) arIds.push(node.value);
		});

        $A(document.getElementsByName('license_objects[]')).each(function (node) {
            licenseObjectCounts[node.getAttribute('data-mandator-id')] = node.value;
        });

        var formData = $('add_form').serialize(true);
		formData['ids'] = Object.toJSON(arIds);
		formData['license_object_counts'] = Object.toJSON(licenseObjectCounts);
		formData['active_license_distribution'] = document.getElementsByName('active_license_distribution')[0].checked;

		new Ajax.Updater('ajax_result', '?req=mandator&action=' + p_action,
				{
					parameters: formData,
					onComplete: function (transport) {
						$('ajax_result').appear().highlight();
						$('toolbar_loading').hide();
						window.transportHandler(transport);

                        reload_mandators();
					}
				});

	}

	/**
	 * Reload mandator list
	 */
	function reload_mandators() {
		new Ajax.Updater('mandators', '?req=mandator&action=list');
		if (!$('mandators').visible()) new Effect.SlideDown('mandators', {duration: 0.3});
	}

	/**
	 * Save edited mandator
	 */
	function save_mandator() {

		$('edit_loading').show();
		new Ajax.Updater('ajax_result', '?req=mandator&action=edit',
				{
					parameters:  $('edit_form').serialize(true),
					evalScripts: true,
					onComplete:  function (transport) {
						$('ajax_result').show().highlight();
						$('edit_loading').hide();

						if (window.transportHandler(transport))
						{
							$('mandator_edit').hide();
							reload_mandators();
						}
					}
				});

	}

	window.transportHandler = function (transport) {
		var jsonObject = transport.responseJSON;

		if (jsonObject)
		{

			$('ajax_result').update(jsonObject.message).setStyle({backgroundColor: ''});

			if (jsonObject.error)
			{
				$('ajax_result').className = 'error p10 mb10';

				return false;
			}
			else
			{
				$('ajax_result').className = 'note p10 mb10';

				return true;
			}

		}

		return false;
	};

	function delete_mandators() {
		if (confirm(LC_DELETE_CONFIRM))
		{
			submit_mandators('delete');
		}
	}

</script>

<div class="gradient content-header">
	<img src="../images/icons/silk/database_table.png" class="vam mr5" /><span class="bold text-shadow headline vam">Tenants</span>
</div>

<div id="innercontent">
	[{if $error}]
		<div id="error" class="error p10 mb10"><strong>Error:</strong><br /><br />[{$error}]</div>
	[{/if}]

	[{if $output}]
		<div id="note" class="note p10 mt0">[{$output}]</div>
	[{/if}]

	<div id="ajax_result" class="note p10 mb10" style="display:none;"></div>

	<form id="add_form" action="?req=mandator&action=add" method="post">
		<div id="add-new" class="mt10" style="display:none;">
			<fieldset>
				<legend class="bold text-shadow">Add a new tenant</legend>

				<table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10">
					<colgroup>
						<col width="350" />
					</colgroup>
					<tr>
						<th colspan="2">
							<span>Tenant Info</span>
						</th>
					</tr>
					<tr>
						<td class="bold">
							<label for="mandator_title">Tenant GUI title</label>
						</td>
						<td>
							<input type="text" id="mandator_title" name="mandator_title" onfocus="if (this.value=='New Tenant')this.value='';" value="New Tenant" />
						</td>
					</tr>
					<tr>
						<th colspan="2">
					  	<span class="fr">This user will be authorized to the tenant database.
					  	<span class="red">Note that this is NOT an i-doit login!</span></span>
							<span>MySQL user settings</span>
						</th>
					</tr>
					<tr>
						<td class="bold">
							<label for="mandator_username">Username (max. 16 & no special chars)</label>
						</td>
						<td>
							<input onblur="CheckDatabaseName(this, 'Your username has got special charactes. Only a-z & A-Z is allowed here. Please correct your value.', 'idoit');" type="text" id="mandator_username" name="mandator_username" placeholder="idoit" value="idoit" />
							(a-z A-Z)
						</td>
					</tr>
					<tr>
						<td class="bold">
							<label for="mandator_password">Password</label>
						</td>
						<td>
							<input type="password" name="mandator_password" id="mandator_password" value="" />
						</td>
					</tr>
					<tr>
						<td class="bold">
							<label for="mandator_password2">Retype password</label>
						</td>
						<td>
							<input type="password" name="mandator_password2" id="mandator_password2" value="" />
						</td>
					</tr>
					<tr>
						<th colspan="2">
							<span>Database settings</span>
						</th>
					</tr>
					<tr>
						<td class="bold" valign="top">
							<label for="addNewDatabase">New Database</label>
						</td>
						<td>
							<label><input type="checkbox" id="addNewDatabase" name="addNewDatabase" value="1" checked="checked" onchange="" /> Yes</label>
						</td>
					</tr>
					<tr>
						<td class="bold">
							<label for="mandator_database">Tenant Database Name (max. 64 char)</label>
						</td>
						<td>
							<input onblur="CheckDatabaseName(this, 'Be aware that the database name only allow the characters 0-9, a-Z and _. Please correct your value.', 'idoit_data_new');" type="text" id="mandator_database" name="mandator_database" value="idoit_data_new" placeholder="idoit_data_new" />
							(0-9, a-z, A-Z and _)
						</td>
					</tr>
					<tr>
						<td class="bold">
							<label for="mandator_autoinc">Auto-Increment start value</label>
						</td>
						<td>
							<input onblur="checkAutoInc(this, 'Please use a value bigger then 1.'); return false;" type="text" id="mandator_autoinc" name="mandator_autoinc" value="1" />
							(>0)
						</td>
					</tr>
					[{if $db_conf.user != "root" || $smarty.post.root_pw}]
						<tr>
							<th colspan="2">
								<span>MySQL user with ALL privileges and GRANT OPTION</span>
							</th>
						</tr>
                        <tr class="newDatabase">
                            <td class="bold">
                                <label for="root_pw">Username</label>
                            </td>
                            <td>
                                <input type="text" id="root_user" name="root_user" placeholder="root" value=""/>
                            </td>
                        </tr>
						<tr class="newDatabase">
							<td class="bold">
								<label for="root_pw">Password</label>
							</td>
							<td>
								<input type="password" id="root_pw" name="root_pw" value="[{$smarty.post.root_pw}]" />
							</td>
						</tr>
					[{/if}]
				</table>

				<div class="toolbar">
					<a class="bold" href="javascript:" id="btnAddTenant"> Add tenant</a>
					<a class="bold" href="javascript:" onclick="new Effect.SlideUp('add-new', {duration:0.3});new Effect.Appear('mandators',{duration:0.4});if($('ajax_result').hasClassName('error')) $('ajax_result').hide();"> Abort</a>
					<span id="add_loading" style="display:none;"><img src="../images/ajax-loading.gif" class="vam" style="margin-top:1px;margin-left:5px;" /> Tenant is being added, please wait..</span>
				</div>
			</fieldset>
		</div>

		<div id="mandators">
			[{include file="pages/mandator_list.tpl"}]
		</div>
	</form>

	<div id="mandator_edit" style="display:none;"></div>
</div>

<script type="text/javascript">
	$('btnAddTenant').on('click', function () {
		if (formCheck())
		{
			$('add_loading').show();

			new Ajax.Updater('ajax_result', '?req=mandator&action=add',
					{
						parameters:  $('add_form').serialize(true),
						evalScripts: true,
						onComplete:  function (transport) {
							if (window.transportHandler(transport))
							{
								$('add-new').hide();
								reload_mandators();
							}

							$('ajax_result').show().highlight();

							$('add_loading').hide();
						}
					}
			);
		}
	});

	var listenerAdded = false;

	var listenersLicenses = function() {
	    var toggleAttribute = function(element, name, force) {
            if(force !== void 0) force = !!force

            if (element.getAttribute(name) !== null) {
                if (force) return true;

                element.removeAttribute(name);
                return false;
            } else {
                if (force === false) return false;

                element.setAttribute(name, "");
                return true;
            }
        };

        var licenseObjects = document.getElementById('total_license_objects').innerText,
            activeLicenseDistribution = document.getElementsByName("active_license_distribution")[0];

        activeLicenseDistribution.addEventListener("change", function () {
            var valueElements = document.getElementsByClassName('mandator_license_objects');

            for (var i = 0; i < valueElements.length; i++) {
                toggleAttribute(valueElements[i], 'disabled', true);
            }

            var totalObjects = licenseObjects,
                tenants = document.getElementsByClassName('mandator-row'),
                tenantsCount = tenants.length;

            function getIntDividedIntoMultiple(dividend, divisor, multiple)
            {
                var values = [];
                while (dividend> 0 && divisor > 0)
                {
                    var a = Math.round(dividend/ divisor / multiple) * multiple;
                    dividend -= a;
                    divisor--;
                    values.push(a);
                }

                return values;
            }

            var objectCounts = getIntDividedIntoMultiple(totalObjects, tenantsCount, 1);

            for (var i = 0; i < tenants.length; i++) {
                var input = tenants[i].getElementsByClassName('mandator_license_objects')[0];

                input.value = objectCounts[i];
            }
        });

        listenerAdded = true;
    };

    document.addEventListener("DOMContentLoaded", function(event) {
        listenersLicenses();
    });

    document.addEventListener("DOMSubtreeModified", function(event) {
        if (document.getElementsByName("active_license_distribution").length === 0) {
            listenerAdded = false;
        }

        if (document.getElementsByName("active_license_distribution").length !== 0 && listenerAdded !== true) {
            listenersLicenses();
        }
    });
</script>
