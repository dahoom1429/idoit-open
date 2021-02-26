<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT" ident="LC__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT"}]</td>
		<td class="value">
			[{isys
				title="LC__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT"
				name="C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				catFilter='C__CATG__ASSIGNED_USERS'
                callback_accept="$('C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN').fire('assignedObjectSubscription:updated');"
                callback_detach="$('C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN').fire('assignedObjectSubscription:updated');"
				multiselection=false}]
		</td>
	</tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID" ident="LC__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID"}]</td>
    </tr>
</table>
<script type="text/javascript">
    (function () {
        "use strict";

        var $assignedSubscriptionObject = $('C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN'),
            $dialogUuid = $('C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID');

        if ($assignedSubscriptionObject) {
            $assignedSubscriptionObject.on('assignedObjectSubscription:updated', function (){
                var selection = parseInt($assignedSubscriptionObject.getValue()),
                    dialogUuidLength = $dialogUuid.length;

                // Configure smarty parameters
                var smartyParameters = {
                    'name': 'C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID',
                    'p_strPopupType': 'dialog',
                    'p_strClass': 'input-small',
                    'p_dataCallback': [
                        'isys_cmdb_dao_category_g_cloud_subscriptions',
                        'getCloudSubscriptionsByRequest'
                    ],
                    'p_dataCallbackParameter': selection,
                    'conditionValue': selection,
                    'p_strTable': 'isys_catg_cloud_subscriptions_list'
                };

                $dialogUuid.update(new Element('option', {value:-1}).update('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));

                if (selection > 0){
                    new Ajax.Request('[{$smartyAjaxUrl}]', {
                        parameters: {
                            'plugin_name': 'f_dialog',
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
                                $dialogUuid.update(json.data);
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
    }());
</script>