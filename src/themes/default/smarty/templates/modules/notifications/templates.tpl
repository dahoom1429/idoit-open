[{* Smarty template for module 'Notifications'
    @ author: Benjamin Heisig <bheisig@i-doit.org>
    @ copyright: synetics GmbH
    @ license: <http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3>
*}]

[{if $g_list}]
    [{$g_list}]
[{else}]

<div>
	<h2 class="gradient p5 border-bottom">[{isys type='lang' ident='LC__NOTIFICATIONS__MANAGE_TEMPLATES'}]</h2>

	<div class="p10">
		<h3 class="mb5">[{$type_title}]</h3>

		<p>[{$type_description}]</p>

		<p>&nbsp;</p>

		<p><a href="[{$type_templates}]">[{isys type='lang' ident='LC__NOTIFICATIONS__MANAGE_NOTIFICATIONS'}]</a></p>

		[{isys type='f_text' name='C__NOTIFICATIONS__TEMPLATE_ID'}]
		[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_TYPE_ID'}]
	</div>

	<fieldset class="overview">
		<legend><span>[{isys type='lang' ident='LC__NOTIFICATIONS__COMMON_SETTINGS'}]</span></legend>

		<table class="contentTable">
			<tr>
		        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE' ident='LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE'}]</td>
		        <td class="value">[{isys type='f_dialog' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE'}]</td>
		    </tr>
			<tr>
		        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT' ident='LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT'}]</td>
		        <td class="value">[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT'}]</td>
		    </tr>
			<tr>
		        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_TEXT' ident='LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_TEXT'}]</td>
		        <td class="value">[{isys type='f_textarea' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_TEXT'}]</td>
		    </tr>
			<tr>
		        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT' ident='LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT'}]</td>
		        <td class="value pl20">[{isys type='f_property_selector' custom_fields=1 dynamic_properties=1 name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT'}]</td>
		    </tr>
		</table>

	</fieldset>

	<fieldset class="overview">
		<legend><span>[{isys type='lang' ident='LC__NOTIFICATIONS__PLACEHOLDERS'}]</span></legend>

		<table class="contentTable">
		    [{foreach from=$placeholders item='placeholder'}]
			<tr class="[{cycle values="CMDBListElementsEven,CMDBListElementsOdd"}]">
		        <td class="key">[{$placeholder.title}]</td>
		        <td class="value" style="padding-left:20px;">[{$placeholder.value}]</td>
		        <td class="value">[{$placeholder.description}]</td>
		    </tr>
		    [{/foreach}]
		</table>

	</fieldset>

</div>

[{/if}]
