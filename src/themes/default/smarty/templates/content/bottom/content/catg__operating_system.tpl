<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION" ident="LC__CATG__OPERATING_SYSTEM"}]</td>
        <td class="value">[{isys title="LC__BROWSER__TITLE__SOFTWARE" name="C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION" type="f_popup" p_strPopupType="browser_object_ng" callback_accept="$('C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN').fire('softwareSelection:updated');" callback_detach="$('C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN').fire('softwareSelection:updated');"}]</td>
    </tr>
    <tr>
        <td colspan="2"><hr class="mt5 mb5" /></td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__OPERATING_SYSTEM_LICENSE__VIEW" ident="LC__CMDB__CATG__LIC_ASSIGN__LICENSE"}]</td>
        <td class="value">
            [{isys
                title="LC__POPUP__BROWSER__LICENSE_TITLE"
                name="C__CATG__OPERATING_SYSTEM_LICENSE"
                type="f_popup"
                p_strPopupType="browser_object_ng"
                secondSelection="true"
                secondList="isys_cmdb_dao_category_s_lic::object_browser"
                secondListFormat="isys_cmdb_dao_category_s_lic::format_selection"}]
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__OPERATING_SYSTEM_IT_SERVICE__VIEW" ident="LC__CMDB__CATG__IT_SERVICE"}]</td>
        <td class="value">[{isys name="C__CATG__OPERATING_SYSTEM_IT_SERVICE" type="f_popup" p_strPopupType="browser_object_ng" multiselection=true}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__OPERATING_SYSTEM_VARIANT__VARIANT" ident="LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATG__OPERATING_SYSTEM_VARIANT__VARIANT"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__OPERATING_SYSTEM_VERSION" ident="LC__CATG__VERSION_TITLE"}]</td>
        <td class="value">
            <div id="C__CATG__OPERATING_SYSTEM_VERSION_HIDER" class="bg-white opacity-50 hide" style="position: absolute; width: 100%;height: 24px">
                <!-- This layer will be used to prevent selection/creation of versions when no operating system is selected. -->
            </div>
            [{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__OPERATING_SYSTEM_VERSION"}]
        </td>
    </tr>
</table>

<div class="hide">
    <!-- Invisible fields, in case someone added data via application category. -->
    [{isys name="C__CATG__OPERATING_SYSTEM_DATABASE_SCHEMATA" type="f_text"}]
    [{isys name="C__CATG__OPERATING_SYSTEM_BEQUEST_NAGIOS_SERVICES" type="f_dialog"}]
    [{isys name="C__CATG__OPERATING_SYSTEM_TYPE" type="f_dialog"}]
</div>

<script type="text/javascript">
    (function () {
        "use strict";

        var $operating_system = $('C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN'),
            $variant = $('C__CATG__OPERATING_SYSTEM_VARIANT__VARIANT'),
            $version = $('C__CATG__OPERATING_SYSTEM_VERSION'),
            $version_hider = $('C__CATG__OPERATING_SYSTEM_VERSION_HIDER'),
            $version_container;

        if ($version) {
            $version_container = $version.up('td');
        }

        if ($operating_system) {
            if (! $operating_system.getValue()) {
                $version_hider.removeClassName('hide');
            }

            $operating_system.on('softwareSelection:updated', function () {
                var selection = $operating_system.getValue(),
                    hideVersionDialog = true,
                    smartyParameters = {
                        'name':'C__CATG__OPERATING_SYSTEM_VERSION',
                        'p_strPopupType':'dialog_plus',
                        'p_strClass': 'input-small',
                        'p_strTable': 'isys_catg_version_list',
                        'id':'C__CATG__OPERATING_SYSTEM_VERSION',
                        'p_dataCallback':          [
                            'isys_cmdb_dao_category_g_application',
                            'getVersionList'
                        ],
                    };

                if(selection == '') {
                    // Case for the detach
                    smartyParameters.condition = 'isys_catg_version_list__isys_obj__id = FALSE';
                    hideVersionDialog = false;
                } else {
                    // Set the smarty condition, after we evaluated the selection.
                    smartyParameters.condition = 'isys_catg_version_list__isys_obj__id = ' + parseInt(selection);
                }

                smartyParameters.p_dataCallbackParameter = [parseInt(selection)];
                smartyParameters.p_strCatTableObj = parseInt(selection);

                new Ajax.Request('[{$smarty_ajax_url}]', {
                    parameters: {
                        'plugin_name': 'f_popup',
                        'parameters': Object.toJSON(smartyParameters)
                    },
                    method: "post",
                    onComplete: function (response) {
                        var json = response.responseJSON;

                        if (Object.isUndefined(json)) {
                            idoit.Notify.error(response.responseText);
                            return;
                        }

                        if (json.success) {
                            $version_container
                                .update(new Element('div', {id:'C__CATG__OPERATING_SYSTEM_VERSION_HIDER', className:'bg-white opacity-50' + (hideVersionDialog ? ' hide' : ''), style:"position: absolute; width: 100%;height: 24px"}))
                                .insert(json.data);

                            $version = $('C__CATG__OPERATING_SYSTEM_VERSION');
                            $version_hider = $('C__CATG__OPERATING_SYSTEM_VERSION_HIDER');
                        } else {
                            idoit.Notify.error(json.message);
                        }
                    }
                });

                new Ajax.Request('[{$application_ajax_url}]', {
                    parameters: {
                        '[{$smarty.const.C__CMDB__GET__OBJECT}]': selection
                    },
                    method: "post",
                    onComplete: function (response) {
                        var i, json = response.responseJSON;

                        if (json.success) {
                            $variant.update(new Element('option', {value: '-1', selected: true}).insert('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));

                            for (i in json.data) {
                                if (json.data.hasOwnProperty(i)) {
                                    $variant.insert(new Element('option', {value: i}).insert(json.data[i]));
                                }
                            }
                        } else {
                            idoit.Notify.error(json.message);
                        }
                    }
                });
            });
        }
    }());
</script>
