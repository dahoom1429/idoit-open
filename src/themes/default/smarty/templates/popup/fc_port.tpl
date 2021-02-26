<style type="text/css">
    #fc_port_list {
        height: 350px;
        overflow: auto;
        border-bottom: 1px solid #888;
        margin-bottom: 10px;
    }

    #fc_port_list label {
        display: block;
        padding: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        background: #eee;
    }

    #fc_port_list label:hover {
        background: #e8e8e8;
        color: #c00;
    }
</style>

<div id="fcport_browser">
    <h3 class="popup-header">
        <img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
        <span>[{isys type="lang" ident="LC__BROWSER__TITLE__FC_PORT"}]</span>
    </h3>

    <div class="popup-content">
        <div id="fc_port_list"></div>

        <label class="ml10">
            [{isys type="lang" ident="LC_FC_PORT_POPUP__PRIMARY_PORTS"}]

            <select id="fc_port_primary" name="fc_port_primary" class="input input-small ml10"></select>
        </label>
    </div>

    <div class="popup-footer">
        <button type="button" id="fcport_browser_save" class="btn mr5">
            <img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_SAVE"}]</span>
        </button>
        <button type="button" class="btn popup-closer">
            <img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
        </button>
    </div>
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        var $container = $('fcport_browser'),
            $fc_port_list = $('fc_port_list'),
            $fc_port_primary = $('fc_port_primary'),
            fc_ports = '[{$fc_ports|escape:"quotes"}]'.evalJSON(),
            selection = '[{$fc_ports_selection|escape:"quotes"}]'.evalJSON(),
            i;

        $container.select('.popup-closer').invoke('on', 'click', function () {
            popup_close();
        });

        // Observe the checkboxes.
        $container.on('change', 'input', function (ev, $el) {
            var id = parseInt($el.readAttribute('data-id'));

            if ($el.checked) {
                selection.push(id);
            } else {
                selection = selection.without(id)
            }

            refresh_dropdown();
        });

        // Move the selection to the form-fields.
        $('fcport_browser_save').on('click', function () {
            var selection_str = [],
                $returnview = $('[{$viewField}]'),
                $returnhidden = $('[{$hiddenField}]'),
                $returnprimary = $('[{$primField}]');

            var initialPortSelection = $returnhidden.getValue();
            var initialPrimaryPort = $returnprimary.getValue();

            if ($returnview && $returnhidden && $returnprimary) {
                if ($fc_port_primary.getValue() >= 0) {
                    for (i in selection) {
                        if (selection.hasOwnProperty(i)) {
                            selection_str.push(fc_ports[selection[i]].title);
                        }
                    }

                    $returnview.setValue(selection_str.join(', '));
                    $returnhidden.setValue(selection.join(','));
                    $returnprimary.setValue($fc_port_primary.getValue());
                } else {
                    $returnview.setValue('[{isys type="lang" ident="LC_UNIVERSAL__NONE_SELECTED" p_bHtmlEncode=false}]');
                    $returnhidden.setValue('');
                    $returnprimary.setValue('');
                }
            }

            // Check for changes before updating the view field
            if (initialPortSelection != $returnhidden.getValue() || initialPrimaryPort != $returnprimary.getValue() ) {
                [{if $callback_accept}]
                    [{$callback_accept}]
                [{/if}]
            }

            popup_close();
        });

        // Create all FC port items.
        if (typeof fc_ports == 'object' && Object.keys(fc_ports).length > 0) {
            for (i in fc_ports) {
                if (fc_ports.hasOwnProperty(i)) {
                    $fc_port_list
                        .insert(new Element('label', {className: 'm5'})
                            .update(new Element('input', {className:'vam mr5', type:'checkbox', name:'fc' + i, 'data-id': i, checked: (selection.in_array(parseInt(i)))}))
                            .insert(new Element('span', {className:'vam'}).update(fc_ports[i].title)));
                }
            }
        } else {
            $fc_port_list.update(new Element('p', {className:'p5 m5 box-red'}).update('[{isys type="lang" ident="LC_FC_PORT_POPUP__NO_PORTS"}]'));
        }

        // Handle preselection.
        if (selection.length > 0) {
            refresh_dropdown();
        }

        // Refresh the drop down by the given selection and select the primary path.
        function refresh_dropdown () {
            var i, m;

            $fc_port_primary.update(new Element('option', {value:-1}).update('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));

            if (selection.length > 0) {
                for (i = 0, m = selection.length; i < m; i++) {
                    $fc_port_primary.insert(new Element('option', {value: selection[i]}).update(fc_ports[selection[i]].title));
                }
            }

            // Preselect the primary path.
            if (!'[{$primary}]'.blank()) {
                $fc_port_primary.setValue('[{$primary}]');
            }

            $fc_port_primary.highlight({endcolor:'#ffffff', restorecolor:'#ffffff'});
        }
    })();
</script>