Browser.portList = Class.create(Browser.objectList, {
    createRow: function (obj, index) {
        var values = Object.values(obj),
            tmpClassName,
            tmpContent,
            tr = new Element('tr', {className: 'data line' + (index % 2), id: this.table.id + '-' + index, 'data-objectid': values[0]});
        
        this.tableColumnsName.each(function (s, index) {
            if (s == '__checkbox__') {
                if (window.browserPreselection.exists(values[0])) {
                    tmpContent = this.removeButton(values, 'r');
                } else {
                    tmpContent = this.addButton(values, 'r');
                }
                
                tmpClassName = this.table.id + '-column-checkbox toolbar center';
            } else {
                tmpContent = values[index];
                tmpClassName = this.table.id + '-column-' + s;
            }
            
            // @see  ID-6907  Render cell values also as title attribute.
            tr.insert(new Element('td', {className: tmpClassName, title: tmpContent}).update(tmpContent));
        }.bind(this));
        
        if (Prototype.Browser.IE && tr.outerHTML) {
            return tr.outerHTML;
        } else {
            return tr;
        }
    },

    radioButton:function (values, checked) {
        // Is port already connected?
        var l_in_use = [{if $usageWarning}](values[2].length > 1 && !checked)[{else}]false[{/if}];

        return '<input type="radio" id="port-id-' + values[0] + '" name="portSelection" ' + (checked ? 'checked="checked" ' : ' ') + 'onclick="window.portSelection(' + values[0] + ', \'' + encodeURI(values[1]).replace(/'/g, '%27') + '\', '+ l_in_use +');" />';
    }
});

// Method for setting selected Port.
window.portSelection = function (pId, pName, pInUse) {
    window.browserPreselection.select(pId);
    
    // Show warning
    if (pInUse && !$('in_use_warning'))
    {
        // Create warning
        $('portList').insert(
            new Element('p', {className: 'box-red p10 m10', id: 'in_use_warning', style:'text-align: left;'})
                .update("[{isys type='lang' ident=$usageWarning}]")
        );
    }
    else if (!pInUse)
    {
        // Remove warning
        if ($('in_use_warning')) $('in_use_warning').remove();
    }
};

// Method for saving the selected objects to the hidden forms.
window.moveToParent = function (hiddenElement, viewElement) {
    var $view     = $(viewElement),
        $hidden   = $(hiddenElement),
        selection = window.browserPreselection.getSelection(),
        val       = '';
    
    if (selection.length === 0) {
        if ($view) {
            $view.setValue('[{isys type="lang" ident="LC__UNIVERSAL__CONNECTION_DETACHED" p_bHtmlEncode=0}]');
        }
    } else {
        if ($view) {
            $view.setValue('[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__SELECTED_OBJECTS" p_bHtmlEncode=0}]'.replace('{0}', selection.length));
        }
        val = JSON.stringify(selection[0]);
    }
    
    // Have to remove it because its currently buggy
    // new Ajax.Request('?ajax=1&call=browser_cable_connection&func=load_connector_name', {
    //     parameters: {'connector_id': selection[0]},
    //     method: "post",
    //     onSuccess: function (transport) {
    //         var json = transport.responseJSON;
    //         if (json.success) {
    //             if (json.data !== false) {
    //                 $view.setValue(json.data);
    //             }
    //         }
    //     }
    // });
    
    if ($hidden) {
        if (window.browserPreselection.isMultiselection()) {
            $hidden.setValue(JSON.stringify(selection));
        } else {
            $hidden.setValue(val);
        }
    }

    [{if $callback_accept}][{$callback_accept}][{/if}]
    
    /*
    // @todo  ID-5686  This needs to be re-done.
    if ($('[{$return_cable_name}]')) {
        $('[{$return_cable_name}]').setValue(' ### ');
    } else if ($('[{$returnElement2}]')) {
        $('[{$returnElement2}]').setValue(' ### ');
    }
    
    if ($(viewElement)) {
        if ($view && selection[0])
        {
            $view.setValue(idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING'));
            $view.setAttribute('data-last-value', $view.getValue());

            window.browserPreselection.getObjectMetaData(selection[0], function (data) {
                $view.setValue(data.isys_obj__title + ' > ' + '###');
            });
        }
    }
    */
    
    popup_close();
};

var multiselection = !! parseInt('[{$multiselection}]');

// Initialize preselection component.
window.browserPreselection = new Browser.preselection('objectPreselection', {
    secondElement:'portList',
    ajaxURL:'[{$ajax_url}]',
    $selectionCounter:'numObjects',
    multiselection:multiselection,
    latestLogElement: 'latestLog',
    instanceName:'browserPreselection',
    returnElement:'[{$return_element}]',
    afterFinish:function () {
        $('preselectionLoader').addClassName('hide');
        $('browser-content').show();
    },
    afterRemove: function () {
        try {
            window.browserSearch.updateTable();
        } catch (e) {
        
        }
        
        try {
            window.browserReport.updateTable();
        } catch (e) {
        
        }
        
        try {
            window.browserList.updateTable();
        } catch (e) {
        
        }
        
        try {
            this.secondList.updateTable();
        } catch (e) {
        
        }
    },
    secondListPreselectionCallback:'[{$preselectionCallback}]',
    secondList: new Browser.portList('portList', {
        listOptions:{
            colgroup:'<colgroup><col width="' + (multiselection ? 110 : 40) + '" /></colgroup>',
            search:false,
            filter:false,
            objectSelectionCallback: 'window.portSelection',
            firstSelection:false,
            secondSelection:true,
            secondSelectionExists:true,
            multiselection:multiselection,
            quickinfo: {
                active: '[{isys_usersettings::get("gui.quickinfo.active", 1)}]'.evalJSON(),
                delay:  '[{isys_usersettings::get("gui.quickinfo.delay", 0.5)}]'.evalJSON()
            }
        }
    })
});

// Append the preselection
window.browserParentPreselection = JSON.parse('[{$parentPreselection|json_encode|escape:"javascript"}]') || [];
window.browserPreselection.setSelection(JSON.parse('[{$preselection|json_encode|escape:"javascript"}]'));

if (Object.isArray(window.browserParentPreselection)) {
    window.browserParentPreselection = [];
}

// Pre-load the current list view.
if ($('object_type')) {
    $('object_type').simulate('change');
} else if ($('object_catfilter')) {
    $('object_catfilter').simulate('change');
}
