<div>
    <div class="p10">
        <table class="contentTable">
            <colgroup>
                <col style="width:120px;" />
            </colgroup>
            <tr>
                <td class="key">[{isys type="f_label" name="dialog_protocol" ident="LC__CMDB__CATG__NET_LISTENER__PROTOCOL"}]</td>
                <td class="value pl20">[{isys type='f_dialog' name='dialog_protocol' p_bDbFieldNN=0 chosen=1 p_strClass="input-small" p_bInfoIconSpacer=0}]</td>
            </tr>
            <tr>
                <td class="key">[{isys type="f_label" name="dialog_protocol_5" ident="LC__CMDB__CATG__NET_LISTENER__LAYER_5_PROTOCOL"}]</td>
                <td class="value pl20">[{isys type='f_dialog' name='dialog_protocol_5' p_bDbFieldNN=0 chosen=1 p_strClass="input-small" p_bInfoIconSpacer=0}]</td>
            </tr>
            <tr>
                <td class="key">[{isys type="f_label" name="dialog_net" ident="LC__CMDB__CATG__NET_LISTENER__LISTENER_NETWORK"}]</td>
                <td class="value pl20">[{isys type='f_dialog' chosen=1 name='dialog_net' p_bDbFieldNN=0 p_strClass="input-small" p_bInfoIconSpacer=0}]</td>
            </tr>
            <tr>
                <td class="key">[{isys type="f_label" name="text_port" ident="LC__CMDB__CATG__NET_LISTENER__LISTENER_PORT"}]</td>
                <td class="value">[{isys type='f_text' p_strStyle="width:40px;" name='text_port'}]</td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button type="button" id="data-loader" class="ml20 btn">
                        <img src="[{$dir_images}]icons/silk/database_table.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</span>
                    </button>
                </td>
            </tr>
        </table>
    </div>

    <fieldset class="cb overview">
        <legend><span>[{isys type="lang" ident="LC__UNIVERSAL__RESULT"}]</span></legend>

        <button type="button" id="csv-export" class="fr m10 hide btn">
            <img src="[{$dir_images}]icons/silk/page_white_office.png" class="mr5" /><span>CSV-Export</span>
        </button>
    </fieldset>

    <div id="networkConnections"></div>
</div>

<script type="text/javascript">
    (function () {
        $('csv-export').on('click', function () {
            if ($('data-grid-networkConnections')) {
                $('data-grid-networkConnections').exportTableAsCSV();
            }
        });

        $('data-loader').on('click', function () {
            $('networkConnections').update();
            $('csv-export').removeClassName('hide');

            new Ajax.Request('[{$reportViewAjaxUrl}]', {
                method:     'post',
                parameters: {
                    dialog_protocol:   $F('dialog_protocol'),
                    dialog_protocol_5: $F('dialog_protocol_5'),
                    dialog_net:        $F('dialog_net'),
                    text_port:         $F('text_port')
                },
                onSuccess:  function (xhr) {
                    if (xhr.responseJSON && Object.isArray(xhr.responseJSON)) {
                        var list = new Browser.objectList(new Element('div')),
                            header = xhr.responseJSON[0],
                            data = xhr.responseJSON.slice(1);

                        window.currentReportView = new Lists.Objects('networkConnections', {
                            max_pages:          0,
                            ajax_pager:         false,
                            ajax_pager_url:     '',
                            ajax_pager_preload: 0,
                            data:               list.performDataMatch(header, data),
                            filter:             'top',
                            paginate:           'top',
                            pageCount:          150,
                            draggable:          false,
                            checkboxes:         false
                        });
                    }
                }
            });
        });
    })();
</script>
