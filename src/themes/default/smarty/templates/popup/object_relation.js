/**
 *
 * @author  Leonard Fischer <lfischer@i-doit.org>
 */

Browser.portList = Class.create(Browser.objectList, {
    createRow:function (obj, index) {
        var values = Object.values(obj), tmpClassName, tmpContent;
        var tr = new Element('tr', {'class':'data line' + (index % 2), id:this.table.id + '-' + index}).writeAttribute('data-objectid', values[0]);

        this.tableColumnsName.each(function (s, index) {
            if (s == '__checkbox__') {

                // Handle preselection
                if (!browserPreselection.exists(values[0])) {
                    tmpContent = this.addButton(values, 'r');
                } else {
                    tmpContent = this.removeButton(values, 'r');
                }

                tmpClassName = this.table.id + '-column-checkbox toolbar center';
            } else {
                tmpContent = values[index];
                tmpClassName = this.table.id + '-column-' + s;
            }

            tr.insert(new Element('td', {
                className:tmpClassName
            }).update(tmpContent));

        }.bind(this));

        if (Prototype.Browser.IE && tr.outerHTML) return tr.outerHTML;
        else return tr;
    },

    addButton:function (values, view) {
        var obj_title = (values[1]).replace(/"/g, '&quot;').replace(/'/g, "\\'");

        if (this.options.multiselection) {
            var func_string = "browserPreselection.select(" + parseInt(values[0]) + ");" +
                "if(this.up()){this.up().update(window.browserList.removeButton([" + values[0] + ", '" + obj_title + "', '" + values[2] + "', '" + values[3] + "'], '" + view + "'));}";

            return '<button type="button" class="btn btn-small btn-block" onclick="' + func_string + '">' +
                   '<img src="' + window.dir_images + 'icons/silk/add.png" style="margin-right:3px" />' +
                   '<span>' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__ADD') + '</span>' +
                   '</button>';
        } else {
            return '<input type="radio" name="listSelection" onclick="browserPreselection.select(' + parseInt(values[0]) + ');" />';
        }
    },

    removeButton:function (values, view) {
        var obj_title = (values[1]).replace(/"/g, '&quot;').replace(/'/g, "\\'");

        if (this.options.multiselection) {
            var func_string = "browserPreselection.remove(" + values[0] + ");" +
                "if(this.up()){this.up().update(window.browserList.addButton([" + values[0] + ", '" + obj_title + "', '" + values[2] + "', '" + values[3] + "'], '" + view + "'));}";

            return '<button type="button" class="btn btn-small btn-block" onclick="' + func_string + '">' +
                   '<img src="' + window.dir_images + 'icons/silk/delete.png" style="margin-right:3px" />' +
                   '<span>' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__REMOVE') + '</span>' +
                   '</button>';
        } else {
            return '<input type="radio" name="listSelection" checked="checked" onclick="browserPreselection.select(' + parseInt(values[0]) + ');" />';
        }
    }
});

// Method for saving the selected objects to the hidden forms.
window.moveToParent = function (hiddenElement, viewElement) {
    var selection = window.browserPreselection.getSelection();
    
    if (window.browserPreselection.isMultiselection()) {
        $(hiddenElement).setValue(JSON.stringify(selection));

        if ($(viewElement)) {
            $(viewElement).value = '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__SELECTED_OBJECTS" p_bHtmlEncode=0}]'.replace('{0}', selection.length);
        }
    
        [{if $callback_accept}][{$callback_accept}][{/if}]
    } else {
        if (selection[0]) {
            $(viewElement).setValue('...');
            $(hiddenElement).setValue(selection[0]);
            
            window.browserPreselection.getObjectMetaData(selection[0], function (data) {
                $(viewElement).setValue(data.isys_obj_type__title + ' >> ' + data.isys_obj__title);
                [{if $callback_accept}][{$callback_accept}][{/if}]
            });
        }
    }

    popup_close();
};

// Initialize preselection component.
window.browserPreselection = new Browser.preselection('objectPreselection', {
    secondElement:'portList',
    ajaxURL:'[{$ajax_url}]',
    $selectionCounter:'numObjects',
    multiselection:!! parseInt('[{$multiselection}]'),
    latestLogElement:'latestLog',
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
    secondList:new Browser.portList('portList', {
        listOptions:{
            colgroup:'<colgroup><col width="80" /><col /><col width="130" /></colgroup>',
            search:false,
            filter:false,
            preselection:JSON.parse('[{$preselection|json_encode|escape:"javascript"}]'),
            objectSelectionCallback:'browserPreselection.select',
            firstSelection:false,
            secondSelection:true,
            secondSelectionExists:true,
            multiselection:!! parseInt('[{$multiselection}]'),
            quickinfo: {
                active: '[{isys_usersettings::get("gui.quickinfo.active", 1)}]'.evalJSON(),
                delay:  '[{isys_usersettings::get("gui.quickinfo.delay", 0.5)}]'.evalJSON()
            }
        }
    })
});

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

window.is_relation = function () {
    return ('groupRelationTypes' == $('leftPane').down('li.selected').readAttribute('data-filter'));
};

Browser.relationList = Class.create(Browser.objectList, {
    createFirstRow:function (obj) {
        var i_cnt = 1;
        var row = '<thead><tr>';

        this.tableColumnsName.each(function (i) {
            var style = '';

            // Set the first element to a specific width, to avoid a ugly bug in webkit-browsers.
            if (i_cnt == 1) style = ' style="width:80px;" ';
            
            if (i == '__checkbox__') {
                var all_button = '<a href="#" id="' + this.table.id + '-add_all" class="btn btn-small mr5" title="' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__ADD_ALL_BY_FILTER') + '">' +
                                 '<img src="' + window.dir_images + 'icons/silk/add.png" style="margin-right:3px;" /><span>' + idoit.Translate.get('LC__UNIVERSAL__ALL') + '</span>' +
                                 '</a>' +
                                 '<a href="#" id="' + this.table.id + '-add_page" class="btn btn-small" title="' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__ADD_ALL_ON_PAGE') + '">' +
                                 '<img src="' + window.dir_images + 'icons/silk/add.png" style="margin-right:3px;" /><span>' + idoit.Translate.get('LC__UNIVERSAL__PAGE') + '</span>' +
                                 '</a>';
                
                row += '<th id="' + this.table.id + '-' + i + '"' + style + '>' + all_button + '</th>';
            } else {
                row += '<th id="' + this.table.id + '-' + i + '"' + style + '>' + i + '</th>';
            }
            
            
            i_cnt++;
        }.bind(this));

        if (this.options.firstSelection) {
            row += '<th>' + idoit.Translate.get('LC__CMDB__CATG__RELATION') + '</th>';
        }

        row += '</tr></thead>';
        return row;
    },
    
    addAll: function () {
        var i,
            data = this.cache || this.data;
        
        if (data)
        {
            for (i in data)
            {
                if (!data.hasOwnProperty(i)) {
                    continue;
                }
             
                browserPreselection.select(Object.values(data[i])[0]);
            }
            
            this.updateTable();
        }
    },

    addButton:function (values, view) {
        if (this.options.multiselection) {
            var func_string = "browserPreselection.select(" + parseInt(values[0]) + ");" +
                "if(this.up()){this.up().update(window.browserList.removeButton(" + values[0] + ", '" + view + "'));}";

            return '<button type="button" class="btn btn-small btn-block" onclick="' + func_string + '">' +
                   '<img src="' + window.dir_images + 'icons/silk/add.png" style="margin-right:3px" />' +
                   '<span>' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__ADD') + '</span>' +
                   '</button>';
        } else {
            return '<input type="radio" name="listSelection" onclick="browserPreselection.select(' + parseInt(values[0]) + ');" />';
        }
    },

    removeButton:function (values, view) {
        if (this.options.multiselection) {
            var func_string = "browserPreselection.remove(" + values[0] + ");" +
                "if(this.up()){this.up().update(window.browserList.addButton(" + values[0] + ", '" + view + "'));}";

            return '<button type="button" class="btn btn-small btn-block" onclick="' + func_string + '">' +
                   '<img src="' + window.dir_images + 'icons/silk/delete.png" style="margin-right:3px" />' +
                   '<span>' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__REMOVE') + '</span>' +
                   '</button>';
        } else {
            return '<input type="radio" name="listSelection" checked="checked" onclick="browserPreselection.select(' + parseInt(values[0]) + ');" />';
        }
    },

    createRow:function (obj, index) {
        var values = Object.values(obj),
            tmpClassName,
            tmpContent,
            tr = new Element('tr', {'class':'data line' + (index % 2), id:this.table.id + '-' + index}).writeAttribute('data-objectid', values[0]),
            is_relation = window.is_relation();

        this.tableColumnsName.each(function (s, index) {
            if (s == '__checkbox__') {
                // We check if we are only allow to display buttons for relations.
                if (!is_relation && ('[{$relation_only}]').evalJSON() && this.options.firstSelection) {
                    tr.insert(new Element('td', {
                        className:'toolbar center'
                    }).update('-'));

                    return;
                }

                if (!browserPreselection.exists(values[0])) {
                    tmpContent = this.addButton(values, 'l');
                } else {
                    tmpContent = this.removeButton(values, 'l');
                }

                tmpClassName = this.table.id + '-column-checkbox toolbar center';
            } else {
                tmpContent = values[index];
                tmpClassName = this.table.id + '-column-' + s;
            }

            tr.insert(new Element('td', {className:tmpClassName}).update(tmpContent));
        }.bind(this));

        tr.insert(new Element('td', {className:this.table.id + '-column-checkbox toolbar center'})
            .update(new Element('button', {className:'btn btn-small btn-block', type:'button', onclick:'browserPreselection.secondSelectionCall(' + values[0] + ');'})
                .update('&raquo;')));

        if (Prototype.Browser.IE && tr.outerHTML) {
            return tr.outerHTML;
        }
        
        return tr;
    }
});

// We have to override the window.browserlist, which has already been instanced in object_ng.tpl.
window.browserList = new Browser.relationList('objectList', {
    jsonClient:idoitJSON,
    listOptions:{
        colgroup:'<colgroup><col style="width:80px;" /><col /><col style="width:130px;" /><col style="width:130px;" /></colgroup>',
        multiselection:('[{if $multiselection}]true[{else}]false[{/if}]').evalJSON(),
        firstSelection:true,
        secondSelection:false,
        secondSelectionExists:('[{$secondSelection|default:"false"}]').evalJSON(),
        objectSelectionCallback:'[{if $secondSelection}]browserPreselection.secondSelectionCall[{else}]browserPreselection.select[{/if}]',
        instanceName:'browserList',
        quickinfo: {
            active: '[{isys_usersettings::get("gui.quickinfo.active", 1)}]'.evalJSON(),
            delay:  '[{isys_usersettings::get("gui.quickinfo.delay", 0.5)}]'.evalJSON()
        }
    }
});
