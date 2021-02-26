<div id="popup-san-zoning">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
		<span>[{isys type="lang" ident="LC__BROWSER__TITLE__SAN_ZONING"}]</span>
	</h3>

	<div class="popup-content p5">
		<div style="height: 270px; overflow:auto">[{$browser}]</div>

		<p>[{isys type="lang" ident="LC_FC_PORT_SAN_ZONING_POPUP__CHOSEN_ZONES"}]: <strong id="selectedFullText">[{$selFull|default:$selNoSelection}]</strong></p>
	</div>

	<div class="popup-footer">
		<button type="button" id="popup-san-zoning-save" class="btn mr5">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_SAVE"}]</span>
		</button>
		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script language="JavaScript" type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('popup-san-zoning'),
			$save_button = $('popup-san-zoning-save'),
			$extra_field = $('[{$extraField}]'),
            g_selected_zones = [],
            g_fc_ports_selected = [],
            g_wwn_selected = [],
            g_zones = [];

        window.move_selection_to_parent = function () {
            var peText = $('[{$name}]'),
                    peHidden = $('[{$name}]__HIDDEN'),
                    peHiddenFCPorts = $('[{$name}]__SELECTED_FCPORT'),
                    peHiddenWWN = $('[{$name}]__SELECTED_WWN');

            if (peText && peHidden) {
                peText.value = $('selectedFullText').innerHTML;
                peHidden.value = Object.toJSON(g_selected_zones);
                peHiddenFCPorts.value = Object.toJSON(g_fc_ports_selected);
                peHiddenWWN.value = Object.toJSON(g_wwn_selected);
            }

            popup_close();
        };

        // FOLLOWING THE DEVICE LIST AS ARRAY
        [{foreach from=$deviceList item=device_name key=device_id}]
        g_zones['[{$device_id}]'] = '[{$device_name|escape}]';
        [{/foreach}]

        window.refresh_selected = function () {
            var l_fc_elements = $$('input[name="fcport_selection[]"]'),
                l_wwn_elements = $$('input[name="wwn_selection[]"]'),
                l_text = [],
                l_devices = false,
                l_selected_zones = [];

            // Reset the selections.
            g_selected_zones = [];
            g_fc_ports_selected = [];
            g_wwn_selected = [];

            l_fc_elements.each(function(el, i) {
                if (el.checked) {
                    var zone_id = parseInt($('zone_' + i).value);

                    g_fc_ports_selected.push(parseInt(el.value));
                    g_selected_zones.push(zone_id);
                    l_text.push(g_zones[zone_id]);

                    l_selected_zones[i] = zone_id;
                    l_devices = true;
                }
            }.bind(this));

            l_wwn_elements.each(function(el, i) {
                if (el.checked) {
                    var l_check, zone_id = parseInt($('zone_' + i).value);

                    g_wwn_selected.push(parseInt(el.value));

                    // This is necessary, so that we only write a zone once to the selection.
                    l_check = ! l_selected_zones.in_array(zone_id);

                    if (l_check) {
                        g_selected_zones.push(zone_id);
                        l_text.push(g_zones[zone_id]);

                        l_selected_zones[i] = zone_id;
                    }

                    l_devices = true;
                }
            }.bind(this));

            if (! l_devices) {
                l_text.push('[{isys type="lang" ident="LC_UNIVERSAL__NONE_SELECTED"}]');
            }

            $('selectedFullText').update(l_text.join(', '));
        };

        window.disable_wwns = function () {
            $$('input[name="wwn_selection[]"]').each(function(el) {
                el.checked = false;
                el.disabled = true;
            });

            g_wwn_selected = [];
        };

		if ($extra_field && $extra_field.getValue().blank()) {
			window.disable_wwns();
		}

		$save_button.on('click', function () {
			window.move_selection_to_parent();
		});

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});

		window.refresh_selected();
    })();
</script>