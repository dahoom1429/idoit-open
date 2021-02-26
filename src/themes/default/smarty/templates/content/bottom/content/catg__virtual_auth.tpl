<div id="auth">
	<h3 class="border-bottom gradient p5">[{isys type="lang" ident="LC__CMDB__CATG__AUTH_ACCESS_PERMISSIONS_OF_THE_CURRENT_OBJECT"}]</h3>
	<table style="border-top: none;" class="contentTable">
		<tr>
			<td colspan="2" style="padding:0 10px;">
				<p>[{isys type="lang" ident="LC__CMDB__CATG__AUTH_INFO_TEXT"}]</p>
			</td>
		</tr>
		[{if $editmode}]
		<tr class="hide-on-viewmode">
			<td colspan="2">
				<hr class="mt5 mb5" />
			</td>
		</tr>
		<tr class="hide-on-viewmode">
			<td class="key">
				[{isys type="f_label" name="C__CATG__VIRTUAL_AUTH__PERSON_SELECTION" ident="LC__MODULE__AUTH__PERSON_AND_PERSONGROUPS"}]
			</td>
			<td class="value">
				[{isys type="f_popup" name="C__CATG__VIRTUAL_AUTH__PERSON_SELECTION" p_strPopupType="browser_object_ng" secondSelection=false catFilter="C__CATS__PERSON;C__CATS__PERSON_GROUP"}]
			</td>
		</tr>
		<tr class="hide-on-viewmode">
			<td class="key">
				[{isys type="f_label" name="C__CATG__VIRTUAL_AUTH__CONDITION" ident="LC__AUTH_GUI__CONDITION"}]
			</td>
			<td class="value right-buttons">
				[{foreach $rights as $right}]
					<button class="btn btn-small mr5" data-right="[{$right.title}]" data-right-value="[{$right.value}]" type="button" title="[{$right.title}]">
						<img src="[{$dir_images}][{$right.icon}]" />
					</button>
				[{/foreach}]

				<input type="hidden" id="C__CATG__VIRTUAL_AUTH__RIGHTS" name="C__CATG__VIRTUAL_AUTH__RIGHTS" value="[{$view}]" />
				[{isys type="lang" ident="LC__AUTH_GUI__REFERS_TO"}]
				[{isys type="f_dialog" name="C__CATG__VIRTUAL_AUTH__CONDITION" p_bInfoIconSpacer=0 p_bDbFieldNN=1 p_strClass="ml5 normal" p_bSort=false disableInputGroup=true}]
			</td>
		</tr>
		<tr class="hide-on-viewmode">
			<td></td>
			<td>
				<button id="auth_path_save" class="btn btn-small" style="margin-left:20px;" type="button">
					<img src="[{$dir_images}]icons/silk/add.png" />
					<span class="vam">[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__SAVE"}]</span>
				</button>
			</td>
		</tr>
		[{/if}]
	</table>

	[{foreach from=$paths key=person item=person_data}]
	<h3 class="gradient p5 border-bottom border-top mt15" style="background-color:#ccc;">[{isys type="lang" ident=$person_data.person.isys_obj_type__title}] &raquo; [{$person_data.person.isys_obj__title}]</h3>
	<div id="path_table_[{$person}]" class="path_tables""></div>
	[{/foreach}]
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $container = $('auth');

		[{include file="src/tools/js/auth/configuration.js"}]

		// Setting some translations...
		idoit.Translate.set('LC__AUTH_GUI__REFERS_TO', '[{isys type="lang" ident="LC__AUTH_GUI__REFERS_TO"}]');
		idoit.Translate.set('LC__UNIVERSAL__REMOVE', '[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]');
		idoit.Translate.set('LC__UNIVERSAL__COPY', '[{isys type="lang" ident="LC__UNIVERSAL__COPY"}]');
		idoit.Translate.set('LC__UNIVERSAL__LOADING', '[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');
		idoit.Translate.set('LC__UNIVERSAL__ALL', '[{isys type="lang" ident="LC__UNIVERSAL__ALL"}]');
		// Translations for the table-header.
		idoit.Translate.set('LC__MODULE__AUTH__PERSON_AND_PERSONGROUPS', '[{isys type="lang" ident="LC__MODULE__AUTH__PERSON_AND_PERSONGROUPS"}]');
		idoit.Translate.set('LC__AUTH_GUI__CONDITION', '[{isys type="lang" ident="LC__AUTH_GUI__CONDITION"}]');
		idoit.Translate.set('LC__AUTH_GUI__PARAMETER', '[{isys type="lang" ident="LC__AUTH_GUI__PARAMETER"}]');
		idoit.Translate.set('LC__AUTH_GUI__ACTION', '[{isys type="lang" ident="LC__AUTH_GUI__ACTION"}]');

		[{foreach from=$paths key=person item=person_data}]
		new AuthConfiguration('path_table_[{$person}]', {
			ajax_url:'[{$ajax_url}]',
			rights:[{$auth_rights}],
			methods:[{$auth_methods}],
			paths:[{$person_data.paths|default:""}],
			wildchar:'[{$auth_wildchar}]',
			empty_id:'[{$auth_empty_id}]',
			edit_mode:0
		});
		[{/foreach}]

		[{if $editmode}]
		$container.down('.right-buttons button:first-child').addClassName('ml20');

		if ($('C__CATG__VIRTUAL_AUTH__PERSON_SELECTION__HIDDEN') && $('C__CATG__VIRTUAL_AUTH__CONDITION') && $('C__CATG__VIRTUAL_AUTH__RIGHTS')) {
			$container.select('.right-buttons button').invoke('on', 'click', function(ev) {
				var $button = ev.findElement('button').toggleClassName('btn-green'),
				    checked = $button.hasClassName('btn-green'),
				    value = $button.readAttribute('data-right-value'),
				    mapping = {};

				mapping['[{isys_auth::CREATE}]'] = [];
				mapping['[{isys_auth::VIEW}]'] = [];
				mapping['[{isys_auth::EDIT}]'] = ['[{isys_auth::CREATE}]', '[{isys_auth::VIEW}]'];
				mapping['[{isys_auth::ARCHIVE}]'] = ['[{isys_auth::VIEW}]'];
				mapping['[{isys_auth::DELETE}]'] = ['[{isys_auth::VIEW}]'];
				mapping['[{isys_auth::EXECUTE}]'] = ['[{isys_auth::VIEW}]'];
				mapping['[{isys_auth::SUPERVISOR}]'] = ['[{isys_auth::CREATE}]', '[{isys_auth::VIEW}]', '[{isys_auth::EDIT}]', '[{isys_auth::ARCHIVE}]', '[{isys_auth::DELETE}]', '[{isys_auth::EXECUTE}]', '[{isys_auth::SUPERVISOR}]'];

				$container.select('.right-buttons button').each(function ($button) {
					// Don't use "getValue()" because that will only work, if the checkbox is checked.
					if (mapping[value].in_array($button.readAttribute('data-right-value')))
					{
						if (checked)
						{
							$button.addClassName('btn-green').disable();
						}
						else
						{
							$button.removeClassName('btn-green').enable();

							$container.select('.right-buttons button.btn-green').invoke('simulate', 'click');
						}
					}
				});

				$button.enable();

				$('C__CATG__VIRTUAL_AUTH__RIGHTS').setValue($container.select('button.btn-green').invoke('readAttribute', 'data-right-value').join(';'));
			});

			$('auth_path_save').on('click', function () {
				// Ajax Request zum speichern der neuen Berechtigung.
				new Ajax.Request('[{$save_ajax_url}]',
					{
						parameters:{
							person_id:$F('C__CATG__VIRTUAL_AUTH__PERSON_SELECTION__HIDDEN'),
							module_id:[{$smarty.const.C__MODULE__CMDB}],
							method:$F('C__CATG__VIRTUAL_AUTH__CONDITION'),
							parameter:[{$obj_id}],
							rights:$F('C__CATG__VIRTUAL_AUTH__RIGHTS')
						},
						method:'post',
						onSuccess:function (response) {
							var json = response.responseJSON;

							if (json.success) {
								// Reload the category... Because that is MUCH easier than re-initializing all the stuff here.
								location.href = '[{$reload_url}]';
							} else {
								$('ajaxReturnNote').update(new Element('div', {className:'p5 box-red'}).update(json.message)).show();
							}
						}
					});
			});
		} else {
			// Just in case the PHP detection of the "edit mode" does not work...
			$container.select('.hide-on-viewmode').invoke('hide');
		}
		[{/if}]
	})();
</script>

<style type="text/css">
	#auth .path_tables thead {
		height: 30px;
	}

	#auth .path_tables th {
		text-align: center;
	}

	#auth .path_tables th,
	#auth .path_tables td {
		padding: 2px;
	}

	#auth .path_tables tbody td {
		border-top: 1px solid #888888;
		padding: 3px;
	}
</style>