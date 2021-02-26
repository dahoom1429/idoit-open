<script type="text/javascript">[{include file="modules/templates/templates.js"}]</script>

<h2 class="p5 gradient text-shadow border-bottom mb10">[{isys type="lang" ident="LC__TEMPLATES__NEW_OBJECT_FROM_TEMPLATE"}]</h2>

<div class="p10">

	<h3 class="mb5">1. [{isys type="lang" ident="LC__TEMPLATES__SELECT_TITLE"}]</h3>

	<table class="contentTable">
		<tr>
			<td class="key vat"><label for="object_title">[{isys type="lang" ident="LC__TASK__TITLE"}]</label></td>
			<td class="value">
                <div class="ml20 input-group input-size-normal">
                    [{isys type="f_text" name="object_title" id="object_title"}]

                    <span class="input-group-addon input-group-addon-clickable">
                        <img src="[{$dir_images}]icons/silk/help.png" onclick="Effect.toggle('placeholderToggleButton', 'slide', {duration:0.2});" />
                    </span>
                </div>

                <br class="cb" />

                <div class="box ml20 mt5 mb5 overflow-auto input-size-normal" style="display:none; height:200px; box-sizing: border-box;" id="placeholderToggleButton">
                    <table class="border-none m0 w100 listing hover" style="border:none;" cellspacing="0">
                    [{foreach $placeholderData as $key => $value}]
                        <tr class="mouse-pointer" onclick="$('object_title').setValue($F('object_title') + '[{$key|escape:"javascript"}]').focus();">
                            <td class="key" style="width:100px;"><code>[{$key}]</code></td>
                            <td class="value">[{$value}]</td>
                        </tr>
                    [{/foreach}]
                    </table>
                </div>
            </td>
		</tr>
		<tr>
			<td class="key"><label for="object_type">[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]</label></td>
			<td class="value">[{isys type="f_dialog" name="object_type"}]</td>
		</tr>
		[{isys type="f_title_suffix_counter" p_bEditMode="1" name="C__TEMPLATE__SUFFIX" title_identifier="object_title" label_counter="LC__CMDB__CATG__QUANTITY"}]
		<tr>
			<td class="key"><label for="purpose">[{isys type="lang" ident="LC__CMDB__CATG__GLOBAL_PURPOSE"}]</label></td>
			<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="purpose"}]</td>
		</tr>
		<tr>
			<td class="key"><label for="category">[{isys type="lang" ident="LC__CMDB__CATG__GLOBAL_CATEGORY"}]</label></td>
			<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="category"}]</td>
		</tr>
	</table>

	<h3 class="mt20 mb5">2. [{isys type="lang" ident="LC__TEMPLATES__SELECT_TEMPLATES"}]</h3>

	[{isys type="f_dialog" id="template_id" name="templates_id"}]
	<br class="cb" />
	<button type="button" class="mt5 btn" onclick="select_template($('template_id'));">
		[{isys type="lang" ident="LC__TEMPLATES__USE"}]
	</button>

	<br class="cb" />

	<div class="container mt5" id="selected_templates">
		<h3 class="mb5">[{isys type="lang" ident="LC__UNIVERSAL__SELECTED"}] Templates (<span id="sel_count">0</span>)</h3>

		<div class="sortable">
			<ul id="template_list" class="list-style-none m0 p0"></ul>
		</div>
	</div>

	<div class="cb mb5"></div>

	<div id="step2">
		<h3 class="mt20 mb5">3. [{isys type="lang" ident="LC__UNIVERSAL__CREATE_OBJECT"}]</h3>

		<button name="create_template" type="submit" id="create_template" class="btn disabled" style="margin-right:5px;" value="1" disabled>
			<span>[{isys type="lang" ident="LC__TEMPLATES__OBJECT_FROM_SELECTED_TEMPLATES"}]</span>
		</button>

        [{isys type="f_button" name="edit-objects-multiedit" p_strClass="btn-large" p_strValue="LC__MODULE__MULTIEDIT__OPEN_OBJECTS_IN_MULTIEDIT" p_strClass="disabled hide"}]
        <input type="hidden" id="edit-objects-multiedit-object-ids" value="">

		<img style="display:none;" id="tpl_loader" class="fl mr5" src="images/ajax-loading.gif" />

	</div>

	<iframe id="iframe" name="iframe" src="" class="mt10 border" style="width:50%;height:250px;display:none;"></iframe>
</div>

<style type="text/css">
	#scroller {
		overflow: visible;
	}
</style>

<script type="text/javascript">
	(function() {
		"use strict";

		var $obj_type = $('object_type'),
			$create_tpl = $('create_template');

		$('object_title').focus();

		$('isys_form').writeAttribute('target', 'iframe');

		if($create_tpl) {
			$create_tpl.on('click', function () {
                $('edit-objects-multiedit').removeClassName('hide');
				$('tpl_loader').show();
				$('iframe').appear();
			});
		}

        $('edit-objects-multiedit').on('click', function (){
            if (this.hasClassName('disabled')) {
                return null;
            }

            if ($('edit-objects-multiedit-object-ids').getValue() != '') {
                document.location.href = window.www_dir + 'multiedit?preselect=' + $('edit-objects-multiedit-object-ids').getValue();
            }

        });

		if($obj_type) {
			$obj_type.on('change', function(){
				if(this.value != -1) {
					$create_tpl.removeClassName('disabled');
					$create_tpl.removeAttribute('disabled');
				} else {
					$create_tpl.addClassName('disabled');
					$create_tpl.writeAttribute('disabled');
				}
			});
			$obj_type.simulate('change');
		}
	})();
</script>