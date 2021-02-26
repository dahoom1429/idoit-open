<div class="p10">
	<h2>[{isys type="lang" ident="LC__MASS_CHANGE__CREATE_NEW_TEMPLATE"}]</h2>

	<div>
		<table class="p5" cellspacing="4">
			<tr>
				<td><label for="object_type">[{isys type="lang" ident="LC__CMDB__OBJTYPE"}] wählen</label></td>
				<td>
					[{isys
						id="object_type"
						name="object_type"
						type="f_dialog"
						p_bInfoIconSpacer="0"
						p_bDbFieldNN=1
						status=0
						exclude="C__OBJTYPE__CONTAINER;C__OBJTYPE__LOCATION_GENERIC;C__OBJTYPE__RELATION"
						p_bEditMode=1
						p_strTable="isys_obj_type"
						sort=true
						p_strStyle="margin-left:15px;"}]
				</td>
			</tr>
		</table>

		<p>
			<strong>[{isys type='lang' ident='LC__CMDB__LOGBOOK__DESCRIPTION'}]</strong><br />
			[{isys type='lang' ident='LC__MASS_CHANGE__DESCRIPTION_CONTENT'}]
		</p>

		<br />

		[{isys type="f_button" p_onClick="obj_create(\$('object_type').value);" name="C__MASS_CHANGE__CREATE_NEW_TEMPLATE" p_strValue="LC__MASS_CHANGE__CREATE_NEW_TEMPLATE" p_bEditMode="1"}]
	</div>
</div>

<script type="text/javascript">
	function obj_create(p_obj_id) {
		$$('input[name=template]')[0].value = '[{$smarty.const.C__RECORD_STATUS__MASS_CHANGES_TEMPLATE}]';
		$$('input[name=navMode]')[0].value = '[{$smarty.const.C__NAVMODE__NEW}]';
		$('isys_form').action = '?' + C__CMDB__GET__VIEWMODE + '=1001&' + C__CMDB__GET__OBJECTTYPE + '=' + p_obj_id;
		$('isys_form').submit();
	}
</script>