<h2 class="p5 gradient text-shadow border-bottom">[{isys type="lang" ident="LC__TEMPLATES__NEW_TEMPLATE"}]</h2>

<div class="p10">
	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]</td>
			<td class="value">[{isys type="f_dialog" name="object_type"}]</td>
		</tr>
		<tr>
			<td class="key"></td>
			<td class="value">
				<button type="button" class="ml20 btn" id="create_template" disabled="disabled">
					<span>[{isys type="lang" ident="LC__TEMPLATE__CREATE_NEW_TEMPLATE"}]</span>
				</button>
			</td>
		</tr>
	</table>
</div>

<style type="text/css">
	#scroller {
		overflow: visible;
	}
</style>

<script type="text/javascript">
	var $objectTypeSelection = $('object_type'),
	    $createTemplateButton = $('create_template');

    $objectTypeSelection.on('change', function () {
        if ($objectTypeSelection.getValue() > 0) {
            $createTemplateButton.enable();
        } else {
            $createTemplateButton.disable();
        }
    });

    $createTemplateButton.on('click', function () {
        var selectedObjectType = $objectTypeSelection.getValue(),
            $template = $('body').down('[name="template"]');

        if ($createTemplateButton.disabled || selectedObjectType <= 0) {
            return false;
        }

        if ($template) {
            $template.setValue('1');
        }

        $('navMode').setValue('[{$smarty.const.C__NAVMODE__NEW}]');

        $('isys_form')
	        .writeAttribute('action', '?[{$smarty.const.C__CMDB__GET__VIEWMODE}]=[{$smarty.const.C__CMDB__VIEW__LIST_OBJECT}]&[{$smarty.const.C__CMDB__GET__OBJECTTYPE}]=' + selectedObjectType)
			.submit();
    });
</script>
