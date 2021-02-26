<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__ORGANISATION_TITLE" name="C__CONTACT__ORGANISATION_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__ORGANISATION_TITLE" id="C__CONTACT__ORGANISATION_TITLE"}]</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__ORGANISATION_PHONE" name="C__CONTACT__ORGANISATION_PHONE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__ORGANISATION_PHONE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__ORGANISATION_FAX" name="C__CONTACT__ORGANISATION_FAX"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__ORGANISATION_FAX"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__ORGANISATION_WEBSITE" name="C__CONTACT__ORGANISATION_WEBSITE"}]</td>
		<td class="value">[{isys type="f_link" name="C__CONTACT__ORGANISATION_WEBSITE" p_strTarget="_blank"}]</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr/>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__ORGANISATION_ASSIGNMENT" name="C__CONTACT__ORGANISATION_ASSIGNMENT"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CONTACT__ORGANISATION_ASSIGNMENT"}]</td>
	</tr>
</table>
<div id="test_container"></div>
<script type="text/javascript">
	(function () {
		'use strict';

		var $object_title = $('C__CATG__GLOBAL_TITLE'),
			$organization_title = $('C__CONTACT__ORGANISATION_TITLE'),
			global_category = true;

		// Add hidden title field if needed.
		if (!$object_title) {
			global_category = false;
			$object_title = new Element('input', {type:'hidden', name:'C__CATG__GLOBAL_TITLE', id:'C__CATG__GLOBAL_TITLE', value:''});
			$('test_container').update($object_title);
		}

		if ($organization_title) {
			if (!global_category) {
				$organization_title.focus();
			}

			$organization_title.on('change', update_organization_title);
			$organization_title.on('keyup', update_organization_title);
		}

		function update_organization_title () {
			$object_title.setValue($organization_title.getValue());
		}
	})();
</script>