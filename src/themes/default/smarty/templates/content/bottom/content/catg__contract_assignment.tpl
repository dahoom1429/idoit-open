<table class="contentTable">
    <tr >
        <td class="key" style="vertical-align: top;padding-top: 2px;">[{isys type="lang" ident="LC__CMDB__CATG__MAINTENANCE_OBJ_MAINTENANCE"}]</td>
        <td class="value" style="vertical-align: top;">
            <span>
            [{isys
                title="LC__BROWSER__TITLE__CONTRACTS"
                name="C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS"
                type="f_popup"
                p_strPopupType="browser_object_ng"
                callback_accept="idoit.callbackManager.triggerCallback('contract_assignment__get_contract');"}]
            </span>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="p20">
            <table id="contract_information_table" class="w100 border">
                [{if (isset($contract_information))}]
                    [{foreach $contract_information as $title => $row}]
                        <tr>
                            <td class="key">[{isys type="lang" ident=$title}]</td>
                            <td class="value pl20">
                                [{if isset($dateFields[$row]) && strtotime($contract[$row]) !== false}]
                                    [{isys_application::instance()->container->locales->fmt_date($contract[$row])}]
                                [{elseif strstr($row, 'costs') || strstr($row, 'sum')}]
                                    [{isys type="f_money_number" p_strValue=$contract[$row] p_bEditMode=0 p_bInfoIconSpacer=0}]
                                [{elseif $title == 'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE' || $title == 'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD'}]
                                    [{$row}]
                                [{else}]
                                    [{isys type="lang" ident=$contract[$row]}]
                                [{/if}]
                            </td>
                        </tr>
                    [{/foreach}]
                <input type="hidden" id="assigned_contract__startdate" data-view="[{isys_application::instance()->container->locales->fmt_date($contract['isys_cats_contract_list__start_date'])}]" value="[{$contract['isys_cats_contract_list__start_date']}]"/>
                <input type="hidden" id="assigned_contract__enddate" data-view="[{isys_application::instance()->container->locales->fmt_date($contract['isys_cats_contract_list__end_date'])}]" value="[{$contract['isys_cats_contract_list__end_date']}]"/>
                <input type="hidden" id="reaction_rate" value="[{$contract['isys_cats_contract_list__isys_contract_reaction_rate__id']}]"/>
                [{/if}]
            </table>
        </td>
    </tr>
    <tr>
        <td class="key">
            <label for="subcontract" class="fr">[{isys type="lang" ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__ACHIEVEMENT_CERTIFICATE"}]</label>
            <input class="m5 fr" type="checkbox" id="subcontract" name="subcontract" value="1" onClick="idoit.callbackManager.triggerCallback('contract_assignment__handle_subcontract', this);" [{if !$smarty.get.editMode && $smarty.post.navMode != $smarty.const.C__NAVMODE__EDIT}]disabled="disabled"[{/if}]  [{if ($subcontract)}]checked="checked"[{/if}]/>
        </td>
    </tr>
</table>

<table class="contentTable mt5 [{if !($subcontract)}]hide[{/if}]" id="subcontract_table">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START'  ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START" p_strPopupType="calendar" p_bTime="0"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END' ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END" p_strPopupType="calendar" p_bTime="0"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__MAINTENANCE_PERIOD' ident="LC__CMDB__CATS__CONTRACT__MAINTENANCE_END"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATG__CONTRACT_ASSIGNMENT__MAINTENANCE_PERIOD"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__REACTION_RATE" name="C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_contract_reaction_rate" name="C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE"}]</td>
    </tr>
</table>

<script type='text/javascript'>
    (function () {
        "use strict";

        var assignSubcontract = function () {
            var l_start_hidden  = $F('assigned_contract__startdate'),
                l_start_view    = $('assigned_contract__startdate').getAttribute('data-view'),
                l_end_hidden    = $F('assigned_contract__enddate'),
                l_end_view      = $('assigned_contract__enddate').getAttribute('data-view'),
                l_reaction_rate = $('reaction_rate').value,
                subContract     = $('subcontract'),
                startFieldView      = $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW'),
                startFieldHidden    = $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN'),
                endFieldView        = $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW'),
                endFieldHidden      = $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN'),
                reactionRateField   = $('C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE');

            if (l_start_view != "01.01.1970" && subContract.checked)
            {
                if (startFieldHidden.getValue() === '') {
                    startFieldView.setValue(l_start_view);
                    startFieldHidden.setValue(l_start_hidden);
                }
            }
            else
            {
                startFieldView.setValue("");
                startFieldHidden.setValue("");
            }

            if (l_end_view != "01.01.1970" && subContract.checked)
            {
                if (endFieldHidden.getValue() === '') {
                    endFieldView.setValue(l_end_view);
                    endFieldHidden.setValue(l_end_hidden);
                }
            }
            else
            {
                endFieldView.setValue("");
                endFieldHidden.setValue("");
            }

            if (reactionRateField.getValue() < 0 && l_reaction_rate > 0 && subContract.checked)
            {
                reactionRateField.setValue(l_reaction_rate);
            }
        };

        idoit.callbackManager
             .registerCallback('contract_assignment__get_contract', function () {
                 var l_contractID = $F('C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS__HIDDEN');

                 if (l_contractID > 0)
                 {
                     new Ajax.Request('?ajax=1&call=contract', {
                         method:     'post',
                         parameters: {
                             contractID: l_contractID
                         },
                         onSuccess:  function (transport) {
                             $("contract_information_table").update(transport.responseText);
                             assignSubcontract();
                         }
                     });
                 }
             })
             .registerCallback('contract_assignment__handle_subcontract', function ($checkbox) {
                 if ($checkbox.checked && !$checkbox.disabled)
                 {
                     assignSubcontract();
                     $('subcontract_table').removeClassName('hide');
                 }
                 else
                 {
                     $('subcontract_table').addClassName('hide');
                     $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW').setValue('');
                     $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN').setValue('');
                     $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW').setValue('');
                     $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN').setValue('');
                     $('C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE').setValue('');
                 }
             });
        [{if $smarty.get.editMode || $smarty.post.navMode == $smarty.const.C__NAVMODE__EDIT}]
        idoit.callbackManager.triggerCallback('contract_assignment__handle_subcontract', $('subcontract'));
        [{/if}]
    }());
</script>

<style type="text/css">
    #contract_information_table {
        background-color:rgba(0, 0, 0, .05);
    }

    #contract_information_table .key {
        width: 185px;
    }
</style>