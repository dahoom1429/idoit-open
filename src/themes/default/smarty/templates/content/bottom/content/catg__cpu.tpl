<table class="contentTable">
    [{if $new_catg_cpu}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CPU_NUMBER2CREATE' ident="LC__NUMBER2CREATE"}]</td>
        <td class="value">[{isys type="f_count" p_strClass="input-mini" name="C__CATG__CPU_NUMBER2CREATE"}]</td>
    </tr>
    <tr>
        <td colspan="2">
            <hr class="mt5 mb5" />
        </td>
    </tr>
    [{/if}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CPU_CORES' ident="LC__CMDB__CATG__CPU_CORES"}]</td>
        <td class="value">[{isys type="f_count" name="C__CATG__CPU_CORES"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CPU_TITLE' ident="LC__CMDB__CATG__CPU_TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__CPU_TITLE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CPU_MANUFACTURER' ident="LC__CMDB__CATG__CPU_MANUFACTURER"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_catg_cpu_manufacturer" name="C__CATG__CPU_MANUFACTURER"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CPU_TYPE' ident="LC__CMDB__CATG__CPU_TYPE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_catg_cpu_type" name="C__CATG__CPU_TYPE"}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CPU_FREQUENCY' ident="LC__CMDB__CATG__CPU_FREQUENCY"}]</td>
        <td class="value">
            [{isys type="f_text" name="C__CATG__CPU_FREQUENCY"}]
            [{isys type="f_dialog" name="C__CATG__CPU_FREQUENCY_UNIT"}]
        </td>
    </tr>
</table>
