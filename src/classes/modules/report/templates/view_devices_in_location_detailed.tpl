<style type="text/css">
    #report_view__devices_in_location div.box {
        width: 100%;
    }
</style>

<div class="p10" id="report_view__devices_in_location">
    <table class="contentTable">
        <tr>
            <td class="key"></td>
            <td class="value">
                <p class="ml20 mb5">[{isys type="lang" ident="LC__REPORT__VIEW__DEVICES_IN_LOCATION_TEXT"}]</p>
            </td>
        </tr>
        <tr>
            <td class="key">[{isys type="f_label" name="C__CONTAINER_OBJECT" ident="LC__CMDB__CATG__LOGICAL_UNIT__PARENT"}]</td>
            <td class="value">[{isys type="f_popup" name="C__CONTAINER_OBJECT" p_strPopupType="browser_location" edit="1" containers_only=true}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type="f_label" name="C__VIEW__OBJTYPE__DIALOG_LIST" ident="LC_UNIVERSAL__FILTERS"}]</td>
            <td class="value">[{isys type="f_dialog_list" name="C__VIEW__OBJTYPE__DIALOG_LIST"}]</td>
        </tr>
        <tr>
            <td class="key"></td>
            <td>
                <button type="button" id="data-loader" class="btn ml20">
                    <img src="[{$dir_images}]icons/silk/database_table.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</span>
                </button>

                <button type="button" id="data-exporter" class="btn ml5">
                    <img src="[{$dir_images}]icons/silk/page_white_office.png" class="mr5" /><span>CSV Download</span>
                </button>
            </td>
        </tr>
    </table>
</div>
<div>
    <h3 class="p5 gradient border-top border-bottom">[{isys type="lang" ident="LC__UNIVERSAL__RESULT"}]</h3>

    <div id="devices_in_location" style="overflow-x:auto;"></div>
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        var $locationObjectField   = $('C__CONTAINER_OBJECT__HIDDEN'),
            $objectTypeFilterField = $('C__VIEW__OBJTYPE__DIALOG_LIST__selected_box'),
            $load_button           = $('data-loader'),
            $dataCSVExportButton   = $('data-exporter').addClassName('hide'),
            $result                = $('devices_in_location');

        $dataCSVExportButton.on('click', function () {
            document.location.href = '[{$ajax_url}]&obj_id=' + $locationObjectField.getValue() + '&objTypeFilter=' + $objectTypeFilterField.getValue() + '&export=1';
        });

        $load_button.on('click', function () {
            var obj_id     = $locationObjectField.getValue(),
                typefilter = $objectTypeFilterField.getValue();

            if (obj_id > 0) {
                $dataCSVExportButton.addClassName('hide');

                $load_button
                    .down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
                    .next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

                new Ajax.Request('[{$ajax_url}]', {
                        parameters: {
                            obj_id:        obj_id,
                            objTypeFilter: Object.toJSON(typefilter)
                        },
                        method:     "post",
                        onComplete: function (transport) {
                            var json = transport.responseJSON, i, i2;

                            is_json_response(transport, true);

                            $load_button
                                .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/database_table.png')
                                .next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]');

                            $result.update();

                            if (!json) {
                                $result.insert(new Element('span').insert('[{isys type="lang" ident="LC__UNIVERSAL__NO_OBJECTS_FOUND"}]'));
                            } else {
                                var $table = new Element('table', {className: 'mainTable'}),
                                    $thead = new Element('thead'),
                                    $tbody = new Element('tbody'),
                                    $tr;

                                for (i in json.headLine) {
                                    if (json.headLine.hasOwnProperty(i)) {
                                        $thead.insert(new Element('th').update(json.headLine[i]));
                                    }
                                }

                                for (i in json.lineValues) {
                                    if (json.lineValues.hasOwnProperty(i)) {
                                        $tr = new Element('tr', {className: (i % 2 ? 'line0' : 'line1')});

                                        for (i2 in json.lineValues[i]) {
                                            if (json.lineValues[i].hasOwnProperty(i2) && i2 != '__objid__') {
                                                $tr.insert(new Element('td').update(json.lineValues[i][i2]))
                                            }
                                        }

                                        $tbody.insert($tr);
                                    }
                                }

                                $dataCSVExportButton.removeClassName('hide');
                                $result.insert($table.insert($thead).insert($tbody));
                            }
                        }
                    }
                );
            }
        });
    })();
</script>