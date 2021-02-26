<table class="contentTable">
    <tr>
        <td class="key">
            [{isys type='f_label' name='C__CATS__OBJECT_GROUP__TYPE' ident="LC__CMDB__CATS__GROUP_TYPE__TYPE"}]
        </td>
        <td class="value">
            [{isys type="f_dialog" name="C__CATS__OBJECT_GROUP__TYPE" p_bDbFieldNN="1" p_strClass="normal" tab="70"}]
        </td>
    </tr>
    <tr id="reportList" style="display:none;">
        <td class="key">
            [{isys type='f_label' name='C__CATS__OBJECT_GROUP__REPORT' ident="LC__CMDB__CATS__GROUP_TYPE__REPORT"}]
        </td>
        <td class="value">
            [{isys type="f_dialog" name="C__CATS__OBJECT_GROUP__REPORT" p_bDbFieldNN="0" p_strClass="normal" tab="70"}]
        </td>
    </tr>
</table>
<script type="text/javascript">
    [{$js_show_reportList}]

    if($('C__CATS__OBJECT_GROUP__TYPE'))
    {
        $('C__CATS__OBJECT_GROUP__TYPE').on('change', function(){
            if(this.value == '0')
            {
                $('reportList').hide();
                $('C__CATS__OBJECT_GROUP__REPORT').selectedIndex = 0;
            }
            else
            {
                $('reportList').show();
            }
        });
    }
</script>