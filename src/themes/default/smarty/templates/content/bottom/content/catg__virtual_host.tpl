<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__VIRTUAL_HOST__YES_NO' ident="LC__CMDB__OBJTYPE__VIRTUAL_HOST"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__VIRTUAL_HOST__YES_NO" p_bDbFieldNN="1" tab="70"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__VIRTUAL_HOST__LICENSE_SERVER__VIEW' ident="LC__CMDB__CATG__CLUSTER__LICENSE_SERVER"}]</td>
		<td class="value">
			[{isys
				name="C__CATG__VIRTUAL_HOST__LICENSE_SERVER"
				type="f_popup"
				p_strPopupType="browser_object_ng"}]
		</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CATG__VIRTUAL_HOST__ADMINISTRATION_SERVICE__VIEW' ident="LC__CMDB__CATG__CLUSTER__ADMINISTRATION_SERVICE"}]</td>
      	<td class="value">
            [{isys
                name="C__CATG__VIRTUAL_HOST__ADMINISTRATION_SERVICE"
                type="f_popup"
                p_strPopupType="browser_object_relation"
                relationFilter="C__RELATION_TYPE__SOFTWARE"}]
    	</td>
    </tr>

</table>
