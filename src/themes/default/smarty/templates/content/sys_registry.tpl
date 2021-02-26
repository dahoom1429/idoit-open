<!-- ToDO: use this template????? -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
 <tr>
  <td valign="top">
   <!-- content view //-->
   <table width="300">
    <tr>
     <td class="key">Key: </td>
     <td class="value"><input class="input input-small" type="f_text" name="reg_key" value="[{$regdata.key}]" /></td>
    </tr>
    <tr>
     <td class="key">Value: </td>
     <td class="value">
      <textarea class="input" name="reg_val">[{$regdata.val}]</textarea>
     </td>
    </tr>
    <tr>
     <td>Deletable?</td>
     <td>
      <input type="checkbox" name="reg_deletable" />
     </td>
    </tr>
    <tr>
     <td>Editable?</td>
     <td>
      <input type="checkbox" name="reg_editable" />
     </td>
    </tr>
    <tr>
     <td colspan="2">
      <div style="font-weight: bold; color: #FF0000">
       [{$regdata.error}]
      </div>
     </td>
    </tr>
    <tr>
     <td colspan="2" nowrap="nowrap">
      <!-- ToDo: use isys function for buttons! -->
      <input type="f_button" p_onClick="this.form.reg_action.value='save'; this.form.submit();" value="Save" />
      <input type="f_button" p_onClick="this.form.reg_action.value='delete'; this.form.submit();" value="Delete" />
      <input type="f_button" p_onClick="this.form.reg_action.value='create_child'; this.form.submit();" value="Create child" />
      <input type="reset" value="Reset form" />
      <input type="hidden" name="reg_action" value="" />
      <input type="hidden" name="reg_id" value="[{$regdata.id}]" /></td>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>