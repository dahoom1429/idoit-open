// Report Export
var ReportExport = {
    init:    function () {
        "use strict";
        $$('div.export-report a.export-btn').invoke('on', 'click', function () {
            ReportExport.trigger(this.getAttribute('data-report-id'), this.getAttribute('data-export-type'));
        });
    },
    trigger: function (reportID, type) {
        "use strict";
        
        window.show_overlay();
        
        var baseUrl       = window.www_dir + '?[{$smarty.const.C__GET__MODULE_ID}]=[{$smarty.const.C__MODULE__REPORT}]&export=1&report_id=' + reportID,
            matches       = $$('span.report-matches')[0],
            count         = 0,
            messageString = "[{isys type='lang' ident='LC__REPORT__EXPORT_PROCESS_TIME_WARNING' p_bHtmlEncode=0}]",
            confirmed     = true;
        
        if (matches)
        {
            count = matches.getAttribute('data-count');
            if (count > 50000)
            {
                if (!window.confirm(messageString.format(count)))
                {
                    confirmed = false;
                }
            }
        }
        
        if (confirmed)
        {
            document.location.href = baseUrl + '&type=' + type;
        }
        
        window.hide_overlay();
    }
};
ReportExport.init();

// Overwriting the TableOrder methods.
Lists.ReportList = Class.create(Lists.Objects, {
    createFirstRow: function () {
        var row = '<thead><tr>';
        
        this.tableColumnsName.each(function (i) {
            if (i != '__id__' && i != '__obj_id__')
            {
                row += '<th id="' + this.table.id + '-' + i + '">' + i + '</th>';
            }
        }.bind(this));
        
        row += '</tr></thead>';
        return row;
    },
    createRow:      function (obj, index) {
        var values = Object.values(obj);
        var tr = new Element('tr', {
            'class': 'data line' + (index % 2),
            id:      this.table.id + '-' + index
        }).writeAttribute('data-objectid', values[0]);
        
        // We don't want to display the ID field.
        this.tableColumnsName.each(function (s, index) {
            if (s != '__id__' && s != '__obj_id__')
            {
                tr.insert(new Element('td', {
                    'className': this.table.id + '-column-' + s
                }).update(this.replaceTablePlaceholders.replacePlaceholdersInCell(values[index])));
            }
        }.bind(this));
        
        if (Prototype.Browser.IE && tr.outerHTML)
        {
            return tr.outerHTML;
        }
        else
        {
            return tr;
        }
    },
    trClick:        function (e) {
        if (this.options.tr_click)
        {
            var object_id = parseInt(e.findElement().up().readAttribute('data-objectid'));
            
            if (Object.isNumber(object_id) && object_id > 0)
            {
                document.location.href = '?objID=' + object_id;
            }
        }
    },
    createFilter:   function () {
        var option = '';
        this.tableColumnsName.each(function (i) {
            if (i != '__id__' && i != '__obj_id__')
            {
                option += '\t<option value="' + i + '">' + i.replace('_', ' ') + '</option>\n';
            }
        });
        
        $(this.table.id + '-options')
            .insert({bottom: this.msgs.filterLabel})
            .insert({bottom: '<select id="' + this.table.id + '-filter-column" class="input input-mini">' + option + '</select>'})
            .insert({
                bottom: new Element('input', {
                    'id':      this.table.id + '-filter-data',
                    className: 'input input-mini'
                })
            });
        
        this.tools.filterCol = $F(this.table.id + '-filter-column');
        this.tools.filterData = $F(this.table.id + '-filter-data');
    }
});

window.build_table = function (name, data, ajax_pager, ajax_pager_url, ajax_pager_preload, max_pages, additionalListParameters) {
    
    if (typeof ajax_pager == 'undefined')
    {
        ajax_pager = false;
        ajax_pager_url = '';
        ajax_pager_preload = 0;
        max_pages = 0;
    }
    
    var page_counter = '[{isys_glob_get_pagelimit()}]';
    
    // Default list configuration parameter
    var defaultListConfiguration = {
        max_pages:          max_pages, // ceil($l_obj_type_rows / $g_page_limit)
        ajax_pager:         ajax_pager,
        ajax_pager_url:     ajax_pager_url, // "?ajax=1&call=object_list&func=load_objtype_list&dao=' . get_class($this->m_dao_list) . '&object_type=' . (int) isys_glob_get_param(C__CMDB__GET__OBJECTTYPE) . '"
        ajax_pager_preload: ajax_pager_preload, // (int) isys_usersettings::get('gui.lists.preload-pages', 30)
        data:               data,
        filter:             "top",
        paginate:           "top",
        pageCount:          page_counter,
        draggable:          false,
        checkboxes:         false
    };
    
    // Merge list configuration parameter
    var listConfiguration = Object.extend(defaultListConfiguration, additionalListParameters || {});
    
    // Creating a new ObjectTypeList instance for the list.
    window.objecttype_list = new Lists.ReportList(name, listConfiguration);
    
    // Create a new object group with the objects of the current report.
    if ($('createObjectGroup'))
    {
        $('createObjectGroup').on('click', function () {
            
            var report_title = $('report-title').innerHTML;
            var objects_from_report = [];
            
            window.objecttype_list.cache.each(function (ele) {
                objects_from_report.push(parseInt(ele['__id__']));
            });
            
            objects_from_report = Object.toJSON(objects_from_report);
            idoitJSON.createObjectGroup(report_title, objects_from_report, true, function () {
                idoit.Notify.success(report_title + " " + "[{isys type='lang' ident='LC__LOGBOOK__OBJECT_CREATED'}]");
            });
        });
    }
    
};

