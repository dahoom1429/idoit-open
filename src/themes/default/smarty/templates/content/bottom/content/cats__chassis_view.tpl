<table class="contentTable mb10">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS_VIEW__X_FRONT" name="C__CMDB__CATS__CHASSIS_VIEW__X_FRONT"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__CHASSIS_VIEW__X_FRONT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS_VIEW__Y_FRONT" name="C__CMDB__CATS__CHASSIS_VIEW__Y_FRONT"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__CHASSIS_VIEW__Y_FRONT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT" name="C__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT" p_bDbFieldNN="1" p_bSort=false}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS_VIEW__X_REAR" name="C__CMDB__CATS__CHASSIS_VIEW__X_REAR"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__CHASSIS_VIEW__X_REAR"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS_VIEW__Y_REAR" name="C__CMDB__CATS__CHASSIS_VIEW__Y_REAR"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__CHASSIS_VIEW__Y_REAR"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR" name="C__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR" p_bDbFieldNN="1" p_bSort=false}]</td>
	</tr>
	<tr id="matrix-size-notice">
		<td colspan="2">
			<p class="text-red">* [{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_VIEW__MATRIX_SIZE_CHANGE_NOTICE"}]</p>
		</td>
	</tr>
</table>

<table id="chassis">
	<tr>
		<td class="vat">
			<div class="box mb15 mr5" style="width:auto;">
				<h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_ENCLOSURE"}] [{isys type="lang" ident="LC__UNIVERSAL__FRONT"}]</h3>
				<div id="chassis_front" class="canvas"></div>
			</div>
			<div class="box mb15 mr5" style="width:auto;">
				<h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_ENCLOSURE"}] [{isys type="lang" ident="LC__UNIVERSAL__REAR"}]</h3>
				<div id="chassis_rear" class="canvas"></div>
			</div>
		</td>
		<td class="vat">
			<div id="notification" class="p5 mb15"></div>

			<div class="box mb15">
				<h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_VIEW__AVAILABLE_SLOTS"}]</h3>

				<div id="unassigned-slot-list">
				[{foreach from=$slots item=slot}]
					<div class="unassigned-slot navbar_item" data-assigned="[{$slot.assigned|intval}]" data-insertion="[{$slot.isys_cats_chassis_slot_list__insertion}]" data-slot-css="m[{$slot.isys_cats_chassis_slot_list__y_from}]-[{$slot.isys_cats_chassis_slot_list__x_from}]" data-slot-id="[{$slot.isys_cats_chassis_slot_list__id}]" data-slot-title="[{$slot.isys_cats_chassis_slot_list__title}]">
						<img src="[{$dir_images}]icons/silk/brick_[{if $slot.assigned === false}]add[{else}]edit[{/if}].png" alt="Add" class="vam" /> [{$slot.isys_cats_chassis_slot_list__title}] ([{if $slot.isys_cats_chassis_slot_list__insertion == $smarty.const.C__INSERTION__FRONT}][{isys type="lang" ident="LC__UNIVERSAL__FRONT"}][{else}][{isys type="lang" ident="LC__UNIVERSAL__REAR"}][{/if}])
					</div>
				[{/foreach}]
				</div>
			</div>

			<div id="slot_options" class="box">
				<h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_VIEW__SLOT_OPTIONS"}]</h3>
				<div class="m5">
					<div id="option-reassign" class="navbar_item"><img src="[{$dir_images}]icons/silk/arrow_switch.png" alt="Switch" class="vam" /> [{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_VIEW__CHANGE_POSITION"}]</div>
					<div id="option-remove" class="navbar_item"><img src="[{$dir_images}]icons/silk/cross.png" alt="Cross" /> [{isys type="lang" ident="LC__UNIVERSAL__RESET_ASSIGNMENT"}]</div>

					<div id="option-save" class="navbar_item"><img src="[{$dir_images}]icons/silk/disk.png" alt="Cross" /> [{isys type="lang" ident="LC__NAVIGATION__NAVBAR__SAVE"}]</div>

					<p id="option-assign-description" class="m5 p5 box-green"><img src="[{$dir_images}]icons/silk/information.png" alt="Info" class="vam" /> [{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_VIEW__DESCRIPTION"}]</p>

					<div id="info"></div>
				</div>
			</div>

            <div id="slot_assigned_devices" class="box mt15">
                <h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_DEVICES"}]</h3>
                <div class="m5" id="assigned_devices"></div>
            </div>
		</td>
	</tr>
</table>

<script type="text/javascript">
	var matrix_front = '[{$matrix_front|escape:"quotes"}]'.evalJSON(),
		devices_front = '[{$devices_front|escape:"quotes"}]'.evalJSON(),
		matrix_rear = '[{$matrix_rear|escape:"quotes"}]'.evalJSON(),
		devices_rear = '[{$devices_rear|escape:"quotes"}]'.evalJSON(),
		editmode = !! $('C__CMDB__CATS__CHASSIS_VIEW__X_FRONT'), // This is more reliable than the "is_edit_mode()" function.
		unassigned_slots = $$('div.unassigned-slot');

	$('matrix-size-notice').hide();

	idoit.Require.require('chassis', function () {
        // Add a hover observer for highlighting the corresponding slot in the matrix.
        unassigned_slots.invoke('on', 'mouseover', function() {
            var slot;

            if (this.readAttribute('data-insertion') == 1) {
                slot = $$('#chassis_front td.slot.' + this.readAttribute('data-slot-css'))[0];
            } else {
                slot = $$('#chassis_rear td.slot.' + this.readAttribute('data-slot-css'))[0];
            }

            if (slot) {
                slot.addClassName('hover');
            }
        });

        // Add a hover observer for highlighting the corresponding slot in the matrix.
        unassigned_slots.invoke('on', 'mouseleave', function() {
            $$('td.slot').invoke('removeClassName', 'hover');
        });

        // The following stuff shall only be available in edit-mode.
        if (editmode) {
            // Add the click observer for editing the assignment.
            unassigned_slots.invoke('on', 'click', window.display_slot_options);

            $('option-reassign').on('click', window.remove_slot_position);
            $('option-remove').on('click', window.remove_slot_position);
            $('option-save').on('click', window.save_slot_position);

            $('C__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT', 'C__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR').invoke('on', 'change', window.change_gridsize);

            if ($('C__CMDB__CATS__CHASSIS_VIEW__X_FRONT')) {
                $('C__CMDB__CATS__CHASSIS_VIEW__X_FRONT', 'C__CMDB__CATS__CHASSIS_VIEW__Y_FRONT', 'C__CMDB__CATS__CHASSIS_VIEW__X_REAR', 'C__CMDB__CATS__CHASSIS_VIEW__Y_REAR').invoke('on', 'change', window.change_matrix_size);
            }
        }

        // Function for changing the grid size.
        window.change_gridsize = function () {
            var x, y;
            window.chassis_front.set_size($('C__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT').getValue()).reset_html();
            x = $('C__CMDB__CATS__CHASSIS_VIEW__X_FRONT').value;
            $('chassis_front').down('table').setStyle({width: (x*window.chassis_front.getGridSize()) + 'px'});

            window.chassis_rear.set_size($('C__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR').getValue()).reset_html();
            x = $('C__CMDB__CATS__CHASSIS_VIEW__X_REAR').value;
            $('chassis_rear').down('table').setStyle({width: (x*window.chassis_rear.getGridSize()) + 'px'});
        };

        // This function will resize the matrix "on-the-fly".
        window.change_matrix_size = function () {
            var x, y;
            // Show the size-notice.
            $('matrix-size-notice').show();

            x = $('C__CMDB__CATS__CHASSIS_VIEW__X_FRONT').value;
            y = $('C__CMDB__CATS__CHASSIS_VIEW__Y_FRONT').value;
            $('chassis_front').down('table').setStyle({width: (x*window.chassis_front.getGridSize()) + 'px'});
            window.chassis_front.set_x(x).set_y(y).reset_html();

            x = $('C__CMDB__CATS__CHASSIS_VIEW__X_REAR').value;
            y = $('C__CMDB__CATS__CHASSIS_VIEW__Y_REAR').value;
            $('chassis_rear').down('table').setStyle({width: (x*window.chassis_rear.getGridSize()) + 'px'});
            window.chassis_rear.set_x(x).set_y(y).reset_html();
        };

        // This function will be called after each action to reset all buttons and data-attributes.
        window.reset_slot_options = function () {
            $('notification', 'slot_options', 'slot_assigned_devices', 'option-assign-description', 'option-reassign', 'option-remove', 'option-save').invoke('hide').invoke('writeAttribute', 'data-slot-id', null);
            window.chassis_front.set_editmode(false).reset_html();
            window.chassis_rear.set_editmode(false).reset_html();
            $$('div.unassigned-slot.selected').invoke('removeClassName', 'selected');
            $('info').update().hide();
        };

        window.infobox = function (ev) {
            var $trigger = ev.findElement('button') || ev.findElement('img'),
                $td;

            if ($trigger.tagName.toLowerCase() === 'button') {
                // Our click does not come from the slot-info-button, but from the option-info-button.
                $td = $('chassis').down('td[data-slot-id=' + $trigger.up('li').readAttribute('data-slot-id') + ']');
            } else {
                $td = $trigger.up('td');
            }

            var slot_title = $td.readAttribute('data-slot-title'),
                devices = $td.select('div.device'),
                device,
                info = $('info').update().show();

            devices.each(function(el) {
                device = el.clone().setStyle({height: null, width: null}).addClassName('mb5 p5');

                if (el.hasAttribute('data-object-id')) {
                    device.update(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + el.readAttribute('data-object-id')}).update(el.innerHTML + ' (' + el.readAttribute('data-object-type') + ')'));
                } else {
                    device.update(el.innerHTML);
                }

                info.insert(device);
            }.bind(this));

            $('slot_options').show().down('h3').update(slot_title);
        };

        // Displays the options, according to the slot status (assigned, not assigned).
        window.display_slot_options = function () {
            window.reset_slot_options();
            $$('td.slot').invoke('removeClassName', 'selected');

            $('slot_options').down('h3').update(this.readAttribute('data-slot-title'));

            this.addClassName('selected');

            var data_slot_id = this.readAttribute('data-slot-id');

            $('option-reassign', 'option-remove', 'option-save').invoke('writeAttribute', 'data-slot-id', data_slot_id);

            window.show_assigned_devices(data_slot_id);

            if (this.readAttribute('data-assigned') == 1) {
                // Display "re-assign" and "remove".
                var slot;

                if (this.readAttribute('data-insertion') == 1) {
                    slot = $$('#chassis_front td.slot.' + this.readAttribute('data-slot-css'))[0];
                } else {
                    slot = $$('#chassis_rear td.slot.' + this.readAttribute('data-slot-css'))[0];
                }

                if (slot) {
                    slot.addClassName('selected');
                }

                $('option-reassign', 'option-remove').invoke('show');
            } else {
                // Display "assign" while passing the insertion.
                window.assign_slot_position(this.readAttribute('data-insertion'));
            }

            $('slot_options').show();
            $('slot_assigned_devices').show();
        };

        // This function shows all assigned devices of the selected slot
        window.show_assigned_devices = function (data_slot_id) {
            new Ajax.Request('?ajax=1&call=chassis&func=get_assigned_devices',
                {
                    parameters: {
                        chassis_slot_id: data_slot_id
                    },
                    method: "post",
                    onSuccess: function (transport) {
                        var json = transport.responseJSON;
                        var div_container = '';

                        $('assigned_devices').update('');
                        if(json.success)
                        {
                            json.devices.each(function(ele){
                                div_container = new Element('div', {'class' : 'assigned-devices m5'}).update(
                                    new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + ele.id}).update(ele.title));
                                $('assigned_devices').insert(div_container);
                            });
                        }
                        else
                        {
                            div_container = new Element('div', {'class' : 'box-red p5'}).update(json.message);
                            $('assigned_devices').insert(div_container);
                        }
                    }
                });
        };


        // This function sets the matrix to edit-view and displays a helpful-text.
        window.assign_slot_position = function (insertion) {

            if (insertion == '[{$smarty.const.C__INSERTION__FRONT}]') {
                window.chassis_front.set_editmode(true).reset_html();
            } else if (insertion == '[{$smarty.const.C__INSERTION__REAR}]') {
                window.chassis_rear.set_editmode(true).reset_html();
            }

            $('option-assign-description', 'option-save').invoke('show');
        };

        // This function finally saves our new assignment.
        window.save_slot_position = function () {
            var slot_id = this.readAttribute('data-slot-id'),
                x1,
                x2,
                y1,
                y2,
                checkboxes = $$('#chassis_front input:checked, #chassis_rear input:checked'),
                insertion = $$('div.unassigned-slot[data-slot-id=' + slot_id + ']')[0].readAttribute('data-insertion');

            if (checkboxes.length == 1) {
                x1 = x2 = checkboxes[0].id.split('-')[0];
                y1 = y2 = checkboxes[0].id.split('-')[1];
            } else if (checkboxes.length == 2) {
                x1 = checkboxes[0].id.split('-')[0];
                y1 = checkboxes[0].id.split('-')[1];
                x2 = checkboxes[1].id.split('-')[0];
                y2 = checkboxes[1].id.split('-')[1];
            } else {
                $('notification').removeClassName('box-green').addClassName('box-red').update('Bitte wählen Sie mindestens ein Feld aus.').show();
                return;
            }

            new Ajax.Request('?ajax=1&call=chassis&func=assign_slot_position',
                {
                    parameters:{
                        obj_id: [{$obj_id}],
                        chassis_slot_id: slot_id,
                        insertion: insertion,
                        x1: x1,
                        x2: x2,
                        y1: y1,
                        y2: y2
                    },
                    method:"post",
                    onSuccess:function (transport) {
                        var json = transport.responseJSON,
                            notification_class;

                        if (json.success) {
                            notification_class = 'box-green';

                            // Update the matrix with the new position.
                            window.chassis_front.set_editmode(false).set_matrix(json.matrix.front).set_devices(json.devices.front).reset_html();
                            window.chassis_rear.set_editmode(false).set_matrix(json.matrix.rear).set_devices(json.devices.rear).reset_html();

                            // Update the data attribute, so the new positioned slot will be highlighted.
                            $$('div.unassigned-slot[data-slot-id=' + slot_id + ']')[0]
                                .writeAttribute('data-slot-css', 'm' + ((y1 > y2) ? y2 : y1) + '-' + ((x1 > x2) ? x2 : x1))
                                .writeAttribute('data-assigned', 1)
                                .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/brick_edit.png');

                            // Remove the slot-option box.
                            window.reset_slot_options();
                        } else {
                            notification_class = 'box-red';
                        }

                        // Remove the "box-red" and "box-green" classes and add new ones + the message.
                        $('notification')
                            .removeClassName('box-green')
                            .removeClassName('box-red')
                            .addClassName(notification_class)
                            .update(json.message)
                            .show();

                    }.bind(this)
                });
        };

        // This function will remove a slot position.
        window.remove_slot_position = function (ev) {
            var $trigger = ev.findElement('button') || ev.findElement('div'),
                slot_id, slot_action;

            if ($trigger.tagName.toLowerCase() === 'button') {
                slot_id = $trigger.up('li').readAttribute('data-slot-id');
                slot_action = $trigger.up('li').readAttribute('id');
            } else {
                slot_id = $trigger.readAttribute('data-slot-id');
                slot_action = $trigger.readAttribute('id');
            }

            $('slot_options').show()
                .down('h3').update($('unassigned-slot-list').down('div.unassigned-slot[data-slot-id=' + slot_id + ']').readAttribute('data-slot-title'));

            new Ajax.Request('?ajax=1&call=chassis&func=remove_slot_position',
                {
                    parameters:{
                        obj_id: [{$obj_id}],
                        chassis_slot_id: slot_id
                    },
                    method:"post",
                    onSuccess:function (transport) {
                        var json = transport.responseJSON,
                            notification_class;

                        if (json.success) {
                            notification_class = 'box-green';

                            // Update the matrix with the new position.
                            window.chassis_front.set_matrix(json.matrix.front).set_devices(json.devices.front).reset_html();
                            window.chassis_rear.set_matrix(json.matrix.rear).set_devices(json.devices.rear).reset_html();

                            // Update the data attribute, so the new positioned slot will be highlighted.
                            var slot = $$('div.unassigned-slot[data-slot-id=' + slot_id + ']')[0]
                                .writeAttribute('data-slot-css', null)
                                .writeAttribute('data-assigned', 0);

                            slot.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/brick_add.png');

                            if (slot_action == 'option-reassign' || slot_action == 'slot-option-reassign') {
                                $('option-reassign', 'option-remove').invoke('hide');

                                // If we click in the list and then on the "slot-option" drop down we may still have the old slot-id.
                                $('option-save').writeAttribute('data-slot-id', slot_id);

                                // Call the assign-feature, because we are re-assigning.
                                window.assign_slot_position(slot.readAttribute('data-insertion'));
                            }
                        } else {
                            notification_class = 'box-red';
                        }

                        if (slot_action != 'option-reassign' && slot_action != 'slot-option-reassign') {
                            // Remove the "box-red" and "box-green" classes and add new ones + the message.
                            $('notification').removeClassName('box-green').removeClassName('box-red').addClassName(notification_class).update(json.message).show();
                            window.reset_slot_options();
                        }
                    }
                });
        };

        // This callback gets called, when a collision is detecting (while editing the matrix).
        window.collision_callback = function (collision) {
            if (collision === true) {
                $('option-save').hide();
                $('notification').addClassName('box-red').update('[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_SLOTS__ALREADY_ASSIGNED"}]').show();
            } else {
                $('option-save').show();
                $('notification').hide().update().removeClassName('box-red');
            }
        };

        // This callback will be used for the "option-click" (the "drop-down" icon inside an assigned slot).
        window.options_callback = function (ev) {
            var $contextWrapper = $('chassisContextPopup'),
                x = Event.pointerX(ev) - 5,
                y = Event.pointerY(ev) - 5,
                element = ev.findElement().up('.slot'),
                slot_id = element.readAttribute('data-slot-id'),
                slot_title = $$('div.unassigned-slot[data-slot-id=' + slot_id + ']')[0].readAttribute('data-slot-title'),
                $ul_options = new Element('ul', {className:'list-style-none m5'});

            if ($contextWrapper) {
                $contextWrapper.remove();
            }

            $ul_options.insert(
                new Element('li', {id: 'slot-option-reassign', 'data-slot-id': slot_id}).update(
                    new Element('button', {className: 'btn btn-small btn-block', type: 'button'})
                        .update(new Element('img', {src: '[{$dir_images}]icons/silk/arrow_switch.png', className: 'mr5'}))
                        .insert(new Element('span').update('[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_VIEW__CHANGE_POSITION"}]'))
                        .observe('click', window.remove_slot_position)
                )
            );

            $ul_options.insert(
                new Element('li', {id: 'slot-option-remove', 'data-slot-id': slot_id}).update(
                    new Element('button', {className: 'btn btn-small btn-block', type: 'button'})
                        .update(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png', className: 'mr5'}))
                        .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__RESET_ASSIGNMENT"}]'))
                        .observe('click', window.remove_slot_position)
                )
            );

            // Check if we have some assigned devices, before we display the info button.
            if (!!element.down('div.device')) {
                $ul_options.insert(
                    new Element('li', {id: 'option-information', 'data-slot-id': slot_id}).update(
                        new Element('button', {className: 'btn btn-small btn-block', type: 'button'})
                            .update(new Element('img', {src: '[{$dir_images}]icons/silk/information.png', className: 'mr5'}))
                            .insert(new Element('span').update('[{isys type="lang" ident="LC_UNIVERSAL__INFORMATION"}]'))
                            .observe('click', window.infobox)
                    )
                );
            }

            $contextWrapper = new Element('div', {id: 'chassisContextPopup', className:'popup', style: 'top: ' + y + 'px; left: ' + x + 'px'})
                .update(new Element('div', {className:'p5 popup-header'}).update(slot_title))
                .insert($ul_options);

            $contextWrapper.on('mouseleave', function () {
                if ($('chassisContextPopup')) {
                    $('chassisContextPopup').remove();
                }
            });

            $('contentBottomContent').insert($contextWrapper);
        };

        // Global variables.
        window.chassis_front = new Chassis('chassis_front', {
            x: [{$cat_data.isys_cats_chassis_view_list__front_width|default:0}],
            y: [{$cat_data.isys_cats_chassis_view_list__front_height|default:0}],
            matrix: matrix_front,
            devices: devices_front,
            editmode: false,
            collision_callback: window.collision_callback,
            options_active: editmode,
            option_callback: window.options_callback,
            info_active: ! editmode,
            info_callback: window.infobox,
            size:[{$cat_data.isys_cats_chassis_view_list__front_size|default:2}]
        });

        window.chassis_rear = new Chassis('chassis_rear', {
            x: [{$cat_data.isys_cats_chassis_view_list__rear_width|default:0}],
            y: [{$cat_data.isys_cats_chassis_view_list__rear_height|default:0}],
            matrix: matrix_rear,
            devices: devices_rear,
            editmode: false,
            collision_callback: window.collision_callback,
            options_active: editmode,
            option_callback: window.options_callback,
            info_active: ! editmode,
            info_callback: window.infobox,
            size:[{$cat_data.isys_cats_chassis_view_list__rear_size|default:2}]
        });

        window.reset_slot_options();
    });
</script>