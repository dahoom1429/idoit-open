<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__LOCATION_PARENT' ident="LC__CMDB__CATG__LOCATION_PARENT"}]</td>
		<td class="value">
			[{isys
				name="C__CATG__LOCATION_PARENT"
				id="C__CATG__LOCATION_PARENT"
				type="f_popup"
				p_strPopupType="browser_location"}]

			[{if isys_glob_is_edit_mode()}]
			<br class="cb" />
			<button type="button" class="btn mt5 ml20 text-normal" id="inherit-parent-geo-coordinates">
				<img src="[{$dir_images}]icons/silk/arrow_down.png" />
				<span>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION__INHERIT_PARENT_GEO_COORDINATES"}]</span>
			</button>
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='LC__CMDB__CATG__LOCATION_LATITUDE' ident="LC__CMDB__CATG__LOCATION_LATITUDE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__LOCATION_LATITUDE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='LC__CMDB__CATG__LOCATION_LONGITUDE' ident="LC__CMDB__CATG__LOCATION_LONGITUDE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__LOCATION_LONGITUDE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='LC__CMDB__CATG__LOCATION__SNMP_SYSLOCATION' ident="LC__CMDB__CATG__LOCATION__SNMP_SYSLOCATION"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__LOCATION_SNMP_SYSLOCATION"}]</td>
	</tr>
	[{if $lat && $lng}]
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_OPEN_MAP"}]</td>
		<td>
			<!-- Google Maps -->
			<a class="btn ml20" target="_blank" href="https://www.google.de/maps/?q=[{$lat}],[{$lng}]&ll=[{$lat}],[{$lng}]&z=17">
				<img src="[{$dir_images}]icons/googlemaps.png" class="mr5" title="Google Maps" /><span>Google Maps</span>
			</a>

			<!-- Bing Maps -->
			<a class="btn ml5" target="_blank" href="https://www.bing.com/maps?cp=[{$lat}]~[{$lng}]&lvl=17">
				<img src="[{$dir_images}]icons/bing.png" class="mr5" title="Bing maps" /><span>Bing maps</span>
			</a>

			<!-- OpenStreetMap -->
			<a class="btn ml5" target="_blank" href="https://www.openstreetmap.org/?mlat=[{$lat}]&mlon=[{$lng}]#map=17/[{$lat}]/[{$lng}]">
				<img src="[{$dir_images}]icons/openstreetmap.png" class="mr5" title="OpenStreetMap" /><span>OpenStreetMap</span>
			</a>
		</td>
	</tr>
	[{/if}]
	<tr class="rack_dummy_class [{if !$parent_is_rack}]hide[{/if}]">
		<td class="key">[{isys type="f_label" name="C__CATG__LOCATION_OPTION" ident="LC__CMDB__CATG__LOCATION_OPTION"}]</td>
		<td class="value">[{isys name="C__CATG__LOCATION_OPTION" type="f_dialog"}]</td>
	</tr>
	<tr class="rack_dummy_class [{if !$parent_is_rack}]hide[{/if}]">
		<td class="key">[{isys type="f_label" name="C__CATG__LOCATION_INSERTION" ident="LC__CMDB__CATG__LOCATION_FRONTSIDE"}]</td>
		<td class="value">[{isys name="C__CATG__LOCATION_INSERTION" type="f_dialog"}]</td>
	</tr>
	<tr class="rack_dummy_class [{if !$parent_is_rack}]hide[{/if}]">
		<td class="key">[{isys type="f_label" name="C__CATG__LOCATION_POS" ident="LC__CMDB__CATG__LOCATION_POS"}]</td>
		<td class="value">[{isys name="C__CATG__LOCATION_POS" type="f_dialog" p_bSort=false}]</td>
	</tr>
	<tr class="segment_dummy_class [{if !$parent_is_segment}]hide[{/if}]">
		<td class="key">[{isys type="f_label" name="C__CATG__LOCATION_SLOT" ident="LC__CMDB__CATG__LOCATION_SLOT"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__LOCATION_SLOT" p_strClass="input"}]</td>
	</tr>
	<tr class="segment_dummy_class [{if !$parent_is_segment}]hide[{/if}]">
		<td></td>
		<td>
			<p class="ml20 p5 box-blue">
				<img src="[{$dir_images}]icons/silk/information.png" class="mr5 vam" />
				<span class="vam">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_LOCATION_IN_RACK"}] "<span id="rackObjectInfo">[{$rackQuickinfo}]</span>"</span>
			</p>
		</td>
	</tr>
</table>

<script type="text/javascript">
    (function () {
        'use strict';

        var $locationParentHidden        = $('C__CATG__LOCATION_PARENT__HIDDEN'),
            $rackOption                  = $('C__CATG__LOCATION_OPTION'),
            $rackInsertion               = $('C__CATG__LOCATION_INSERTION'),
            $rackPosition                = $('C__CATG__LOCATION_POS'),
            $rackSegmentSlot             = $('C__CATG__LOCATION_SLOT__selected_box'),
            $inheritParentGEOCoordinates = $('inherit-parent-geo-coordinates'),
            $rackRows                    = $$('.rack_dummy_class'),
            $segmentRows                 = $$('.segment_dummy_class'),
            $rackObjectInfo              = $('rackObjectInfo'),
            objectTypeAllowedInRack      = parseInt('[{$objectTypeAllowedInRack}]'),
            parentLocationID             = parseInt('[{$parentObjectId}]'),
            objectID                     = parseInt('[{$objectId}]'),
            locationParentChange         = function (ev) {
                if (!objectTypeAllowedInRack) {
                    return false;
                }

                parentLocationID = $locationParentHidden.getValue();

                $rackOption.update().disable();
                $rackInsertion.update().disable();
                $rackPosition.update().disable();
                $rackSegmentSlot.update();
                $rackSegmentSlot.fire('chosen:updated');
                $segmentRows.invoke('addClassName', 'hide');

                new Ajax.Request('?ajax=1&call=rack&func=get_immediate_parent_rack', {
                    parameters: {
                        'obj_id': parentLocationID
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON;

                        if (json.success) {
                            if (json.data > 0) {
                                parentLocationID = json.data;
                            }

                            getRackOptions();
                        } else {
                            idoit.Notify.error(json.message || xhr.responseText)
                        }
                    }
                });
            },
            getRackOptions               = function () {
                new Ajax.Request('?ajax=1&call=rack&func=get_rack_options', {
                    parameters: {
                        'obj_id': parentLocationID
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON;

                        if (!json) {
                            $rackRows.invoke('addClassName', 'hide');
                        } else {
                            $rackRows.invoke('removeClassName', 'hide');

                            $rackOption.enable();

                            $rackOption.insert(new Element('option', {value: -1}).update('[{isys_tenantsettings::get("gui.empty_value", "-")}]'));

                            for (var i in json) {
                                if (!json.hasOwnProperty(i)) {
                                    continue;
                                }

                                $rackOption.insert(new Element('option', {value: json[i].id}).update(json[i].title));
                            }

                            rackOptionChange();
                        }
                    }
                });
            },
            rackOptionChange             = function () {
                $rackInsertion.update().disable();
                $rackPosition.update().disable();
                $rackSegmentSlot.update();
                $rackSegmentSlot.fire('chosen:updated');
                $segmentRows.invoke('addClassName', 'hide');

                if ($rackOption.getValue() < 0) {
                    return;
                }

                new Ajax.Request('?ajax=1&call=rack&func=get_rack_insertions', {
                    parameters: {
                        'obj_id': objectID,
                        'option': $rackOption.getValue()
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON, i;

                        $rackInsertion.enable();

                        for (i in json) {
                            if (!json.hasOwnProperty(i)) {
                                continue;
                            }

                            $rackInsertion.insert(new Element('option', {value: json[i].id}).update(json[i].title))
                        }

                        rackInsertionChange();
                    }
                });
            },
            rackInsertionChange          = function () {
                var oldPositionValue = null;

                if ($rackPosition.down(':selected')) {
                    oldPositionValue = parseInt($rackPosition.down(':selected').readAttribute('value'));
                }

                $rackPosition.update().disable();
                $rackSegmentSlot.update();
                $rackSegmentSlot.fire('chosen:updated');
                $segmentRows.invoke('addClassName', 'hide');

                new Ajax.Request('?ajax=1&call=rack&func=get_free_slots_for_location', {
                    parameters: {
                        'rack_obj_id':   parentLocationID,
                        'assign_obj_id': objectID,
                        'option':        $rackOption.getValue(),
                        'insertion':     $rackInsertion.getValue()
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON,
                            optionParameters,
                            positionValue,
                            i;

                        $rackPosition.enable();

                        for (i in json) {
                            if (!json.hasOwnProperty(i)) {
                                continue;
                            }

                            positionValue = parseInt(i.split(';')[0]);
                            optionParameters = {value: positionValue};

                            if (positionValue === oldPositionValue) {
                                optionParameters.selected = true;
                            }

                            $rackPosition.insert(new Element('option', optionParameters).update(json[i]));
                        }

                        rackPositionChange();
                    }
                });
            },
            rackPositionChange           = function () {
                var $positionSelection = $rackPosition.down(':selected'),
                    $tmp               = $('C__CATG__LOCATION_SLOT__selected_box_chosen');

                $rackSegmentSlot.update();
                $rackSegmentSlot.fire('chosen:updated');
                $segmentRows.invoke('addClassName', 'hide');

                if ($tmp) {
                    $tmp.setStyle({width: '290px'}).down('input');
                }

                if ($positionSelection) {
                    if ($positionSelection.innerHTML.indexOf('(') < 0) {
                        return;
                    }
                }

                new Ajax.Request('?ajax=1&call=rack&func=get_segments', {
                    parameters: {
                        'rack_obj_id': parentLocationID,
                        'option':      $rackOption.getValue(),
                        'insertion':   $rackInsertion.getValue(),
                        'position':    $rackPosition.getValue()
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON, i;

                        if (json.success) {
                            if (Object.isArray(json.data.slots) && json.data.slots.length === 0) {
                                return;
                            }

                            for (i in json.data.slots) {
                                if (!json.data.slots.hasOwnProperty(i)) {
                                    continue;
                                }

                                $rackSegmentSlot.insert(new Element('option', {value: i}).update(json.data.slots[i]));
                            }

                            $rackSegmentSlot.fire('chosen:updated');
                            $segmentRows.invoke('removeClassName', 'hide');
                            $rackObjectInfo.update(json.data.rackQuickInfo);
                        } else {
                            idoit.Notify.error(json.message || xhr.responseText);
                        }
                    }
                });
            };

        if ($inheritParentGEOCoordinates) {
            $inheritParentGEOCoordinates.on('click', function () {
                $inheritParentGEOCoordinates.disable()
                    .down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif')
                    .next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

                new Ajax.Request('?ajax=1&call=location&func=get_geo_coordinates_from_object', {
                    parameters: {'[{$smarty.const.C__CMDB__GET__OBJECT}]': $F('C__CATG__LOCATION_PARENT__HIDDEN')},
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON;

                        if (json.success) {
                            if (json.data.hasGeoCoordinates) {
                                $('C__CATG__LOCATION_LATITUDE').setValue(json.data.latitude).highlight();
                                $('C__CATG__LOCATION_LONGITUDE').setValue(json.data.longitude).highlight();
                            } else {
                                idoit.Notify.info('[{isys type="lang" ident="LC__CMDB__CATG__LOCATION__INHERIT_PARENT_GEO_COORDINATES_FAIL"}]', {life: 5});
                            }

                            $inheritParentGEOCoordinates.enable()
                                .down('img').writeAttribute('src', window.dir_images + 'icons/silk/arrow_down.png')
                                .next('span').update('[{isys type="lang" ident="LC__CMDB__CATG__LOCATION__INHERIT_PARENT_GEO_COORDINATES"}]');
                        } else {
                            idoit.Notify.error(json.message || xhr.responseText, {sticky: true});
                        }
                    }
                });
            });
        }

        if ($locationParentHidden) {
            $locationParentHidden.on('locationObject:selected', locationParentChange);
        }

        if ($rackOption) {
            $rackOption.on('change', rackOptionChange);
        }

        if ($rackInsertion) {
            $rackInsertion.on('change', rackInsertionChange);
        }

        if ($rackPosition) {
            $rackPosition.on('change', rackPositionChange);
        }
    }());
</script>
