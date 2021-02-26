<tr style="display:none" class="suf">
	<td style="width:100px;vertical-align: top;" class="strong">[{isys type="lang" ident="LC__CMDB__LOGBOOK__TITLE"}] Suffix:</td>
	<td class="value">
		<label style="margin-left:20px;"><input type="radio" class="suf_options" name="object_appending" value="" checked="checked" onclick="show_preview();" /> [{isys type="lang" ident="LC__UNIVERSAL__NO_SUFFIX"}]</label><br />
		<label style="margin-left:20px;"><input type="radio" class="suf_options" name="object_appending" value="##COUNT##" onclick="show_preview();"/> "[{isys type="lang" ident="LC__TEMPLATES__OBJECT_COUNTER"}]"</label><br />
		<label style="margin-left:20px;"><input type="radio" class="suf_options" name="object_appending" value="-1" onclick="show_preview();" onchange="if (this.checked) $('object_appending_own').show()" /> [{isys type="lang" ident="LC__TEMPLATES__OWN"}]: </label>
		[{isys type="f_text" p_bEditMode="1" name="object_appending_own" id="object_appending_own" p_strStyle="display:none;" p_strClass="input-small" p_strValue="##COUNT##" p_onChange="show_preview();"}]
	</td>
</tr>
<tr style="display:none" class="suf">
	<td valign="top" class="strong"><label for="count_starting_at">[{isys type="lang" ident="LC__WORKFLOWS__STARTING_NOW"}]:</label></td>
	<td class="value">[{isys type="f_text" p_bInfoIconSpacer="1" p_bEditMode="1" p_strClass="input-mini" p_onChange="show_preview();" name="count_starting_at" id="count_starting_at" p_strValue="0"}]</td>
</tr>
<tr style="display:none" class="suf">
	<td valign="top" class="strong"><label for="zero_point_calc">[{isys type="lang" ident="LC__UNIVERSAL__LEADING_ZEROS"}]:</label></td>
	<td class="value">
		[{isys type="f_text" p_bInfoIconSpacer="1" p_bEditMode="1" p_strClass="input-mini" name="zero_points" id="zero_points" p_strValue="2" p_onChange="show_preview();"}]
		<label>
			[{isys type="checkbox" name="zero_point_calc" id="zero_point_calc" p_bInfoIconSpacer=0 p_bChecked="1" tab="20" p_strValue="1" p_strOnClick="show_preview();"}]
			[{isys type="lang" ident="LC__NOTIFICATIONS__STATUS__ACTIVATED"}]?
		</label>
	</td>
</tr>
<tr style="display:none;" class="suf">
	<td class="top">
		Vorschau
	</td>
	<td class="value">
		[{isys type="f_textarea" p_nRows="2" p_nSize="2" p_bInfoIconSpacer="1" p_bReadonly="1" p_bDisabled="1" p_bEditMode="1" name="preview" id="preview"}]
		<input type="hidden" id="title_identifier" value="">
	</td>
</tr>

<script type="text/javascript">
	window.show_preview = function () {
		var type = "";
		var additional = "";
		var ele = $('title_identifier').value;
		$$('input[name=object_appending]:checked').find(function (e) {
			type = e.value;
		});
		var start_with = parseInt($('count_starting_at').value);
		var start_with_as_string = $('count_starting_at').value;
		var zero_calc = $('zero_point_calc').checked;
		var zero_points = parseInt($('zero_points').value);
		var appending_zeros = "";
		var appending = $('object_appending_own').value;
		$('preview').value = "";
		for (i = 0; i < 2; i++) {
			appending_zeros = "";
			additional = "";
			if (type != "") {
				if (zero_calc) {
					for (n = 0; n < zero_points; n++) {
						appending_zeros += 0;
					}

					if (start_with > 9) {
						appending_zeros = appending_zeros.substr(0,
								(appending_zeros.length - (start_with_as_string.length - 1)));
					}

					additional = appending_zeros;
				}
			}
			switch (type) {
				case '##COUNT##':
					additional = additional + start_with;
					start_with = start_with + 1;
					start_with_as_string = String(start_with);
					break;
				case '-1':
					additional = appending.replace('##COUNT##', additional + start_with);
					start_with = start_with + 1;
					start_with_as_string = String(start_with);
					break;
				default:
					additional = "";
					break;
			}

			$('preview').value += $(ele).value + additional + "\n";
		}
	}
</script>