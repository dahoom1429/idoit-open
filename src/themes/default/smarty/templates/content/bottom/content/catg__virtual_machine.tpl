<table class="contentTable pl10 border-bottom">
	<tr>
		<td>
			<label class="ml5">
				<input type="radio" onclick="$('vm_guest').hide();" [{if ($editMode || isys_glob_is_edit_mode())}][{else}]disabled="disabled[{/if}]" [{if $vm == $smarty.const.C__VM__NO}]checked="checked"[{/if}] name="C__CATG__VM__VM" value="[{$smarty.const.C__VM__NO}]" /> [{isys type="lang" ident="LC__CMDB__CATG__VIRTUAL_NO"}]
			</label>
			<label class="ml5">
				<input type="radio" onclick="new Effect.Appear('vm_guest', {duration:0.2});"
					[{if ($editMode || isys_glob_is_edit_mode())}][{else}]disabled="disabled[{/if}]"
					[{if $vm == $smarty.const.C__VM__GUEST}]checked="checked"[{/if}]
					name="C__CATG__VM__VM" value="[{$smarty.const.C__VM__GUEST}]" /> [{isys type="lang" ident="LC__CMDB__CATG__VIRTUAL_MACHINE"}]
			</label>
		</td>
	</tr>
</table>

<table style="border-top: none; display:none;" id="vm_guest" class="contentTable">
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__CATG__VM__RUNNING_ON_HOST"}] / Cluster</td>
		<td class="value">
			[{isys name="C__CATG__VM__OBJECT" type="f_popup" p_strPopupType="browser_object_ng" callback_accept="$('C__CATG__VM__OBJECT__HIDDEN').fire('vmObject:updated');" callback_detach="$('C__CATG__VM__OBJECT__HIDDEN').fire('vmObject:updated');"}]
		</td>
	</tr>
	<tr id="cluster_options" style="[{$cluster_options_display}]">
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VIRTUAL_MACHINE_HOST' ident="LC__CMDB__HOST_IN_CLUSTER"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__VIRTUAL_MACHINE_HOST" id="C__CMDB__CATG__VIRTUAL_MACHINE_HOST"}]</td>
	</tr>

	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__VM__SYSTEM' ident="LC__CMDB__CATG__VIRTUALIZATION_SYSTEM"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_vm_type" name="C__CATG__VM__SYSTEM"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__VM__CONFIG_FILE' ident="LC__CATG__VM__CONFIG_FILE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__VM__CONFIG_FILE"}]</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CATG__VIRTUAL_MACHINE__ADMINISTRATION_SERVICE__VIEW' ident="LC__CMDB__CATG__CLUSTER__ADMINISTRATION_SERVICE"}]</td>
		<td class="value">
			[{isys name="C__CATG__VIRTUAL_MACHINE__ADMINISTRATION_SERVICE" type="f_text" p_bReadonly=1}]
		</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		[{if $vm == $smarty.const.C__VM__GUEST}]
		$('vm_guest').show();
		[{/if}]

		var $vm_host = $('C__CATG__VM__OBJECT__HIDDEN');

		if($vm_host)
		{
			$vm_host.on('vmObject:updated', function(){
				if ($('C__CATG__VM__OBJECT__HIDDEN').value > 0) {
					var $mode;
					$mode = "[{$smarty.post.navMode|strip_tags|escape}]";

					// If the mode is set to save, don't propagate
					// it to the ajax call.
					if ("[{$smarty.post.navMode}]" == "10") {
						$mode = null;
					}

					new Ajax.Request('[{$virtual_machine_ajax_url}]',
							{
								parameters: {
									application_id: $('C__CATG__VM__OBJECT__HIDDEN').value,
									navMode: $mode
								}, method: "post",
								onComplete: function (data) {
									// Evaluate json response.
									var jsonResponse = data.responseText.evalJSON();

									// When dealing with a cluster, populate the dropdown with
									// the json payload and show form section.
									var isCluster = jsonResponse.isCluster;
									var dropdown = ($("C__CMDB__CATG__VIRTUAL_MACHINE_HOST"));
									if (isCluster) {
										// Clear dropdown.
										dropdown.options.length = 0;

										// Add null item.
										dropdown.options[0] = new Option("-", -1);
										for (var v in jsonResponse) {
											// Add cluster members.
											if (v != "isCluster") {
												dropdown.options[dropdown.options.length] = new Option(jsonResponse [v], v);
											}
										}
										$("cluster_options").show();
									}
									else {
										// Hide form section.
										$("cluster_options").hide();
									}
								}
							});
				} else {
					$("C__CMDB__CATG__VIRTUAL_MACHINE_HOST").options.length = 0;
					$("cluster_options").hide();
					$("C__CATG__VIRTUAL_MACHINE__ADMINISTRATION_SERVICE").value = '';
				}
			});
		}


	}());
</script>