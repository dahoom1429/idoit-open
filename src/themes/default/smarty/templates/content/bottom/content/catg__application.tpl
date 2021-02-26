<table class="contentTable">
	<tr>
		<td class="key">
			[{if $category == 's'}]
				[{isys type="f_label" name="C__CATS__APPLICATION_OBJ_APPLICATION__VIEW" ident="LC_UNIVERSAL__OBJECT"}]
			[{else}]
				[{isys type="f_label" name="C__CATG__APPLICATION_OBJ_APPLICATION__VIEW" ident="LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION"}]
			[{/if}]
		</td>
		<td class="value">
			[{if $category == 's'}]
				[{isys title="LC__BROWSER__TITLE__SOFTWARE" name="C__CATS__APPLICATION_OBJ_APPLICATION" type="f_popup" p_strPopupType="browser_object_ng"}]
			[{else}]
				[{isys title="LC__BROWSER__TITLE__SOFTWARE" name="C__CATG__APPLICATION_OBJ_APPLICATION" type="f_popup" p_strPopupType="browser_object_ng" callback_accept="$('C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN').fire('softwareSelection:updated');" callback_detach="$('C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN').fire('softwareSelection:updated');"}]
			[{/if}]
		</td>
	</tr>
	<tr id="C__CATG__APPLICATION_PRIORITY_ROW" [{if $hide_priority}]style="display:none;"[{/if}]>
		<td class="key">[{isys type="f_label" name="C__CATG__APPLICATION_PRIORITY" ident="LC__CATG__APPLICATION_PRIORITY"}]</td>
		<td class="value">[{isys name="C__CATG__APPLICATION_PRIORITY" type="f_popup" p_strPopupType="dialog_plus" p_strClass="input input-mini"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__APPLICATION_DATABASE_SCHEMATA__VIEW" ident="LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA"}]</td>
		<td class="value">[{isys title="LC__BROWSER__TITLE__DATABASE_SCHEMATA" name="C__CATG__APPLICATION_DATABASE_SCHEMATA" type="f_popup" p_strPopupType="browser_object_ng"}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__APPLICATION_ASSIGNED_DATABASES" ident="LC__CATG__APPLICATION_ASSIGNED_DATABASES"}]</td>
        <td class="value">[{isys title="LC__CATG__APPLICATION_ASSIGNED_DATABASES" name="C__CATG__APPLICATION_ASSIGNED_DATABASES" type="f_popup" p_strPopupType="browser_object_ng"}]</td>
    </tr>
    <tr>
		<td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES" ident="LC__CMDB__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES"}]</td>
		<td class="value">[{isys name="C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES" type="f_dialog" p_strClass="input input-mini"}]</td>
	</tr>
	<tr>
		<td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__LIC_ASSIGN__LICENSE__VIEW" ident="LC__CMDB__CATG__LIC_ASSIGN__LICENSE"}]</td>
		<td class="value">
			[{isys
				title="LC__POPUP__BROWSER__LICENSE_TITLE"
				name="C__CATG__LIC_ASSIGN__LICENSE"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				secondSelection="true"
				secondList="isys_cmdb_dao_category_s_lic::object_browser"
				secondListFormat="isys_cmdb_dao_category_s_lic::format_selection"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__APPLICATION_IT_SERVICE__VIEW" ident="LC__CMDB__CATG__IT_SERVICE"}]</td>
		<td class="value">[{isys name="C__CATG__APPLICATION_IT_SERVICE" type="f_popup" p_strPopupType="browser_object_ng" multiselection=true}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__APPLICATION_VARIANT__VARIANT" ident="LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__APPLICATION_VARIANT__VARIANT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__APPLICATION_VERSION" ident="LC__CATG__VERSION_TITLE"}]</td>

		[{if $category == 's'}]
		<td class="value">[{isys type="f_dialog" name="C__CATG__APPLICATION_VERSION"}]</td>
		[{else}]
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__APPLICATION_VERSION"}]</td>
		[{/if}]
	</tr>
</table>

<div class="hide">[{isys name="C__CATG__APPLICATION_TYPE" type="f_dialog"}]</div>

<script type="text/javascript">
    (function () {
        "use strict";

        var $variant      = $('C__CATG__APPLICATION_VARIANT__VARIANT'),
            $version      = $('C__CATG__APPLICATION_VERSION'),
            $version_container,
            $type         = $('C__CATG__APPLICATION_TYPE'),
            $priority_row = $('C__CATG__APPLICATION_PRIORITY_ROW'),
            $application  = $('C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN'),
            $dialogOpener;

        [{if $hide_priority}]
            // Disable priority field
            if ($('C__CATG__APPLICATION_PRIORITY'))
            {
                $('C__CATG__APPLICATION_PRIORITY').writeAttribute('disabled');
            }
        [{/if}]

        if ($version)
        {
            $dialogOpener = $version.next('a.dialog-plus');

            $version_container = $version.up('td');

            if ($dialogOpener)
            {
                $dialogOpener.store('onclick', $dialogOpener.readAttribute('onclick'));
            }
        }

        if ($application)
        {
            $application.on('softwareSelection:updated', function () {
                // Get selected application objects
                var selection = $application.getValue();

                try
                {
                    // Try to parse JSON
                    selection = JSON.parse(selection);
                }
                catch (e)
                {
                }

                // Configure smarty parameters
                var smartyParameters = {
                    'name':                    'C__CATG__APPLICATION_VERSION',
                    'p_strPopupType':          'dialog_plus',
                    'p_strClass':              'input-small',
                    'p_dataCallback':          [
                        'isys_cmdb_dao_category_g_application',
                        'getVersionList'
                    ],
                    'p_dataCallbackParameter': selection,
                    'p_strTable':              'isys_catg_version_list'
                };

                // Disable the version, because this dialog will be completely reloaded.
                triggerVersion();
                $variant.enable();

                if (Object.isArray(selection))
                {
                    if (selection.length > 1)
                    {
                        $variant.disable();
                        return;
                    }
                    else
                    {
                        selection = selection[0];
                    }
                }

                [{if $category != 's'}]
                // Only reload the version field, if we selected ONE application.
                if ((Object.isString(selection) && !selection.blank()) || (Object.isArray(selection) && selection.length === 1) || Object.isNumber(selection))
                {
                    // Set the smarty condition, after we evaluated the selection.
                    smartyParameters.condition = 'isys_catg_version_list__isys_obj__id = ' + parseInt(selection);
                    smartyParameters.p_strCatTableObj = parseInt(selection);

                    new Ajax.Request('[{$smarty_ajax_url}]', {
                        parameters: {
                            'plugin_name': 'f_popup',
                            'parameters':  Object.toJSON(smartyParameters)
                        },
                        method:     "post",
                        onComplete: function (response) {
                            var json = response.responseJSON;

                            if (Object.isUndefined(json))
                            {
                                idoit.Notify.error(response.responseText);
                                return;
                            }

                            if (json.success)
                            {
                                $version_container.update(json.data);

                                $version = $('C__CATG__APPLICATION_VERSION');

                                if ($version)
                                {
                                    $dialogOpener = $version.next('a.dialog-plus');
                                    $dialogOpener.store('onclick', $dialogOpener.readAttribute('onclick'));
                                }
                                else
                                {
                                    $dialogOpener = null;
                                }

                                triggerVersion();
                            }
                            else
                            {
                                idoit.Notify.error(json.message);
                            }
                        }
                    });
                }
                [{/if}]

                new Ajax.Request('[{$application_ajax_url}]&func=get_variants', {
                    parameters: {
                        '[{$smarty.const.C__CMDB__GET__OBJECT}]': selection
                    },
                    method:     "post",
                    onComplete: function (response) {
                        var i, json = response.responseJSON;

                        if (Object.isUndefined(json))
                        {
                            idoit.Notify.error(response.responseText);
                            return;
                        }

                        if (json.success)
                        {
                            $variant.update(new Element('option', {
                                value:    '-1',
                                selected: true
                            }).insert('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));

                            for (i in json.data)
                            {
                                if (json.data.hasOwnProperty(i))
                                {
                                    $variant.insert(new Element('option', {value: i}).insert(json.data[i]));
                                }
                            }
                        }
                        else
                        {
                            idoit.Notify.error(json.message);
                        }
                    }
                });

                new Ajax.Request('[{$application_ajax_url}]&func=get_type', {
                    parameters: {
                        '[{$smarty.const.C__CMDB__GET__OBJECT}]': selection
                    },
                    method:     "post",
                    onComplete: function (response) {
                        var json = response.responseJSON;

                        if (json.success)
                        {
                            if ($type)
                            {
                                $type
                                    .setValue(json.data)
                                    .fire('softwareType:updated');
                            }
                        }
                        else
                        {
                            idoit.Notify.error(json.message);
                        }
                    }
                });
            });
        }

        if ($type)
        {
            $type.on('softwareType:updated', function () {
                if ($type.getValue() == '[{$smarty.const.C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM}]')
                {
                    $priority_row.show();

                    // Disable priority field
                    if ($('C__CATG__APPLICATION_PRIORITY'))
                    {
                        $('C__CATG__APPLICATION_PRIORITY').writeAttribute('disabled', false);
                    }
                }
                else
                {
                    $priority_row.hide();

                    // Enable priority field
                    if ($('C__CATG__APPLICATION_PRIORITY'))
                    {
                        $('C__CATG__APPLICATION_PRIORITY').writeAttribute('disabled', true);
                    }
                }
            });

            $type.fire('softwareType:updated');
        }

        function triggerVersion() {
            var application;

            if (!$version || !$application)
            {
                disableVersion();
                return;
            }

            try
            {
                application = $application.getValue().evalJSON();
            }
            catch (e)
            {
                application = null;
            }

            if (application === null || (Object.isString(application) && application.blank()) || (Object.isArray(application) && application.length > 1))
            {
                disableVersion();
            }
            else
            {
                enableVersion();
            }
        }

        function disableVersion() {
            if (!$version)
            {
                return;
            }

            $version.disable();

            if ($dialogOpener)
            {
                $dialogOpener
                    .writeAttribute('onclick', null)
                    .addClassName('opacity-50')
                    .addClassName('mouse-default');
            }
        }

        function enableVersion() {
            if (!$version)
            {
                return;
            }

            $version.enable();

            if ($dialogOpener)
            {
                $dialogOpener
                    .writeAttribute('onclick', $dialogOpener.retrieve('onclick'))
                    .removeClassName('opacity-50')
                    .removeClassName('mouse-default');
            }
        }
        [{if $category != 's'}]
        triggerVersion();
        [{/if}]
    }());
</script>
