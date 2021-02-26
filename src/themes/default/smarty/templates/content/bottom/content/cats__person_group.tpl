<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__GROUP_TITLE" name="C__CONTACT__GROUP_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__GROUP_TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__GROUP_EMAIL_ADDRESS" name="C__CONTACT__GROUP_EMAIL_ADDRESS"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__GROUP_EMAIL_ADDRESS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__GROUP_PHONE" name="C__CONTACT__GROUP_PHONE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__GROUP_PHONE"}]</td>
	</tr>
	[{if $ldap}]
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__PERSON_GROUPS__LDAP_MAPPING" name="C__CONTACT__GROUP_LDAP"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__GROUP_LDAP"}]</td>
	</tr>
	[{/if}]
</table>

<div id="test_container"></div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $object_title = $('C__CATG__GLOBAL_TITLE'),
			$group_title = $('C__CONTACT__GROUP_TITLE'),
			global_category = true;

		// Add hidden title field if needed.
		if (!$object_title) {
			global_category = false;
			$object_title = new Element('input', {type:'hidden', name:'C__CATG__GLOBAL_TITLE', id:'C__CATG__GLOBAL_TITLE', value:''});
			$('test_container').update($object_title);
		}

		if ($group_title) {
			if (!global_category) {
				$group_title.focus();
			}

			$group_title.on('change', change_global_title);
			$group_title.on('keyup', change_global_title);
		}

		function change_global_title () {
			$object_title.setValue($group_title.getValue());
		}
	})();
</script>