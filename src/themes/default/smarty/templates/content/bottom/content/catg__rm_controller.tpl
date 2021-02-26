<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT" ident="LC__CMDB__CATG__RM_CONTROLLER__ASSIGNED_OBJECT"}]</td>
        <td class="value">
            [{isys
            name="C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT"
            type="f_popup"
            p_strPopupType="browser_object_ng"
            catFilter="C__CATG__IP"
            callback_accept="$('C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT__HIDDEN').fire('rmcSelection:updated');"
            callback_detach="$('C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT__HIDDEN').fire('rmcSelection:removed');"}]
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__RM_CONTROLLER__PRIMARY_URL_READONLY" ident="LC__CMDB__CATG__RM_CONTROLLER__PRIMARY_URL_READONLY"}]</td>
        <td class="value">
            [{isys type="f_link" name="C__CATG__RM_CONTROLLER__PRIMARY_URL_READONLY" p_bReadonly=true}]
        </td>
    </tr>
</table>
<script type="text/javascript">
    (function () {
        "use strict";

        var $assigned_object = $('C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT__HIDDEN');

        if($assigned_object)
        {
            $assigned_object.on('rmcSelection:removed', function(){
                $('C__CATG__RM_CONTROLLER__PRIMARY_URL_READONLY').value = '';
            });

            $assigned_object.on('rmcSelection:updated', function(){
                var object_selection = $assigned_object.getValue();

                new Ajax.Request('[{$rm_controller_ajax_url}]',
                {
                    parameters: {
                        rmc_object: object_selection
                    },
                    method: "post",
                    onComplete: function (data) {
                        // Evaluate json response.
                        $('C__CATG__RM_CONTROLLER__PRIMARY_URL_READONLY').value = data.responseText;
                    }
                });

            })
        }

    }());
</script>