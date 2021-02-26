<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_TYPE' ident="LC__CMDB__CATS__LICENCE_TYPE"}]</td>
        <td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__CATS__LICENCE_TYPE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_AMOUNT' ident="LC__CMDB__CATS__LICENCE_AMOUNT"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATS__LICENCE_AMOUNT"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_KEY' ident="LC__CMDB__CATS__LICENCE_KEY"}]</td>
        <td class="value">[{isys type="f_textarea" name="C__CATS__LICENCE_KEY"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_SERIAL' ident="LC__CMDB__CATS__LICENCE_SERIAL"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATS__LICENCE_SERIAL"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_START__VIEW' ident="LC__CMDB__CATS__LICENCE_START"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CATS__LICENCE_START" p_strPopupType="calendar"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_EXPIRE__VIEW' ident="LC__CMDB__CATS__LICENCE_EXPIRE"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CATS__LICENCE_EXPIRE" p_strPopupType="calendar"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_COST' ident="LC__CMDB__CATS__LICENCE_UNIT_PRICE"}]</td>
        <td class="value">[{isys type="f_money_number" name="C__CATS__LICENCE_COST"}]</td>
    </tr>
</table>

<table class="mainTable m5" cellspacing="0">
    <tr>
        <th>[{isys type="lang" ident="LC_UNIVERSAL__OBJECT_TYPE"}]</th>
        <th>[{isys type="lang" ident="LC__UNIVERSAL__TITLE"}]</th>
        <th>[{isys type="lang" ident="LC__UNIVERSAL__IN_USE"}]</th>
    </tr>
    [{foreach from=$objects key="id" item="object"}]
    <tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
        <td>[{$object.type}]</td>
        <td><a href="?[{$smarty.const.C__CMDB__GET__OBJECT}]=[{$id}]"><img class="vam" src="[{$dir_images}]icons/silk/link.png"/> [{$object.title}]</a></td>
        <td>[{$object.used_licences}]x</td>
    </tr>
    [{foreachelse}]
    <tr>
        <td colspan="3">[{isys type="lang" ident="LC__CMDB__CATS__LICENCE_NO_ASSIGNED_OBJECTS"}]</td>
    </tr>
    [{/foreach}]
</table>

<script>
	(function () {
		"use strict";

		var licence_type = $('C__CATS__LICENCE_TYPE'),
			licence_amount = $('C__CATS__LICENCE_AMOUNT');

		var check_amount = function () {
			if (licence_type.getValue() == '[{$smarty.const.C__CATS__LICENCE_TYPE__VOLUME_LICENCE}]') {
				licence_amount.readOnly = false;
			} else {
				licence_amount.setValue(1);
				licence_amount.readOnly = true;
			}

			// For triggering the validator.
			licence_amount.simulate('change');
		};

		if (licence_type) {
			licence_type.on('change', check_amount);
			check_amount();
		}
	}());
</script>