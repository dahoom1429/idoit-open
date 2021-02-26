
[{if isset($g_list)}]
	[{$g_list}]
[{else}]
	<h3 class="p5 gradient">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__SERVER"}]</h3>

	<fieldset class="overview">
		<legend><span>[{isys type="lang" ident="LC__MODULE__LDAP__LDAP_CONNECTION_FOR_LOOKUPS"}]</span></legend>
		<br/>
		<input type="hidden" name="id" value="[{$entryID}]">

		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="lang" ident="LC_WORKFLOW__ACTIVE"}]</td>
				<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__MODULE__LDAP__ACTIVE"}]</td>
			</tr>
			<tr>
				<td class="key">Directory<span class="text-red">*</span></td>
				<td class="value">[{isys type="f_dialog" name="C__MODULE__LDAP__DIRECTORY" id="C__MODULE__LDAP__DIRECTORY" p_strTable="isys_ldap_directory"}]</td>
			</tr>
			<tr>
				<td class="key">LDAP-Version</td>
				<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__MODULE__LDAP__VERSION"}]</td>
			</tr>
            <tr>
                <td class="key">Enable LDAP Paging</td>
                <td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__MODULE__LDAP__ENABLE_PAGING"}]</td>
            </tr>
            <tr>
                <td class="key">LDAP Page Limit</td>
                <td class="value">[{isys type="f_text" name="C__MODULE__LDAP__PAGE_LIMIT"}]</td>
            </tr>
			<tr>
				<td class="key">[{isys type="lang" ident="IP / Hostname"}]<span class="text-red">*</span></td>
				<td class="value">[{isys type="f_text" name="C__MODULE__LDAP__HOST"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="Port"}]<span class="text-red">*</span></td>
				<td class="value">[{isys type="f_text" name="C__MODULE__LDAP__PORT"}] <img src="[{$dir_images}]icons/silk/information.png" class="vam ml10" /> <em class="text-grey">default: 389</em></td>
			</tr>
			<tr>
				<td class="key">TLS</td>
				<td class="value">[{isys type="f_dialog" name="C__MODULE__LDAP__TLS"}]</td>
			</tr>
			<tr>
				<td class="key">Admin [{isys type="lang" ident="LC__LOGIN__USERNAME"}] (DN)<span class="text-red">*</span></td>
				<td class="value">[{isys type="f_text" name="C__MODULE__LDAP__DN"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__LOGIN__PASSWORD"}]<span class="text-red">*</span></td>
				<td class="value">[{isys type="f_password" name="C__MODULE__LDAP__PASS"}]</td>
			</tr>
            <tr>
                <td class="key">[{isys type="lang" ident="LC__MODULE__LDAP__USE_ADMIN_ONLY"}]</td>
                <td class="value pl20">
                    <label class="vam">
                        <input type="checkbox" name="C__MODULE__LDAP__USE_ADMIN_ONLY" [{if !$isEditMode}]disabled[{/if}] [{if $g_use_admin_only}]checked="checked" [{/if}] value="1"/>
                        [{isys type="lang" ident="LC__UNIVERSAL__ACTIVE"}]
                    </label>
                </td>
            </tr>
			<tr>
				<td class="key">Timelimit</td>
				<td class="value">[{isys type="f_text" name="C__MODULE__LDAP__TIMELIMIT"}] <img src="[{$dir_images}]icons/silk/information.png" class="vam ml10" /> <em class="text-grey">default: 30</em></td>
			</tr>
		</table>
	</fieldset>


	<fieldset class="overview">
		<legend><span>[{isys type="lang" ident="LC__MODULE__LDAP__LDAP_PARAMETER_FOR_IDOIT_LOGIN"}]</span></legend>
		<br/>
		<table class="contentTable">
            <tr>
                <td class="key">[{isys type="lang" ident="LC__LDAP__UNIQUE_IDENTIFIER"}]</td>
                <td class="value">[{isys type="f_dialog" name="C__MODULE__LDAP__UNIQUE_ATTRIBUTE"}]</td>
            </tr>
			<tr>
				<td class="key">Filter</td>
				<td class="value">[{isys type="f_text" p_strTitle="Default: (objectClass=user)" name="C__MODULE__LDAP__FILTER"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__LDAP__SEARCH_FOR_USERS_IN"}]<span class="text-red">*</span></td>
				<td class="value">
					[{isys type="f_text" name="C__MODULE__LDAP__SEARCH"}]
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__LDAP__RECURSIVE_SEARCH"}]</td>
				<td class="value pl20">
					<label class="vam">
						<input type="checkbox" name="C__MODULE__LDAP__RECURSIVE" [{if !$isEditMode}]disabled[{/if}] [{if $g_recursive}]checked="checked" [{/if}] value="1"/>
					[{isys type="lang" ident="LC__UNIVERSAL__ACTIVE"}]
					</label>
				</td>
			</tr>
			[{if $isEditMode}]
			<tr>
				<td class="vat">
					<button type="button" class="btn ml10 mt15" onclick="window.add_filter('');">
						<img class="mr5" src="[{$dir_images}]icons/silk/add.png"/>
						<span>[{isys type="lang" ident="LC__LDAP__ADD_FILTER"}]</span>
					</button>
					<input type="hidden" name="counter" id="counter" value="0">
				</td>
			</tr>
			[{/if}]
			<tbody id="extra_filter" class="p5 m10"></tbody>
		</table>
	</fieldset>

	<fieldset id="openldap_only" class="overview" style="display:none;">
		<legend><span>[{isys type="lang" ident="LC__MODULE__LDAP__OPENLDAP_GROUPSEARCH"}]</span></legend>
		<br />

		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="lang" ident="LC__LDAP__SEARCH_FOR_GROUPS_IN"}]<span class="text-red">*</span></td>
				<td class="value">
					[{isys type="f_text" name="C__MODULE__LDAP__SEARCH_GROUP"}]
				</td>
			</tr>
		</table>
	</fieldset>

	[{if $isEditMode}]
		<h3 class="m0 p5 mt10 border-bottom border-top gradient">Connection-Test</h3>
		<p class="m10">
			<button type="button" id="ldap-connection-test" class="btn mr10"><img src="[{$dir_images}]icons/silk/connect.png" class="mr5" /><span>Start Test</span></button>

			<label>
				Debug-Level
				<select name="debugLevel" id="debugLevel" class="input input-mini">
					<option value="0" selected="selected">normal</option>
					<option value="6">low</option>
					<option value="7">high</option>
				</select> <img class="vam" src="images/icons/silk/information.png" title="Additional debug messages are written to apache error log." />
			</label>
		</p>

		<div id="ajax_return" class="m5 p5" style="display:none;"></div>
	[{/if}]

	<script type="text/javascript">
		"use strict";

		var show_openldap_group_search = false,
			options_types = [
				{title: 'AND', value:'&'},
				{title: 'OR', value:'|'}
			],
			options_operators = [
				{title: '=', value:'='},
				{title: '!=', value:'!='},
				{title: '>=', value:'>='},
				{title: '<=', value:'<='}
			],
			$filter_table = $('extra_filter'),
			$connection_test = $('ldap-connection-test'),
			$connection_result = $('ajax_return');


		if ($connection_test) {
			$connection_test.on('click', function () {
				$connection_test.disable()
					.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
					.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

				$connection_result.update('...');

				new Ajax.Submit('index.php?moduleID=[{$smarty.get.moduleSubID|strip_tags|escape}]&id=[{$smarty.get.id|strip_tags|escape}]&connection_test=1&debug=' + $('debugLevel').value,
					'ajax_return',
					'isys_form',
					{
						onComplete: function () {
							$connection_test.enable()
								.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/connect.png')
								.next('span').update('Start Test');

							$connection_result.show().highlight();
						}
					});
			});
		}

		if ($('C__MODULE__LDAP__DIRECTORY')) {
			$('C__MODULE__LDAP__DIRECTORY').on('change', function() {
				if (this.options[this.selectedIndex].innerHTML.trim() == 'Open LDAP') {
					$('openldap_only').show();
				} else {
					$('openldap_only').hide();
				}
			});
		}

		if ($('C__MODULE__LDAP__ENABLE_PAGING')) {
            $('C__MODULE__LDAP__ENABLE_PAGING').on('change', function (){
                if (this.getValue() > 0) {
                    $('C__MODULE__LDAP__PAGE_LIMIT').disabled = false;
                } else{
                    $('C__MODULE__LDAP__PAGE_LIMIT').disabled = true;
                }
            });
            $('C__MODULE__LDAP__ENABLE_PAGING').simulate('change');
        }

		window.add_filter = function(p_title, p_value, p_type, p_link_type, p_operator) {
			var counter = $F('counter'),
				custom_title = new Element('input', {type:'text', name:'field_title[' + counter + ']', placeholder:'[{isys type="lang" ident="LC__LDAP__FILTER__ATTRIBUTE"}]', className:'ml5 mr15 input input-mini', value:p_title}),
				custom_value = new Element('input', {type:'text', name:'field_value[' + counter + ']', placeholder:'[{isys type="lang" ident="LC__LDAP__FILTER__VALUE"}]', className:'ml5 mr15 input input-mini', value:(p_value !== undefined)? p_value: ''}),
				custom_select = new Element('select', {name:'field_link_type[' + counter + ']', className:'input input-small'}),
				custom_select2 = new Element('select', {name:'field_operator[' + counter + ']', className:'input input-mini'});

			$('counter').setValue(parseInt(counter) + 1);
			if (Object.isUndefined(p_title)) p_title = '';
			if (Object.isUndefined(p_value)) p_value = '';

			options_types.each(function(el) {
				var option = new Element('option', {value:el.value}).update(el.title);

				if (p_link_type == el.value) {
					option.selected = true;
				}

				custom_select.insert(option);
			});

			options_operators.each(function (el) {
				var option = new Element('option', {value:el.value}).update(el.title);

				if (p_operator == el.value) {
					option.selected = true;
				}

				custom_select2.insert(option);
			});

			var l_insert = new Element('tr', {id: 'extra_filter_' + counter}).update(
				new Element('td', {className: 'm10', colspan: 2}).update(
					new Element('table', {className: 'ml20'}).update(
						new Element('tr')
							.update(new Element('td').update(new Element('label').update(new Element('span', {className:'counter'}).update()).insert(custom_title)))
							.insert(new Element('td').update(custom_select2))
							.insert(new Element('td').update(new Element('label').insert(custom_value)))
							.insert(
								new Element('td').update(
									new Element('label')
										.update('[{isys type="lang" ident="LC__LDAP__FILTER__ATTACH_TO_LAST_FILTER"}]')
										.insert(new Element('input', {type:'radio', className:'ml5 vam mr15', name:'field_type[' + counter + ']', value:1, checked:(p_type == 1)}))
								)
							)
							.insert(
								new Element('td').update(
									new Element('label')
										.update('[{isys type="lang" ident="LC__LDAP__FILTER__ATTACH_NEW_FILTER"}]')
										.insert(new Element('input', {type:'radio', className:'ml5 vam mr15', name:'field_type[' + counter + ']', value:2, checked:(p_type == 2)}))
								)
							)
							.insert(
								new Element('td')
									.update(new Element('label').update('[{isys type="lang" ident="LC__LDAP__FILTER__ADD_NEW_FILTER"}]')
									.insert(new Element('input', {type:'radio', className:'ml5 vam mr15', name:'field_type[' + counter + ']', value:3, checked:(p_type == 3)}))
								)
							)
							.insert(new Element('td').update(custom_select))
					)
				)
			);

			if (! (p_title == "objectClass" && p_value == "user")) {
				l_insert.down('table tr').insert(
					new Element('td').update(
						new Element('button', {type: 'button', className: 'ml20 btn', onClick: 'del_filter(' + counter + ')'}).update(
								new Element('img', {className: 'mr5', src: '[{$dir_images}]icons/silk/delete.png'})
							).insert(
								new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]')
							)
					)
				);
			}

			$filter_table.insert(l_insert);

			update_filter_count();
		};

		window.del_filter = function(p_id) {
			$('extra_filter_' + p_id).remove();

			update_filter_count();
		};

		if ($('C__MODULE__LDAP__DIRECTORY')) {
            $('C__MODULE__LDAP__DIRECTORY').simulate('change');
		}

        /**
         * Set port based on encoding
         *
         * @author Selcuk Kekec <skekec@i-doit.com>
         */
        window.setPortByEncoding = function() {
            // Preparation
            var $portField = $('C__MODULE__LDAP__PORT');
            var port = 0;

            // Check encoding type
            switch ($('C__MODULE__LDAP__TLS').getValue()) {
                case '[{idoit\Component\Helper\LdapUrlGenerator::LDAP_ENCODING_OFF}]':
                case '[{idoit\Component\Helper\LdapUrlGenerator::LDAP_ENCODING_STARTTLS}]':
                    port = '[{idoit\Component\Helper\LdapUrlGenerator::LDAP_DEFAULT_PORT}]';
                    break;
                case '[{idoit\Component\Helper\LdapUrlGenerator::LDAP_ENCODING_TLS}]':
                    port = '[{idoit\Component\Helper\LdapUrlGenerator::LDAP_TLS_PORT}]';
            }

            // Set port
            $portField.setValue(port);
        };


		function update_filter_count () {
			var counter = 0;
			$filter_table.select('span.counter').each(function($span) {
				$span.update(++counter + ') ');
			});
		}

		[{if $isEditMode && is_array($filter_arr) && count($filter_arr) > 0}]
		[{foreach $filter_arr.attributes as $filter_key => $filter_item}]
			window.add_filter('[{$filter_arr.attributes[$filter_key]}]', '[{$filter_arr.values[$filter_key]}]', '[{$filter_arr.field_type[$filter_key]}]', '[{$filter_arr.field_link_type[$filter_key]}]', '[{$filter_arr.field_operator[$filter_key]}]');
		[{/foreach}]
		[{/if}]
	</script>
[{/if}]