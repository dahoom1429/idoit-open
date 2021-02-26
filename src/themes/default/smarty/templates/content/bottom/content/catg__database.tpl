<table class='contentTable'>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE__ASSIGNED_DBMS' ident='LC__CATG__DATABASE__ASSIGNED_DBMS'}]</td>
        <td class='value'>
            [{isys
            title="LC__BROWSER__TITLE__CONTACT"
            name="C__CATG__DATABASE__ASSIGNED_DBMS"
            type="f_popup"
            p_strPopupType="browser_object_ng"}]
        </td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE__INSTANCE_NAME' ident='LC__CATG__DATABASE__INSTANCE_NAME'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE__INSTANCE_NAME'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE__INSTANCE_TYPE' ident='LC__CATG__DATABASE__INSTANCE_TYPE'}]</td>
        <td class='value'>[{isys type='f_popup' p_strPopupType='dialog_plus' name='C__CATG__DATABASE__INSTANCE_TYPE' p_strClass='input'}]</td>
    </tr>

    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE__MANUFACTURER' ident='LC__CATG__DATABASE__MANUFACTURER'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE__MANUFACTURER'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE__VERSION' ident='LC__CATG__DATABASE__VERSION'}]</td>
        <td class='value'>[{isys type='f_popup' p_strPopupType="dialog_plus" name='C__CATG__DATABASE__VERSION'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE__PATH' ident='LC__CATG__DATABASE__PATH'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE__PATH'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE__PORT' ident='LC__CATG__DATABASE__PORT'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE__PORT'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE__PORT_NAME' ident='LC__CATG__DATABASE__PORT_NAME'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE__PORT_NAME'}]</td>
    </tr>
</table>
<script language="JavaScript" type="text/javascript">
    (function () {
        "use strict";

        var $version      = $('C__CATG__DATABASE__VERSION'),
            $version_container,
            objectId = '[{$objectId}]',
            $application  = $('C__CATG__DATABASE__ASSIGNED_DBMS__HIDDEN'),
            $dialogOpener;

        function setApplicationData (applicationId){
            new Ajax.Request(
                '[{$ajaxUrl}]',
                {
                    parameters: {
                        'applicationId': parseInt(applicationId),
                        'objectId': parseInt(objectId)
                    },
                    method:     'post',
                    onComplete: function (xhr) {
                        var json = xhr.responseJSON;
                        if (json.success) {
                            $('C__CATG__DATABASE__MANUFACTURER').value = (json.data['manufacturer'] ? json.data['manufacturer']: null);
                            $('C__CATG__DATABASE__VERSION').value = (json.data['version'] ? json.data['version']: null);
                        } else {
                            idoit.Notify.error(json.message);
                        }

                    }.bind(this)
                });
        };

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
                    'name':                    'C__CATG__DATABASE__VERSION',
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

                                $version = $('C__CATG__DATABASE__VERSION');
                                setApplicationData(selection);

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
            });
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
    }());
</script>