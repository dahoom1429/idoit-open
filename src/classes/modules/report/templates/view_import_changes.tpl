<div class="p10" id="report_view_import_types">
	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" name="C__IMPORT_TYPES" ident="LC__REPORT__VIEW__IMPORT_CHANGES__IMPORT_TYPES"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__IMPORT_TYPES"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__IMPORT_TYPES__TIMEPERIOD'  ident="LC__REPORT__VIEW__CMDB_CHANGES__PERIOD"}]</td>
			<td class="value">
				[{isys type="f_popup" name="C__IMPORT_TYPES__TIMEPERIOD__START" p_strPopupType="calendar"}]
				[{isys type="f_popup" name="C__IMPORT_TYPES__TIMEPERIOD__END" p_strPopupType="calendar"}]
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<button type="button" id="data-loader" class="btn ml20">
					<img src="[{$dir_images}]icons/silk/database_table.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</span>
				</button>
			</td>
		</tr>
	</table>

	<div id="import_log_result" style="display:none;">
        <div class="box mt10" style="width: 400px; display:inline-table;position:relative">
            <h3 class="gradient text-shadow p5">[{isys type="lang" ident="LC__REPORT__VIEW__IMPORT_CHANGES__EXECUTED_IMPORTS"}]</h3>
            <table class="mainTable">
                <thead>
                <tr>
                    <th>
                        [{isys type="lang" ident="LC__REPORT__VIEW__IMPORT_CHANGES__DATE_IMPORT"}]
                    </th>
                    <th>
                        [{isys type="lang" ident="LC__REPORT__VIEW__IMPORT_CHANGES__FILE_PROFILE"}]
                    </th>
                </tr>
                </thead>
                <tbody id="executed_imports">
                </tbody>
            </table>
        </div>

        <div class="mt10 ml5 box" id="import_log" style="display:inline-table; border">
            <h3 class="gradient text-shadow p5">[{isys type="lang" ident="LC__REPORT__VIEW__IMPORT_CHANGES__DATA_CHANGES"}]</h3>
            <img style="display:none;" id="tpl_loader" class="fl mr5" src="images/ajax-loading.gif" />
            <div id="import_log_details"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('data-loader').on('click', function () {
        $('import_log_result').hide();
        new Ajax.Request('[{$ajax_url}]',
            {
                parameters:{
                    import_type: $('C__IMPORT_TYPES').value,
                    timeperiod_start: $('C__IMPORT_TYPES__TIMEPERIOD__START__HIDDEN').value,
                    timeperiod_end: $('C__IMPORT_TYPES__TIMEPERIOD__END__HIDDEN').value,
                    func: 'load_executed_imports'
                },
                method:"post",
                onSuccess:function (transport) {
                    var json = transport.responseJSON;
                    $('executed_imports').update('');

                    if(json.length > 0) {
                        $('import_log_result').show();
                        for (var i in json) {
                            if (json.hasOwnProperty(i)) {
                                tr_ele = new Element('tr', {className: ((i%2 == 0)? 'CMDBListElementsOdd': 'CMDBListElementsEven')});
                                tr_ele.setAttribute('import-id', json[i].id);
                                td_ele_time = new Element('td').insert(json[i].datetime);
                                td_ele_title = new Element('td').insert(json[i].title);
                                tr_ele.insert(td_ele_time).insert(td_ele_title);
                                tr_ele.on('click', function () {
                                    $('tpl_loader').show();
                                    window.load_import_log(this.getAttribute('import-id'));
                                });
                                $('executed_imports').insert(tr_ele);
                            }
                        }
                    }
                    else
                    {
                        idoit.Notify.error('[{isys type="lang" ident="LC__REPORT__VIEW__IMPORT_CHANGES__NO_RESULTS"}]');
                    }
                }
            });
    });

    window.load_import_log = function(p_import_id){
        new Ajax.Updater('import_log_details', '[{$ajax_url}]', {
            parameters:{
                import_id: p_import_id,
                func: 'load_import_changes'
            },
            method: 'post',
            onComplete: $('tpl_loader').hide()
        });
    }

</script>