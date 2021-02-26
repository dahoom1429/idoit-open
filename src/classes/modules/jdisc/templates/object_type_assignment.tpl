[{* Smarty template for JDisc object type assignment
    @ author: Benjamin Heisig <bheisig@i-doit.org>
    @ copyright: synetics GmbH
    @ license: <http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3>
*}]

<tr id="assignment_[{$object_type_assignment.id}]">
    <td>
        <select id="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE[]" onchange="if(this.value == '-2'){edit_assignment_field(this, true);}" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE[]" class="input input-small">
            <option value="-1">&ndash;</option>
            <option value="-2" style="font-style: italic;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED'}]</option>
            [{foreach key=value item="title" from=$jdisc_types}]
                <option value="[{$value}]"[{if $value === $object_type_assignment.jdisc_type}] selected="selected"[{/if}]>[{$title}]</option>
            [{/foreach}]
        </select>

        <input type="text" class="input input-small" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE_CUSTOMIZED[]" style="display: none;" placeholder="*" value="[{$object_type_assignment.jdisc_type_customized}]" />

        [{if !empty($object_type_assignment.jdisc_type_customized)}]
        <script type="text/javascript">
            var selection = $('assignment_[{$object_type_assignment.id}]').getElementsByTagName('td')[0].getElementsByTagName('select')[0];
            window.edit_assignment_field(selection, false);
        </script>
        [{/if}]
    </td>
    <td>
        <select id="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS[]" onchange="if(this.value == '-2'){edit_assignment_field(this, true);}" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS[]" class="input input-small">
            <option value="-1">&ndash;</option>
            <option value="-2" style="font-style: italic;">[{isys type='lang' ident='LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED'}]</option>
            [{foreach key=value item="title" from=$jdisc_operating_systems}]
                <option value="[{$value}]"[{if $value === $object_type_assignment.jdisc_os}] selected="selected"[{/if}]>[{$title}]</option>
            [{/foreach}]
        </select>

        <input type="text" class="input input-small" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS_CUSTOMIZED[]" style="display: none;" placeholder="*" value="[{$object_type_assignment.jdisc_os_customized}]" />

        [{if !empty($object_type_assignment.jdisc_os_customized)}]
        <script type="text/javascript">
            var selection = $('assignment_[{$object_type_assignment.id}]').getElementsByTagName('td')[1].getElementsByTagName('select')[0];
            window.edit_assignment_field(selection, false);
        </script>
        [{/if}]
    </td>
    <td>
        <select class="input input-mini" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__TITLE_TRANSFORM[]">
            [{foreach key=value item="title" from=$titleTransformation}]
            <option value="[{$value}]"[{if $value === $object_type_assignment.title_transform}] selected="selected"[{/if}]>[{isys type='lang' ident=$title}]</option>
            [{/foreach}]
        </select>
    </td>
    <td>
        <input  type="text" class="input input-mini fqdn_addition" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__FQDN_ADDITION[]" value="[{$object_type_assignment.fqdn_addition}]" />
    </td>
    <td>
        <div class="m5" id="test_[{$object_type_assignment.id}]" style="white-space: nowrap">
            <input type="text" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER[[{$row_counter}]][]" class="portFilter input input-mini mr5">
            <select class="portFilterType input input-mini" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER_TYPE[[{$row_counter}]][]">
                <option value="0">
                    [{isys type="lang" ident="LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__NORMAL"}]
                </option>
                <option value="1">
                    [{isys type="lang" ident="LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__LOGICAL_PORT"}]
                </option>
                <option value="2">
                    [{isys type="lang" ident="LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__PHYSICAL_PORT"}]
                </option>
                <option value="4">
                    [{isys type="lang" ident="LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__FC_PORT"}]
                </option>
                <option value="3">
                    [{isys type="lang" ident="LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__NO_IMPORT"}]
                </option>
            </select>
            <button type="button" class="btn" onclick="window.add_port_condition(this.parentNode, '', null, true);" title="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__ADD_NEW_ASSIGNMENT'}]">
	            <img src="[{$dir_images}]icons/silk/add.png" alt="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__ADD_NEW_ASSIGNMENT'}]" />
            </button>
	        <button type="button" class="btn" style="display:none" onclick="window.delete_port_condition(this.parentNode)" title="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__DELETE_THIS_ASSIGNMENT'}]">
                <img  src="[{$dir_images}]icons/silk/delete.png" alt="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__DELETE_THIS_ASSIGNMENT'}]" />
	        </button>
        </div>
        <script type="text/javascript">
            [{if $object_type_assignment.port_filter && $object_type_assignment.port_filter != '["undefined"]'}]
                var port_filter = [{$object_type_assignment.port_filter}];
                if (port_filter) {
	                $('test_[{$object_type_assignment.id}]').down('input').value = port_filter[0];
                }
            [{else}]
                var port_filter = null;
            [{/if}]

            [{if $object_type_assignment.port_filter_type != ''}]
                var port_filter_type = [{$object_type_assignment.port_filter_type}];
	            if (port_filter_type) {
		            $('test_[{$object_type_assignment.id}]').down('select').value = port_filter_type[0];
	            }
            [{else}]
                var port_filter_type = null;
            [{/if}]

            if($('test_[{$object_type_assignment.id}]').down('select').value != 0)
            {
                $('test_[{$object_type_assignment.id}]').down('input').readOnly=false;
            }
            else
            {
                $('test_[{$object_type_assignment.id}]').down('input').readOnly=true;
                $('test_[{$object_type_assignment.id}]').down('input').setStyle({'background':'none repeat scroll 0 0 #DDDDDD'});
            }

            $('test_[{$object_type_assignment.id}]').down('select').on('change', function(){
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

            if(port_filter_type !== null && port_filter_type.length > 1) {
                port_filter_type.each(function(ele, index){
                    if(index > 0) {
                        var filter_text = (port_filter !== null)? port_filter[index]: '';
                        window.add_port_condition($('test_[{$object_type_assignment.id}]'), filter_text, ele, true);
                    }
                });
            }

        </script>
    </td>
    <td>
        <select id="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE[]" name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE[]" class="input input-mini m5">
            <option value="-1">&ndash;</option>
            [{foreach key=value item="title" from=$object_types}]
                <option value="[{$value}]"[{if $value === $object_type_assignment.object_type}] selected="selected"[{/if}]>[{$title}]</option>
            [{/foreach}]
        </select>
    </td>
    <td>
        <div class="assignment_browser" style="white-space: nowrap">
            [{isys
                name="C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_LOCATION[[{$row_counter}]]"
                type="f_popup"
                p_strClass="input-mini jdisc_location"
                p_strPopupType="browser_location"
                p_strSelectedID="[{$object_type_assignment.location}]"
                p_bInfoIconSpacer=0
                containers_only=true}]
        </div>
    </td>
	<td style="white-space: nowrap">
		<button type="button" class="btn" title="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__ADD_NEW_ASSIGNMENT'}]" onclick="window.add_new_assignment('object_type_assignments', this.parentNode.parentNode, true)">
			<img src="[{$dir_images}]icons/silk/add.png" alt="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__ADD_NEW_ASSIGNMENT'}]" />
		</button>
		<button type="button" class="btn" title="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__DUPLICATE_ASSIGNMENT'}]" onclick="window.add_new_assignment('object_type_assignments', this.parentNode.parentNode, false)">
			<img src="[{$dir_images}]icons/silk/page_copy.png" alt="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__DUPLICATE_ASSIGNMENT'}]" />
		</button>
		<button type="button" class="btn" title="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__DELETE_THIS_ASSIGNMENT'}]" onclick="window.delete_assignment('object_type_assignments', this.parentNode.parentNode)">
			<img src="[{$dir_images}]icons/silk/delete.png" alt="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES__DELETE_THIS_ASSIGNMENT'}]" />
		</button>
	</td>
</tr>