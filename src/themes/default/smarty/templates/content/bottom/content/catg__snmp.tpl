<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__SNMP_COMMUNITY' ident="SNMP Community"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__SNMP_COMMUNITY" p_strTable="isys_snmp_community" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CATG__IP_ADDRESS"}] ([{isys type="lang" ident="LC__CATP__IP__PRIMARY"}])</td>
		<td class="value">[{isys type="f_data" name="C__CATG__SNMP_HOSTADDRESS"}]</td>
	</tr>
</table>

<div class="contentTable">
    <h3 class="gradient text-shadow p5 border-top border-bottom">SNMP OID's</h3>

	<table id="oids" class="listing">
		<colgroup>
			<col width="200" />
		</colgroup>

	    <thead>
	        <tr>
	            <th>[{isys type="lang" ident="LC__CMDB__LOGBOOK__DESCRIPTION"}]</th>
	            [{if $editmode}]
		        <th>OID</th>
	            [{else}]
	            <th>SNMP-[{isys type="lang" ident="LC__UNIVERSAL__RESULT"}]</th>
	            [{/if}]
	        </tr>
	    </thead>

		<tbody>
			[{foreach $oids as $key => $oid}]
			<tr>
				<td>
					[{if isys_glob_is_edit_mode()}]
						<input type="text" name="C__CATG__SNMP_OID_TITLES[]" value="[{$key}]" class="input input-mini" />
					[{else}]
						[{$key}]
					[{/if}]
				</td>
				<td>
					[{if isys_glob_is_edit_mode()}]
						<input type="text" name="C__CATG__SNMP_OIDS[]" value="[{$oid}]" placeholder="OID" class="input" />
					[{else}]
						[{if $has_primary}]
							[{$snmp->cleanup($snmp->get($oid))|default:"n/a"}]
						[{else}]
							n/a
						[{/if}]
					[{/if}]
				</td>
			</tr>
			[{/foreach}]
        </tbody>
	</table>

    [{if isys_glob_is_edit_mode()}]
	<button type="button" onclick="idoit.callbackManager.triggerCallback('snmp__add_oid');" class="m5 btn">
		<img src="[{$dir_images}]icons/plus-green.gif" class="mr5" alt="+" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
	</button>
	[{/if}]
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		var oid_counter = parseInt('[{$oid_count}]');

		idoit.callbackManager.registerCallback('snmp__add_oid', function() {
			oid_counter ++;

			$('oids').down('tbody').insert(
				new Element('tr').update(
						new Element('td').update(new Element('input', {type:'text', name: 'C__CATG__SNMP_OID_TITLES[]', className: 'input input-mini', value: oid_counter}))
					).insert(
						new Element('td').update(new Element('input', {type:'text', name: 'C__CATG__SNMP_OIDS[]', placeholder:"OID", className: 'input'}))
					));
		});
	}());
</script>