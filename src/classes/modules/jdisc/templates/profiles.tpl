[{* Smarty template for JDisc profiles
    @ author: Benjamin Heisig <bheisig@i-doit.org>
    @ copyright: synetics GmbH
    @ license: <http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3>
*}]

[{if $g_list}]
	[{$g_list}]
[{else}]

<script type="text/javascript">

    /**
     * Adds new assignment after the current one or duplicates it.
     */
    window.add_new_assignment = function (type, assignment, append) {
        var clone = assignment.cloneNode(true),
                parent = $(type),
                e,
                location_field = 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_LOCATION',
                jdisc_ajax_url = '[{$jdisc_ajax_url}]';

        if (assignment.nextSibling) {
            e = parent.insertBefore(clone, assignment.nextSibling);
        } else {
            e = parent.appendChild(clone);
        }

        if (append) {
            // Reset fields:
            var nodes = e.childNodes;
            for (i = 1; i < (nodes.length - 2); i += 2) {
                var input = nodes[i].getElementsByTagName('input')[0];
                if (input) {
                    if (!input.hasClassName('portFilter')) {
                        input.value = '';
                        if ((!input.hasClassName('jdisc_location')) && (!input.hasClassName('fqdn_addition')))
                        {
                            input.hide();
                        }
                        else if (input.hasClassName('jdisc_location'))
                        {
                            if(nodes[i].down('div'))
                            {
                                // Object browser location
                                location_field = location_field + '[_' + (parseInt(parent.childElementCount) - 1) + ']';

                                nodes[i].down('div').down('div').setAttribute('id', 'div_browser_location');
                                $('div_browser_location').update('');
                                new Ajax.Updater('div_browser_location', jdisc_ajax_url + '&func=location_browser&editMode=1', {
                                    evalScripts: true,
                                    parameters: {
                                        'field':location_field
                                    },
                                    onComplete: function() {
                                        $('div_browser_location').removeAttribute('id');
                                    }
                                });
                            }
                        }
                    }
                    else
                    {
                        var input_parent = input.parentNode;
                        input.value = '';
                        input.readOnly=true;
                        input.name = input.name.replace(/[0-9]/, parent.childElementCount-1);
                        input.setStyle({'background':'none repeat scroll 0 0 #DDDDDD'});
                        input.next('select').name = input.next('select').name.replace(/[0-9]/, parent.childElementCount-1);

                        input.next('select').on('change', function(){
                            if(this.value == 0)
                            {
                                this.previous('input').readOnly=true;
                                this.previous('input').value='';
                                this.previous('input').setStyle({'background':'none repeat scroll 0 0 #DDDDDD'});
                            }
                            else
                            {
                                this.previous('input').readOnly=false;
                                this.previous('input').setStyle({'background':'none repeat scroll 0 0 #FBFBFB'});
                            }
                        });

                        if(input_parent.next('div') != '')
                        {
                            while(input_parent = input_parent.next('div'))
                            {
                                input_parent.down('input').value = '';
                                input_parent.down('input').name = input_parent.down('input').name.replace(/[0-9]/, parent.childElementCount-1);
                                input_parent.down('select').name = input_parent.down('select').name.replace(/[0-9]/, parent.childElementCount-1);
                            }
                        }
                    }
                }
                var select = nodes[i].getElementsByTagName('select')[0];
                if(select) {
                    select.options[0].selected = true;
                    select.show();
                }
            }
        } else {
            var originalNodes = assignment.childNodes;
            var clonedNodes = e.childNodes;
            for (i = 1; i < (originalNodes.length - 2); i += 2) {
                var select = originalNodes[i].getElementsByTagName('select')[0];

                if(select) {
                    var clonedSelect = clonedNodes[i].getElementsByTagName('select')[0];
                    for (j = 0; j < select.length; j++) {
                        if (select.options[j].selected == true) {
                            clonedSelect.options[j].selected = true;
                        }
                    }
                }
                var input_clone = clonedNodes[i].getElementsByTagName('input')[0];
                var input_original = originalNodes[i].getElementsByTagName('input')[0];
                if (input_clone) {
                    if (input_clone.hasClassName('portFilter')) {
                        var input_parent = input_clone.parentNode;
                        var input_parent_original = input_original.parentNode;

                        input_clone.name = input_clone.name.replace(/[0-9]/, parent.childElementCount-1);
                        input_clone.next('select').name = input_clone.next('select').name.replace(/[0-9]/, parent.childElementCount-1);
                        input_clone.next('select').on('change', function(){
                            if(this.value == 0)
                            {
                                this.previous('input').readOnly=true;
                                this.previous('input').value='';
                                this.previous('input').setStyle({'background':'none repeat scroll 0 0 #DDDDDD'});
                            }
                            else
                            {
                                this.previous('input').readOnly=false;
                                this.previous('input').setStyle({'background':'none repeat scroll 0 0 #FBFBFB'});
                            }
                        });

                        if(input_parent.next('div') != '')
                        {
                            while((input_parent = input_parent.next('div')) && (input_parent_original = input_parent_original.next('div')))
                            {
                                input_parent.down('input').name = input_parent.down('input').name.replace(/[0-9]/, parent.childElementCount-1);
                                input_parent.down('select').name = input_parent.down('select').name.replace(/[0-9]/, parent.childElementCount-1);
                                input_parent.down('select').options[input_parent_original.down('select').selectedIndex].selected = true;
                            }
                        }
                    }
                    else if(input_clone.hasClassName('jdisc_location')) {
                        var location_div = input_clone.up();
                        var location_id = input_clone.next('input').value;
                        location_field = location_field + '[_' + (parseInt(parent.childElementCount) - 1) + ']';
                        location_div.setAttribute('id', 'div_browser_location');
                        $('div_browser_location').update('');
                        new Ajax.Updater('div_browser_location', jdisc_ajax_url + '&func=location_browser&editMode=1', {
                            evalScripts: true,
                            parameters: {
                                'field':location_field,
                                'selected_object': location_id
                            },
                            onComplete: function() {
                                $('div_browser_location').removeAttribute('id');
                            }
                        });
                    }
                }
            }
        }

        e.appear({duration:0.3});
    };

    /**
     * Deletes assignment.
     */
    window.delete_assignment = function (type, assignment) {
        var parent = $(type),
            length = parent.parentNode.getElementsByTagName("TR").length;

        if (length > 2) {
            assignment.fade({duration:0.3});
            parent.removeChild(assignment);
        } else {
            var nodes = assignment.childNodes;
            for (i = 1; i < (nodes.length - 2); i += 2) {
                var input = nodes[i].getElementsByTagName('input')[0];
                if (input) {
                    if (!input.hasClassName('portFilter')) {
                        input.value = '';
                        input.hide();
                    }
                }
                var select = nodes[i].getElementsByTagName('select')[0];
                if(select) {
                    select.options[0].selected = true;
                    select.show();
                }
            }
        }
    };

    /**
     * Replaces select field with text field to edit assignment.
     */
    window.edit_assignment_field = function (element, setFocus) {
        element.hide();
        var inputField = element.parentNode.getElementsByTagName('input')[0];
        inputField.show();
        if (setFocus) {
            inputField.focus();
        }
    };

    /**
     * Adds a new filter for ports
     * @param element
     */
    window.add_port_condition = function(p_element, p_port_txt, p_port_type_selection, p_hide) {
        var cloned_element = p_element.cloneNode(true),
                input_field = cloned_element.firstElementChild,
                select_field = input_field.next();

        input_field.value = p_port_txt;

        if(p_port_type_selection != null)
            select_field.value = p_port_type_selection;

        if(select_field.value == 0)
        {
            input_field.readOnly=true;
            input_field.value='';
            input_field.setStyle({'background':'none repeat scroll 0 0 #DDDDDD'});
        }
        else
        {
            input_field.readOnly=false;
            input_field.setStyle({'background':'none repeat scroll 0 0 #FBFBFB'});
        }

        select_field.on('change', function(){
            if(this.value == 0)
            {
                this.previous('input').readOnly=true;
                this.previous('input').value='';
                this.previous('input').setStyle({'background':'none repeat scroll 0 0 #DDDDDD'});
            }
            else
            {
                this.previous('input').readOnly=false;
                this.previous('input').setStyle({'background':'none repeat scroll 0 0 #FBFBFB'});
            }
        });

        if(p_hide == true)
            cloned_element.lastElementChild.show();

        p_element.parentNode.insert(cloned_element);
    };

    /**
     * Removes the selected port filter
     * @param element
     */
    window.delete_port_condition = function(element) {
        element.remove();
    };

    /**
     * Reloads the profile
     * @param p_jdisc_server
     */
    window.reload_profile = function(p_jdisc_server) {

        $('switch-server-loader').removeClassName('hide');

        new Ajax.Request('?call=jdisc&ajax=1&func=get_profile_data', {
            method:"post",
            parameters:{
                jdisc_server:p_jdisc_server
            },
            onSuccess: function(transport){
                var json = transport.responseJSON;
                $('switch-server-loader').addClassName('hide');
                if(json.success){
                    var option_counters = $H(json.data.options_counters);
                    var operating_system = $H(json.data.operating_systems);
                    var celements = $A($('object_type_assignments').children);

                    /**
                     * reload operating system for each assignment row
                     */
                    celements.each(function(ele){
                        var sel_ele = ele.down('select', 1);
                        var cloned_first_ele = sel_ele.options[0].cloneNode();
                        var cloned_second_ele = sel_ele.options[1].cloneNode();
                        var selected_text = sel_ele.options[sel_ele.selectedIndex].text;
                        var index_counter = 0;

                        sel_ele.update(cloned_first_ele).insert(cloned_second_ele);

                        operating_system.each(function(ele1){
                            var is_selected = false;
                            if(ele1.value == selected_text)
                            {
                                is_selected = true;
                            }
                            sel_ele.insert(new Element('option', {value:ele1.key, selected:is_selected}).insert(ele1.value));
                            index_counter++;
                        });
                    });

                    /**
                     * update counters
                     */
                    option_counters.each(function(ele){
                        $(ele.key).update(ele.value);
                    });
                } else {
                    idoit.Notify.error(json.message);
                }
            }
        });
    }
</script>

<div id="jdisc-module-config">
	<h2 class="p5 gradient border-bottom">
	    <span class="fr"><a href="[{$link_to_jdisc_import}]" target="_blank" title="[{isys type='lang' ident='LC__MODULE__JDISC__IMPORT'}]">[{isys type='lang' ident='LC__MODULE__JDISC__LINK_TO_IMPORT'}]</a></span>
		[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES'}]
	</h2>

	<div class="p5">
	[{isys type='f_text' name='C__MODULE__JDISC__PROFILES__ID'}]
	</div>

	<h3 class="p5 gradient border-top border-bottom text-shadow">[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__COMMON_SETTINGS'}]</h3>
	<table class="contentTable" style="border-top: none;">
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__IMPORT__JDISC_SERVERS' ident='LC__MODULE__JDISC__IMPORT__JDISC_SERVERS'}]</td>
            <td class="value">
                [{isys type="f_dialog" name="C__MODULE__JDISC__IMPORT__JDISC_SERVERS" p_bSort=false p_onChange="window.reload_profile(this.value);"}]
                <img src="[{$dir_images}]ajax-loading.gif" id="switch-server-loader" class="vam hide">
            </td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__PROFILES__TITLE' ident='LC__MODULE__JDISC__PROFILES__TITLE'}]</td>
			<td class="value">[{isys type='f_text' name='C__MODULE__JDISC__PROFILES__TITLE'}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__PROFILES__DESCRIPTION' ident='LC__MODULE__JDISC__PROFILES__DESCRIPTION'}]</td>
			<td class="value">[{isys type='f_textarea' name='C__MODULE__JDISC__PROFILES__DESCRIPTION'}]</td>
		</tr>
	</table>

	<h3 class="p5 gradient border-top border-bottom text-shadow mt10">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS'}]</h3>
    <div style="overflow-x:auto;">
	<table class="listing" style="border-top: none;">
		<thead>
		<tr>
			<th>[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE'}]</th>
			<th>[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS'}]</th>
            <th>[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__TITLE_TRANSFORM'}]</th>
            <th>[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__FQDN_ADDITION'}]</th>
            <th>[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER'}]</th>
			<th>[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE'}]</th>
            <th>[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__LOCATION'}]</th>
			<th>[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__ACTIONS'}]</th>
		</tr>
		</thead>
		<tbody id="object_type_assignments">
            [{assign var="row_counter" value=0}]
			[{if count($object_type_assignments)>0}]
                [{foreach item="object_type_assignment" from=$object_type_assignments}]
                    [{include file=$object_type_assignment_file object_type_assignment=$object_type_assignment}]
                    [{assign var="row_counter" value=$row_counter+1}]
				[{/foreach}]
            [{else}]
                [{include file=$object_type_assignment_file}]
	        [{/if}]
		</tbody>
	</table>
    </div>

	<dl class="m10">
		<dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE'}]:</dt>
		<dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE__DESCRIPTION'}]</dd>

		<dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS'}]:</dt>
		<dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS__DESCRIPTION'}]</dd>

		<dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE'}]:</dt>
		<dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE__DESCRIPTION'}]</dd>

        <!-- TODOTODO Description of port filter -->

        <dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_NAME_TRANSFORMATION'}]:</dt>
        <dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_NAME_TRANSFORMATION__DESCRIPTION'}]</dd>

		<dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED'}]:</dt>
		<dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED__DESCRIPTION'}]</dd>
	</dl>

	<h3 class="p5 gradient border-top border-bottom text-shadow mt10">[{isys type='lang' ident='LC__MODULE__JDISC__ADDITIONAL_OPTIONS'}]</h3>
	<table class="contentTable" style="border-top: none;">
        <tr>
            <td class="key">[{isys type='f_label' name="C__MODULE__JDISC__PROFILES__CATEGORIES" ident='LC__MODULE__JDISC__PROFILES__CATEGORIES'}]</td>
            <td class="value">[{isys type='f_dialog_list' name='C__MODULE__JDISC__PROFILES__CATEGORIES' p_strStyle="width:350px;"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name="C__MODULE__JDISC__CHASSIS_INTERFACE_OPTION" ident='LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS'}]</td>
            <td class="value">[{isys type="f_dialog" name="C__MODULE__JDISC__CHASSIS_INTERFACE_OPTION" p_bDbFieldNN=1}]</td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__ONLY_CREATE_SOFTWARE_RELATIONS' ident='LC__MODULE__JDISC__SOFTWARE_IMPORT__IMPORT_ALL'}]</td>
			<td class="value pl20" style="vertical-align: middle">
				<input type="checkbox" id="C__MODULE__JDISC__ONLY_CREATE_SOFTWARE_RELATIONS" name="C__MODULE__JDISC__ONLY_CREATE_SOFTWARE_RELATIONS" [{if $import_all_software}]checked="checked"[{/if}] />
			</td>
		</tr>

		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__SOFTWARE_IMPORT__LICENCES' ident='LC__MODULE__JDISC__SOFTWARE_IMPORT__LICENCES'}]</td>
			<td class="value pl20" style="vertical-align: middle">
				<input type="checkbox" id="C__MODULE__JDISC__SOFTWARE_IMPORT__LICENCES" name="C__MODULE__JDISC__SOFTWARE_IMPORT__LICENCES" [{if $import_software_licences}]checked="checked"[{/if}] />
			</td>
		</tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__SOFTWARE_IMPORT__SERVICES' ident='LC__MODULE__JDISC__SOFTWARE_IMPORT__SERVICES'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__SOFTWARE_IMPORT__SERVICES" name="C__MODULE__JDISC__SOFTWARE_IMPORT__SERVICES" [{if $import_software_services}]checked="checked"[{/if}] />
            </td>
        </tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__SOFTWARE_IMPORT__CLOUD_SUBSCRIPTIONS' ident='LC__MODULE__JDISC__SOFTWARE_IMPORT__CLOUD_SUBSCRIPTIONS'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__SOFTWARE_IMPORT__CLOUD_SUBSCRIPTIONS" name="C__MODULE__JDISC__SOFTWARE_IMPORT__CLOUD_SUBSCRIPTIONS" [{if $import_cloud_subscriptions}]checked="checked"[{/if}] />
            </td>
        </tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONNECTION_ENDPOINT' ident='LC__MODULE__JDISC__IMPORT_CONNECTION_ENDPOINT'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__CONNECTION_ENDPOINT" name="C__MODULE__JDISC__CONNECTION_ENDPOINT" [{if $importConnectionEndpoint}]checked="checked"[{/if}] />
            </td>
        </tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__USE_SIMPLE_DATABASE_MODEL' ident='LC__MODULE__JDISC__USE_SIMPLE_DATABASE_MODEL'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__USE_SIMPLE_DATABASE_MODEL" name="C__MODULE__JDISC__USE_SIMPLE_DATABASE_MODEL" [{if $useSimpleDatabaseModel}]checked="checked"[{/if}] />
            </td>
        </tr>

		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__ONLY_CREATE_NETWORK_RELATIONS' ident='LC__MODULE__JDISC__NETWORK_IMPORT__IMPORT_ALL'}]</td>
			<td class="value pl20" style="vertical-align: middle">
				<input type="checkbox" id="C__MODULE__JDISC__ONLY_CREATE_NETWORK_RELATIONS" name="C__MODULE__JDISC__ONLY_CREATE_NETWORK_RELATIONS" [{if $import_all_networks}]checked="checked"[{/if}] />
			</td>
		</tr>
        <tr>
            <td class="key">[{isys type='f_label' name="C__MODULE__JDISC__PROFILES__NETWORK_ADRESSES" ident='LC__MODULE__JDISC__PROFILES__NETWORK_ADRESSES'}]</td>
            <td class="value">[{isys type='f_dialog_list' name='C__MODULE__JDISC__PROFILES__NETWORK_ADRESSES' p_strStyle="width:350px;"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name="C__MODULE__JDISC__IMPORT_TYPE_DHCP_ADRESSES" ident='LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_DHCP_ADRESSES'}]</td>
            <td class="value">[{isys type="f_dialog" name="C__MODULE__JDISC__IMPORT_TYPE_DHCP_ADRESSES" p_bDbFieldNN=1}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CREATE_VLAN_RELATIONS' ident='LC__MODULE__JDISC__VLAN_IMPORT__IMPORT_ALL'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__CREATE_VLAN_RELATIONS" name="C__MODULE__JDISC__CREATE_VLAN_RELATIONS" [{if $import_all_vlans}]checked="checked"[{/if}] />
            </td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CREATE_CLUSTER' ident='LC__MODULE__JDISC__CLUSTER_IMPORT__IMPORT_ALL'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__CREATE_CLUSTER" name="C__MODULE__JDISC__CREATE_CLUSTER" [{if $import_all_clusters}]checked="checked"[{/if}] />
            </td>
        </tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CREATE_BLADE_CONNECTIONS' ident='LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__IMPORT_ALL'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__CREATE_BLADE_CONNECTIONS" name="C__MODULE__JDISC__CREATE_BLADE_CONNECTIONS" [{if $import_all_blade_connections}]checked="checked"[{/if}] />
                [{if $blade_connections_counter > 0}][{$blade_chassis_connection_needed_types}][{/if}]
            </td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CHASSIS_ASSIGNED_MODULES_OBJTYPE' ident='LC__MODULE__JDISC__PROFILES__CHASSIS_ASSIGNED_OBJTYPE'}]</td>
			<td class="value">[{isys type="f_dialog" name="C__MODULE__JDISC__CHASSIS_ASSIGNED_MODULES_OBJTYPE" p_bDbFieldNN=1}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CHASSIS_ASSIGNED_MODULES_UPDATE_OBJTYPE' ident='LC__MODULE__JDISC__PROFILES__CHASSIS_ASSIGNED_MODULES_UPDATE_OBJTYPE'}]</td>
			<td class="value">[{isys type="f_dialog" name="C__MODULE__JDISC__CHASSIS_ASSIGNED_MODULES_UPDATE_OBJTYPE" p_bDbFieldNN=1 p_arData=get_smarty_arr_YES_NO()}]</td>
		</tr>

        [{if $is_jedi_version !== true}]
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__ADD_CUSTOM_ATTRIBUTES' ident='LC__MODULE__JDISC__ADD_CUSTOM_ATTRIBUTES'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__IMPORT_CUSTOM_ATTRIBUTES" name="C__MODULE__JDISC__IMPORT_CUSTOM_ATTRIBUTES" [{if $import_custom_attributes}]checked="checked"[{/if}] />
            </td>
        </tr>
        [{/if}]

        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__USE_DEFAULT_TEMPLATES' ident='LC__MODULE__JDISC__USE_DEFAULT_TEMPLATES'}]</td>
            <td class="value pl20" style="vertical-align: middle">
                <input type="checkbox" id="C__MODULE__JDISC__USE_DEFAULT_TEMPLATES" name="C__MODULE__JDISC__USE_DEFAULT_TEMPLATES" [{if $use_default_templates}]checked="checked"[{/if}] />
            </td>
        </tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CMDB_STATUS' ident='LC__MODULE__JDISC__PROFILES__CHANGE_CMDB_STATUS_OF_OBJECTS_TO'}]</td>
            <td class="value" style="vertical-align: middle">
                [{isys type="f_dialog" name="C__MODULE__JDISC__CMDB_STATUS" p_bDbFieldNN=1}]
            </td>
        </tr>

        <tr>
            <td class="key vat">[{isys type='f_label' name='C__MODULE__JDISC__SOFTWARE_FILTER' ident='LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER'}]</td>
            <td class="value" style="vertical-align: middle">
                [{isys type="f_dialog" name="C__MODULE__JDISC__SOFTWARE_FILTER_TYPE"}]
                <br class="cb" />
                [{isys type="f_textarea" name="C__MODULE__JDISC__SOFTWARE_FILTER" p_strClass="mt5"}]
            </td>
        </tr>
        <tr>
            <td class="key vat">[{isys type='f_label' name='C__MODULE__JDISC__SOFTWARE_FILTER_TYPE_REGEXP' ident='LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE_REGEXP'}]</td>
            <td class="value" style="vertical-align: middle">
                [{isys type="f_dialog" name="C__MODULE__JDISC__SOFTWARE_FILTER_TYPE_REGEXP"}]
            </td>
        </tr>
		<tr>
			<td class="key vat">[{isys type='f_label' name='C__MODULE__JDISC__SOFTWARE_OBJ_TITEL' ident='LC__MODULE__JDISC__PROFILES__SOFTWARE_OBJ_TITEL'}]</td>
			<td class="value">[{isys type='f_dialog' name='C__MODULE__JDISC__SOFTWARE_OBJ_TITEL' p_bDbFieldNN=0}]</td>
		</tr>
		<tr>
			<td class="key vat">[{isys type='f_label' name='C__MODULE__JDISC__OBJECT_MATCHING_PROFILE' ident='LC__MODULE__JDISC__PROFILES__OBJECT_MATCHING_PROFILE'}]</td>
			<td class="value">[{isys type='f_dialog' name='C__MODULE__JDISC__OBJECT_MATCHING_PROFILE' p_bDbFieldNN=0}]</td>
		</tr>

		<tr>
			<td class="key vat">[{isys type='f_label' name='C__MODULE__JDISC__UPDATE_OBJTYPE' ident='LC__MODULE__JDISC__PROFILES__UPDATE_OBJTYPE'}]</td>
			<td class="value">[{isys type='f_dialog' name='C__MODULE__JDISC__UPDATE_OBJTYPE' p_bDbFieldNN=1 p_arData=get_smarty_arr_YES_NO()}]</td>
		</tr>

		<tr>
			<td class="key vat">[{isys type='f_label' name='C__MODULE__JDISC__UPDATE_OBJ_TITLE' ident='LC__MODULE__JDISC__PROFILES__UPDATE_OBJ_TITLE'}]</td>
			<td class="value">[{isys type='f_dialog' name='C__MODULE__JDISC__UPDATE_OBJ_TITLE' p_bDbFieldNN=1 p_arData=get_smarty_arr_YES_NO()}]</td>
		</tr>
	</table>

	<dl class="m10">
		<dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__SOFTWARE_IMPORT__IMPORT_ALL'}]:</dt>
		<dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__SOFTWARE_IMPORT__IMPORT_ALL__DESCRIPTION'}]</dd>
		<dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__SOFTWARE_IMPORT__COUNTER'}]: <span style="font-weight: bold;" id="software_counter">[{$software_counter}]</span></dd>
		<dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__NETWORK_IMPORT__IMPORT_ALL'}]:</dt>
		<dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__NETWORK_IMPORT__IMPORT_ALL__DESCRIPTION'}]</dd>
		<dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__NETWORK_IMPORT__COUNTER'}]: <span style="font-weight: bold;" id="network_counter">[{$network_counter}]</span></dd>
        <dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__CLUSTER_IMPORT__IMPORT_ALL'}]:</dt>
        <dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__CLUSTER_IMPORT__IMPORT_ALL__DESCRIPTION'}]</dd>
        <dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__CLUSTER_IMPORT__COUNTER'}]: <span style="font-weight: bold;" id="cluster_counter">[{$cluster_counter}]</span></dd>
        <dt style="font-weight: bold; margin-top: 1em;">[{isys type='lang' ident='LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__IMPORT_ALL'}]:</dt>
        <dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__IMPORT_ALL__DESCRIPTION'}]</dd>
        <dd style="margin-left: 2em;">[{isys type='lang' ident='LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__COUNTER'}]: <span style="font-weight: bold;" id="blade_connections_counter">[{$blade_connections_counter}]</span></dd>
	</dl>
</div>
[{/if}]