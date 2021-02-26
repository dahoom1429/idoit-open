<div class="p10">
	<div id="rack_stats" class="box-white mb5">
	    <h3 class="p5 gradient mouse-pointer text-shadow"><img class="vam" src="[{$dir_images}]icons/silk/bullet_arrow_right.png" alt="Icon" /> [{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS"}]</h3>
		<div class="m5 hide">
	         <!-- To be filled by AJAX -->
	    </div>
	</div>

	<div id="rackview">
		<div class="fl">
			<h3 class="p5 gradient text-shadow text-center border">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_FRONT"}]</h3>
			<div id="rack_front">
				<!-- To be filled by JS -->
			</div>
		</div>

		<div class="fl ml5 mr10">
			<h3 class="p5 ml5 gradient text-shadow text-center border">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_BACK"}]</h3>
			<div id="rack_rear" class="ml5">
				<!-- To be filled by JS -->
			</div>
		</div>

		<div id="side_box" class="fl">
			<button id="rack_detail_view_button" type="button" class="btn btn-block mb10">
				<img src="[{$dir_images}]icons/silk/zoom.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__CATS__RACK__DETAILS_BUTTON"}]</span>
			</button>

			<div class="mb10 box-white text-black">
				<h3 class="p5 gradient text-shadow border-bottom border-grey text-normal">[{isys type="lang" ident="LC__CMDB__CATS__RACK__ATTRIBUTES"}]</h3>

				<div class="m5">
					<table>
						<tr>
							<td class="key">
								[{isys type="f_label" ident="LC__CMDB__CATS__ENCLOSURE__SLOT_SORTING" name="C__CATS__ENCLOSURE__UNIT_SORTING"}]
							</td>
							<td class="value">
								[{isys type="f_dialog" name="C__CATS__ENCLOSURE__UNIT_SORTING" p_bDbFieldNN=true}]
							</td>
						</tr>
						<tr>
							<td class="key">
								[{isys type="f_label" ident="LC__CMDB__CATS__ENCLOSURE__VERTICAL_SLOTS" name="C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT"}] ([{isys type="lang" ident="LC__UNIVERSAL__FRONT"}])
							</td>
							<td class="value">
								[{isys type="f_dialog" name="C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT"}]
							</td>
						</tr>
						<tr>
							<td class="key">
								[{isys type="f_label" ident="LC__CMDB__CATS__ENCLOSURE__VERTICAL_SLOTS" name="C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR"}] ([{isys type="lang" ident="LC__UNIVERSAL__REAR"}])
							</td>
							<td class="value">
								[{isys type="f_dialog" name="C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR"}]
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div id="unassigned_objects" class="mb10 box-white text-black">
				<h3 class="p5 gradient text-shadow border-bottom border-grey text-normal">
                    [{isys type="lang" ident="LC__CMDB__CATG__LOCATION_UNPOSITIONED"}]
                    <span id="unassigned_objects_count" class="text-grey fr">([{if is_array($objects)}][{count($objects)}][{else}]0[{/if}])</span></h3>

				<div class="list">
					<!-- To be filled by JS -->
				</div>

				<a id="new" class="btn" href="[{$category_link}]">
					[{isys type="lang" ident="LC__CMDB__CATS__RACK__MANAGE_OBJECTS"}]
				</a>
			</div>

			<div id="edit_height_unit" class="mb10 box-white text-black hide">
				<h3 class="p5 gradient text-shadow border-bottom border-grey text-normal">
					<img src="[{$dir_images}]icons/close-circle.png" class="box-closer" alt="x" />
					[{isys type="lang" ident="LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS"}]
				</h3>

				<div class="m5">
					<p class="mb5">...</p>

					[{isys name="C__CATS__ENCLOSURE__HE_UNIT" type="f_dialog"}]

					<button type="button" id="object_he_units_submit" class="cb btn btn-block mt5">
						<img src="[{$dir_images}]icons/silk/disk.png" class="mr5" /><span>[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__SAVE"}]</span>
					</button>
				</div>
			</div>

			<div id="objectAssignmentBox" class="mb10 box-white text-black hide">
				<h3 class="p5 gradient text-shadow border-bottom border-grey text-normal" id="object-positioning">
					<img src="[{$dir_images}]icons/close-circle.png" class="box-closer" alt="x" />
					<span>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_POSITIONING"}]</span>
				</h3>

				<div class="m5">
					<select id="rack_option" class="input input-block mb10"></select>

					<select id="rack_insertion" class="input input-block mb10"></select>

					<select id="rack_position" class="input input-block mb10"></select>

					<select id="rack_segment_slot" class="input input-block mb10"></select>

					<button type="button" class="btn" id="rack_submit">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_POSITIONING"}]</button>
				</div>
			</div>

			<div id="segment_slot_option" class="box-white text-black hide">
				<h3 class="p5 gradient text-shadow border-bottom border-grey text-normal">
					<img src="[{$dir_images}]icons/close-circle.png" class="box-closer" alt="x" />
					[{isys type="lang" ident="LC__CMDB__CATS__RACK__SEGMENT_SLOT"}]
				</h3>

				<div class="m5">
					[{isys name="C__CATS__ENCLOSURE__SEGMENT_TEMPLATES" type="f_dialog"}]

					<strong class="mt5 text-grey">[{isys type="lang" ident="LC__UNIVERSAL__PREVIEW"}]</strong>
					<div id="segment_slot_option_preview" class="p5 box-grey">
						<!-- To be filled by JS -->
					</div>

					<button type="button" id="segment_slot_option_submit" class="mt5 btn btn-block">
						<img src="[{$dir_images}]icons/silk/application_split.png" class="mr5" />
						<span>[{isys type="lang" ident="LC__CMDB__CATS__RACK__SEGMENT_SLOT"}]</span>
					</button>
				</div>
			</div>
		</div>

		<br class="clear" />
	</div>

	<script type="text/javascript">
		var $rackOptions = $('rack_option'),
            $rackFront = $('rack_front'),
            $rackRear = $('rack_rear'),
            rackFront,
            rackRear,
			rackAssignment,
            statistics_loaded = false;

        idoit.Require.require(['rack', 'rackAssignment', 'chassis'], function () {
            idoit.Translate.set('LC__CMDB__CATS__RACK__REASSIGN_OBJECT', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__REASSIGN_OBJECT"}]');
            idoit.Translate.set('LC__CMDB__CATS__RACK__REMOVE_OBJECT', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__REMOVE_OBJECT"}]');
            idoit.Translate.set('LC__UNIVERSAL__TITLE_LINK', '[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LINK"}]');
            idoit.Translate.set('LC__CMDB__CATS__RACK__CONFIGURE_SLOT', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__CONFIGURE_SLOT"}]');
            idoit.Translate.set('LC__CMDB__CATS__RACK__SEGMENT_SLOT', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__SEGMENT_SLOT"}]');
            idoit.Translate.set('LC__CMDB__CATS__RACK__RESET_SLOT', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__RESET_SLOT"}]');
            idoit.Translate.set('LC__CMDB__CATG__RACKUNITS_ABBR', '[{isys type="lang" ident="LC__CMDB__CATG__RACKUNITS_ABBR"}]');
            idoit.Translate.set('LC__CMDB__CATG__FORMFACTOR_TYPE', '[{isys type="lang" ident="LC__CMDB__CATG__FORMFACTOR_TYPE"}]');
            idoit.Translate.set('LC_UNIVERSAL__REMOVE_LOCATION', '[{isys type="lang" ident="LC_UNIVERSAL__REMOVE_LOCATION" p_bHtmlEncode=false}]');
            idoit.Translate.set('LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS" p_bHtmlEncode=false}]');
            idoit.Translate.set('LC__CMDB__CATS__RACK__REMOVE_OBJECT_LOCATION', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__REMOVE_OBJECT_LOCATION" p_bHtmlEncode=false}]');
            idoit.Translate.set('LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS__DESCRIPTION', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS__DESCRIPTION"}]');
            idoit.Translate.set('LC__CMDB__CATS__RACK__SLOT_HAS_NO_OPTIONS', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__SLOT_HAS_NO_OPTIONS"}]');

            [{if $new_entry}]
            if ($$('input[name="SM2__C__CATS__ENCLOSURE__UNIT_SORTING[p_strSelectedID]"]')[0]) $$('input[name="SM2__C__CATS__ENCLOSURE__UNIT_SORTING[p_strSelectedID]"]')[0].setValue('');
            if ($$('input[name="SM2__C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT[p_strSelectedID]"]')[0]) $$('input[name="SM2__C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT[p_strSelectedID]"]')[0].setValue('-1');
            if ($$('input[name="SM2__C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR[p_strSelectedID]"]')[0]) $$('input[name="SM2__C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR[p_strSelectedID]"]')[0].setValue('-1');
            [{/if}]

            // Prepare the assignment options.
            new Ajax.Request('?ajax=1&call=rack&func=get_rack_options',
                {
                    parameters:{
                        'obj_id':parseInt([{$object_id}])
                    },
                    method:"post",
                    onSuccess:function (xhr) {
                        var json = xhr.responseJSON, i;

                        if (Object.isArray(json))
                        {
                            for (i in json)
                            {
                                if (!json.hasOwnProperty(i))
                                {
                                    continue;
                                }

                                $rackOptions.insert(new Element('option', {value: json[i].id}).update(json[i].title));
                            }
                        }
                    }
                });

            (function(){
                'use strict';

                var objects                   = '[{$objects|json_encode|escape:"javascript"}]'.evalJSON(),
                    hasEditRight              = !!parseInt('[{$has_edit_right|intval}]'),
                    rack_slots                = parseInt('[{$rack_slots}]'),
                    $contentWrapper           = $('contentWrapper'),
                    $rackView                 = $('rackview'),
                    $rackStats                = $('rack_stats'),
                    $sideBox                  = $('side_box'),
                    $unassignedObjects        = $('unassigned_objects'),
                    $unassignedObjectsCounter = $('unassigned_objects_count'),
                    $verticalSlotsFront       = $('C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT'),
                    $verticalSlotsRear        = $('C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR'),
                    $slotUnitSorting          = $('C__CATS__ENCLOSURE__UNIT_SORTING');

                var createQuickinfo = function ($element, obj) {
                    new Tip($element, '',
                        {
                            ajax:      {url: '?ajax=1&call=quick_info&objID=' + obj},
                            delay:     [{isys_usersettings::get('gui.quickinfo.delay', 0.5)}],
                            className: 'objectinfo'
                        });
                };

                // Function that gets called, when an object from the "not-assigned" area is clicked.
                var objectReassignment = function (ev) {
                    var $objectElement = ev.findElement('[data-object-id]'),
                        slotNumber = $('rackOptionsPopup').readAttribute('data-slot-number');

                    // If this action is called from the option-drop down, we remove it now.
                    if ($('rackOptionsPopup')) {
                        $('rackOptionsPopup').remove();
                    }

                    // data-slotnum
                    rackAssignment.setLastSlotNumber(slotNumber).detachObjectFromRack($objectElement.readAttribute('data-object-id'), function () {
                        rackAssignment.selectObjectForAssignment(ev)
                    });
                };

                // Function for removing an object from the rack and making it selectable in the "unassigned" field.
                var removeObjectAssignment = function() {
                    if ($('rackOptionsPopup')) {
                        $('rackOptionsPopup').remove();
                    }

                    rackAssignment.detachObjectFromRack(this.readAttribute('data-object-id'), function () {
                        objects = rackAssignment.getObjects();
                    });
                };

                var detachSegmentObject = function (ev) {
                    var $li = ev.findElement('li');

                    // If this action is called from the option-drop down, we remove it now.
                    if ($('rackOptionsPopup')) {
                        $('rackOptionsPopup').remove();
                    }

                    if (confirm(('[{isys type="lang" ident="LC__CMDB__CATS__ENCLOSURE__CONFIRM_SEGMENT_DETACHMENT" p_bHtmlEncode=false}]'.replace(':objectTitle', $li.readAttribute('data-object-title')))))
                    {
                        rackAssignment.detachSlotSegmentation($li.readAttribute('data-object-id'));
                    }
                };

                /*
                 * We call this logic here, because the functions above are used for observer actions
                 * inside the class (only if "objectReassign : true").
                 */
                rackFront = new Rack($rackFront, {
                    edit_right:             hasEditRight,
                    view:                   'front',
                    slots:                  rack_slots,
                    slot_sort:              '[{$rack_slot_sorting}]',
                    object_link:            true,
                    objects:                objects,
                    objectReassign:         true,
                    objectReassignCallback: objectReassignment,
                    object_remove:          true,
                    objectRemoveCallback:   removeObjectAssignment,
                    slot_segment:           true,
                    slotSegmentCallback:    function (ev) {
                        var $li      = ev.findElement('li'),
                            position = $li.readAttribute('data-slot-number');

                        // If this action is called from the option-drop down, we remove it now.
                        if ($('rackOptionsPopup')) {
                            $('rackOptionsPopup').remove();
                        }

                        this.select(true, position, position);
                        rackAssignment.prepareSlotSegmentation(position, '[{$smarty.const.C__INSERTION__FRONT}]');
                    },
                    slotDetach:              true,
                    slotDetachCallback:      detachSegmentObject,
                    verticalSlots:          parseInt('[{$vertical_slots_front}]'),
                    verticalSlotsMirrored:  0,
                    verticalSlotSorting:    parseInt('[{isys_tenantsettings::get('cmdb.rack.vertical-slot-sorting', 1)}]'),
                    quickinfoCallback:      createQuickinfo,
                    OPTION_VERTICAL:        '[{$smarty.const.C__RACK_INSERTION__VERTICAL}]',
                    OPTION_HORIZONTAL:      '[{$smarty.const.C__RACK_INSERTION__HORIZONTAL}]',
                    INSERTION_FRONT:        '[{$smarty.const.C__INSERTION__FRONT}]',
                    INSERTION_REAR:         '[{$smarty.const.C__INSERTION__REAR}]',
                    INSERTION_BOTH:         '[{$smarty.const.C__INSERTION__BOTH}]'
                });

                rackRear = new Rack($rackRear, {
                    edit_right:             hasEditRight,
                    view:                   'rear',
                    slots:                  rack_slots,
                    slot_sort:              '[{$rack_slot_sorting}]',
                    object_link:            true,
                    objects:                objects,
                    objectReassign:         true,
                    objectReassignCallback: objectReassignment,
                    object_remove:          true,
                    objectRemoveCallback:   removeObjectAssignment,
                    slot_segment:           true,
                    slotSegmentCallback:    function (ev) {
                        var $li      = ev.findElement('li'),
                            position = $li.readAttribute('data-slot-number');

                        // If this action is called from the option-drop down, we remove it now.
                        if ($('rackOptionsPopup')) {
                            $('rackOptionsPopup').remove();
                        }

                        this.select(true, position, position);
                        rackAssignment.prepareSlotSegmentation(position, '[{$smarty.const.C__INSERTION__REAR}]');
                    },
                    slotDetach:              true,
                    slotDetachCallback:      detachSegmentObject,
                    verticalSlots:          parseInt('[{$vertical_slots_rear}]'),
                    verticalSlotsMirrored:  parseInt('[{isys_tenantsettings::get('cmdb.rack.vertical-slot-rear-mirrored', 1)}]'),
                    verticalSlotSorting:    parseInt('[{isys_tenantsettings::get('cmdb.rack.vertical-slot-sorting', 1)}]'),
                    quickinfoCallback:      createQuickinfo,
                    OPTION_VERTICAL:        '[{$smarty.const.C__RACK_INSERTION__VERTICAL}]',
                    OPTION_HORIZONTAL:      '[{$smarty.const.C__RACK_INSERTION__HORIZONTAL}]',
                    INSERTION_FRONT:        '[{$smarty.const.C__INSERTION__FRONT}]',
                    INSERTION_REAR:         '[{$smarty.const.C__INSERTION__REAR}]',
                    INSERTION_BOTH:         '[{$smarty.const.C__INSERTION__BOTH}]'
                });

                rackAssignment = new RackAssignment(objects, {
                    rackFront:                 rackFront,
                    rackRear:                  rackRear,
                    $unassignedObjects:        $unassignedObjects,
                    $unassignedObjectsCounter: $unassignedObjectsCounter,
                    $assignmentBox:            $('objectAssignmentBox'),
                    $assignmentOption:         $('rack_option'),
                    $assignmentInsertion:      $('rack_insertion'),
                    $assignmentPosition:       $('rack_position'),
                    $assignmentSegmentSlot:    $('rack_segment_slot'),
                    $assignmentSubmitButton:   $('rack_submit'),
                    $heightChangeBox:          $('edit_height_unit'),
                    $heightChangeSelect:       $('C__CATS__ENCLOSURE__HE_UNIT'),
                    $heightChangeButton:       $('object_he_units_submit'),
                    $segmentBox:               $('segment_slot_option'),
                    $segmentObjectSelect:      $('C__CATS__ENCLOSURE__SEGMENT_TEMPLATES'),
                    $segmentPreview:           $('segment_slot_option_preview'),
                    $segmentButton:            $('segment_slot_option_submit'),
                    rackObjectId:              parseInt('[{$object_id}]'),
                    allowedToEdit:             hasEditRight,
                    quickinfoCallback:         createQuickinfo,
                    OPTION_VERTICAL:           '[{$smarty.const.C__RACK_INSERTION__VERTICAL}]',
                    OPTION_HORIZONTAL:         '[{$smarty.const.C__RACK_INSERTION__HORIZONTAL}]',
                    INSERTION_FRONT:           '[{$smarty.const.C__INSERTION__FRONT}]',
                    INSERTION_REAR:            '[{$smarty.const.C__INSERTION__REAR}]',
                    INSERTION_BOTH:            '[{$smarty.const.C__INSERTION__BOTH}]'
                });

                [{if $smarty.get.catsID == $smarty.const.C__CATS__ENCLOSURE}]
                $contentWrapper.on('scroll', function () {
                    var top = $contentWrapper.cumulativeScrollOffset().top,
                        limit = 140;

                    if (infiniteScroll()) {
                        $sideBox.setStyle({top: 'auto'});
                    } else {
                        if ($rackStats.down('div').visible()) {
                            limit += $rackStats.down('div').getHeight() + 10;
                        }

                        $sideBox.setStyle({top: (top > limit) ? (top - limit) + 'px' : 0});
                    }
                });

                function infiniteScroll () {
                    if ($rackView.getWidth() < 1081) {
                        return true;
                    }

                    if ($contentWrapper.getHeight() <= $sideBox.getHeight()) {
                        return true;
                    }
                }
                [{/if}]

                $('rack_detail_view_button').on('click', function () {
                    $rackFront.toggleClassName('expanded');
                    $rackRear.toggleClassName('expanded');

                    // Trigger the row height change.
                    rackFront.updateRowSizes($rackFront.hasClassName('expanded'));
                    rackRear.updateRowSizes($rackRear.hasClassName('expanded'));
                });

                if ($verticalSlotsFront && $verticalSlotsRear && $slotUnitSorting) {
                    $verticalSlotsFront.on('change', function () {
                        rackFront.updateVerticalSlots($verticalSlotsFront.getValue());
                        rackRear.updateVerticalSlots($verticalSlotsRear.getValue());
                    });

                    $verticalSlotsRear.on('change', function () {
                        rackFront.updateVerticalSlots($verticalSlotsFront.getValue());
                        rackRear.updateVerticalSlots($verticalSlotsRear.getValue());
                    });

                    $slotUnitSorting.on('change', function () {
                        rackFront.set_slot_sorting($slotUnitSorting.getValue())
                            .create_slots()
                            .createVerticalSlots($verticalSlotsFront.getValue());
                        rackAssignment.options.rackFront.set_slot_sorting($slotUnitSorting.getValue());

                        rackRear.set_slot_sorting($slotUnitSorting.getValue())
                            .create_slots()
                            .createVerticalSlots($verticalSlotsRear.getValue());
                        rackAssignment.options.rackRear.set_slot_sorting($slotUnitSorting.getValue());

                        rackAssignment.resetAssignment();
                    });
                }

                $rackView.on('click', '.box-closer', function (ev) {
                    ev.findElement('div').addClassName('hide');

                    rackAssignment.setLastSlotNumber(0).resetAssignment();
                });

                $rackStats.down('h3').on('click', function () {
                    var $container = $rackStats.down('div').toggleClassName('hide');

                    if ($container.hasClassName('hide')) {
                        $rackStats
                            .down('h3').removeClassName('border-bottom')
                            .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_right.png');
                    } else {
                        $rackStats
                            .down('h3').addClassName('border-bottom')
                            .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
                    }

                    if (!statistics_loaded) {
                        $container
                            .update(new Element('img', {src: window.dir_images + 'ajax-loading.gif', className: 'vam mr5'}))
                            .insert(new Element('span', {className: 'vam'}).update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

                        statistics_loaded = true;

                        new Ajax.Request('?ajax=1&call=statistic&func=get_rack_statistics', {
                            parameters: {
                                'obj_id': parseInt('[{$object_id}]')
                            },
                            method:     "post",
                            onSuccess:  function (xhr) {
                                $container.update(xhr.responseText);
                            }
                        });
                    }
                });

                rackAssignment.updateUnassignedObjectsList();
            })();
        });
	</script>
</div>
