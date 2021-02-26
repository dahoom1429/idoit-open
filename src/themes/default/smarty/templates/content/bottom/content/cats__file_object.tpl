<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__FILE__ASSIGNMENT_TYPE' ident="LC__CMDB__CATS__FILE_OBJECT__ASSIGNMENT_TYPE"}]</td>
        <td class="value">
            [{isys type="f_dialog" name="C__CATS__FILE__ASSIGNMENT_TYPE"}]
        </td>
    </tr>
    <tr>
       <td class="key">[{isys type='f_label' name='C__CATS__FILE__OBJECT' ident="LC__CMDB__CATS__FILE_OBJECT__ASSIGNED_OBJECT"}]</td>
        <td class="value">
            [{isys
            title="LC__BROWSER__TITLE__CONTACT"
            name="C__CATS__FILE__OBJECT"
            type="f_popup"
            p_strPopupType="browser_object_ng"
            multiselection="false"
            p_bReadonly="1"}]
        </td>
    </tr>
</table>
<hr/>
<table class="contentTable categoryExtension" id="categoryExtension_[{$smarty.const.C__CATG__FILE}]">
    <tr>
        [{*
            @todo This one should be a link like it is in C__CATG__FILE
        *}]
        <td class="key">[{isys type='f_label' name='C__CATS__FILE__FILE_LINK' ident="LC__CMDB__CATG__FILE__FILE_LINK"}]</td>
        <td class="value">
            [{isys
                type="f_text"
                name="C__CATS__FILE__FILE_LINK"
            }]
        </td>
    </tr>
</table>

<table class="contentTable categoryExtension" id="categoryExtension_[{$smarty.const.C__CATG__MANUAL}]">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__FILE__MANUAL_TITLE' ident="LC__CMDB__CATG__MANUAL_TITLE"}]</td>
        <td class="value">
            [{isys
            type="f_text"
            name="C__CATS__FILE__MANUAL_TITLE"
            }]
        </td>
    </tr>
</table>

<table class="contentTable categoryExtension" id="categoryExtension_[{$smarty.const.C__CATG__EMERGENCY_PLAN}]">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__FILE__EMERGENCY_PLAN_TITLE' ident="LC__CMDB__CATG__EMERGENCY_PLAN_TITLE"}]</td>
        <td class="value">
            [{isys
            type="f_text"
            name="C__CATS__FILE__EMERGENCY_PLAN_TITLE"
            }]
        </td>
    </tr>
</table>

<script type="text/javascript">
    (function() {
        // ViewMode handling
        if (!$('C__CATS__FILE__ASSIGNMENT_TYPE')) {
            // Hide all extension sections
            $$('.categoryExtension').each(Element.hide);

            // Display desired one only
            $('categoryExtension_[{$assignedCategoryConstant}]').show();
        }

        $('C__CATS__FILE__ASSIGNMENT_TYPE').on('change', function(evt, element) {
            // Stop propagation
            evt.stopPropagation();

            $$('.categoryExtension').each(Element.hide);

            $('categoryExtension_' + element.value).show();
        });

        $('C__CATS__FILE__ASSIGNMENT_TYPE').simulate('change');
    })();
</script>