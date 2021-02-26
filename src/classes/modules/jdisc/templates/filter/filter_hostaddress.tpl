<script type="text/javascript" src="[{$dir_tools}]js/ajax_upload/fileuploader.js"></script>
<style>
.qq-upload-list li.qq-upload-success {
    background: url("[{$dir_images}]gradient.png") repeat-x scroll 0 0 #44FF55;
    border: 1px solid #6A9AA9;
    color: #000000;
}
.qq-upload-list li.qq-upload-fail {
    background: url("[{$dir_images}]gradient.png") repeat-x scroll 0 0 #ff4343;
    border: 1px solid #D60000;
    color: #000000;
}
</style>

<tr class="import_filter filter_hostaddress" style="display: none;">
    <td class="key">
        [{isys type='lang' ident='LC__MODULE__JDISC__IMPORT__FILTER_DEVICES_FOR_A_HOST_ADDRESS'}]
    </td>
    <td class="value" style="vertical-align: middle">
        [{isys type="f_text" name="C__MODULE__JDISC__IMPORT__FILTER__ADDRESS" tab="10" p_strClass="normal"}]
    </td>
</tr>
<!--<tr class="import_filter filter_hostaddress" style="display: none;">
    <td class="key">
Geräte nach Netzbereich filtern
    </td>
    <td class="value">
        [{isys type="f_text" name="C__MODULE__JDISC__IMPORT__FILTER__ADDRESS_RANGE_START" tab="10" p_strClass="small"}]
        -
        [{isys type="f_text" name="C__MODULE__JDISC__IMPORT__FILTER__ADDRESS_RANGE_END" tab="10" p_strClass="small" p_bInfoIconSpacer=0}]
    </td>
</tr>-->

<tr class="import_filter filter_hostaddress" style="display: none;">
    <td class="key" style="vertical-align: top;">
        [{isys type='lang' ident='LC__MODULE__JDISC__IMPORT__FILTER_DEVICES_FOR_HOST_ADDRESSES_FROM_A_FILE'}]
    </td>
    <td class="value" style="vertical-align: middle">
        <div class="mb5 p5 ml15" id="file_browser_upload_wrapper" style="width:490px;">
            <div id="file_browser_upload"></div>
            <br class="cb" />
        </div>

        <dl class="ml20 mt5">
            <dt class="bold">[{isys type='lang' ident='Info'}]:</dt>
            <dd style="margin-left: 2em;font-weight:normal">[{isys type='lang' p_bHtmlEncode="0" ident='LC__MODULE__JDISC__IMPORT__FILTER_DEVICES_FOR_IP_FROM_FILE__DESCRIPTION'}]</dd>
        </dl>
    </td>
</tr>
<script type="text/javascript">
    var uploader = new qq.FileUploader({
        element:$('file_browser_upload'),
        action:'?call=jdisc&func=read_hostaddress_from_file&ajax=1',
        multiple:false,
        autoUpload:true,
        onComplete:function(id, filename, response) {
            var defined_filter = $('module-jdisc-import-filter-data').value, ip_filter_list, new_value, ip_filter_list2, ip_filter_range;
            if(defined_filter == '' && defined_filter.indexOf('|') < 0 && defined_filter.indexOf('[') < 0)
            {
                $('module-jdisc-import-filter-data').value = response.data.join(',');
            }
            else
            {
                new_value = response.data.join(',');
                if(defined_filter.indexOf('|') > -1)
                {
                    ip_filter_list = $('module-jdisc-import-filter-data').value.split('|');
                    new_value += '|' +  ip_filter_list[1] + '|';
                }

                if(defined_filter.indexOf('[') > -1){
                    ip_filter_list = $('module-jdisc-import-filter-data').value.split('[');
                    ip_filter_list2 = ip_filter_list[1].split(']');
                    ip_filter_range = ip_filter_list2[0];
                    new_value += '[' + ip_filter_range + ']';
                }
                $('module-jdisc-import-filter-data').value = new_value;
            }
        },
        allowedExtensions: ['txt','csv'],
        dragText: '[{isys type="lang" ident="LC_FILEBROWSER__DROP_FILE"}]',
        multipleFileDropNotAllowedMessage: '[{isys type="lang" ident="LC_FILEBROWSER__SINGLE_FILE_UPLOAD"}]',
        uploadButtonText: '<img src="[{$dir_images}]icons/silk/arrow_up.png" alt="" class="vam" /> <span class="vam">[{isys type="lang" ident="LC__UNIVERSAL__FILE_ADD"}]</span>',
        cancelButtonText: '&nbsp;',
        failUploadText: '[{isys type="lang" ident="LC__UNIVERSAL__ERROR"}]'
    });

    if($('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS')){
        idoit.Require.require('validation', function () {
            var validation = new Validation($('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS'));

            $('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS').on('keyup', function(ele){
                // Disable Import Start as long as no valid IP is given
                $('C__MODULE__JDISC__IMPORT__BUTTON').disabled = true;

                var ipCheck = function(exact) {
                    var v4 = '(?:25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]\\d|\\d)(?:\\.(?:25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]\\d|\\d)){3}';

                    var v6seg = '[0-9a-fA-F]{1,4}';
                    var v6 = '((?:' + v6seg + ':){7}(?:' + v6seg + '|:)|(?:' + v6seg + ':){6}(?:' + v4 + '|:' + v6seg + '|:)|(?:' + v6seg + ':){5}(?::' + v4 + '|(:' + v6seg + '){1,2}|:)|(?:' + v6seg + ':){4}(?:(:' + v6seg + '){0,1}:' + v4 + '|(:' + v6seg + '){1,3}|:)|(?:' + v6seg + ':){3}(?:(:' + v6seg + '){0,2}:' + v4 + '|(:' + v6seg + '){1,4}|:)|(?:' + v6seg + ':){2}(?:(:' + v6seg + '){0,3}:' + v4 + '|(:' + v6seg + '){1,5}|:)|(?:' + v6seg + ':){1}(?:(:' + v6seg + '){0,4}:' + v4 + '|(:' + v6seg + '){1,6}|:)|(?::((?::' + v6seg + '){0,5}:' + v4 + '|(?::' + v6seg + '){1,7}|:)))(%[0-9a-zA-Z]{1,})?'.replace(/\s*\/\/.*$/gm, '').replace(/\n/g, '').trim();

                    return (exact ? new RegExp('(?:^' + v4 + '$)|(?:^' + v6 + '$)') : new RegExp('(?:' + v4 + ')|(?:' + v6 + ')', 'g'));
                };

                if (ipCheck(true).test(ele.findElement().value) || ele.findElement().value.length === 0) {
                    $('C__MODULE__JDISC__IMPORT__BUTTON').disabled = false;

                    validation.success();
                } else {
                    validation.fail('[{isys type="lang" ident="LC__CMDB__CATG__IP__INVALID_IP"}]');
                }

                var defined_filter = $('module-jdisc-import-filter-data').value, ip_filter_list;
                var current_value = ele.findElement().value;
                if(defined_filter == '' || defined_filter.indexOf('|') < 0)
                {
                    $('module-jdisc-import-filter-data').value = defined_filter + '|' + current_value;
                }
                else
                {
                    ip_filter_list = $('module-jdisc-import-filter-data').value.split('|');
                    if(current_value == '')
                    {
                        $('module-jdisc-import-filter-data').value = ip_filter_list[0];
                    }
                    else
                    {
                        $('module-jdisc-import-filter-data').value = ip_filter_list[0] + '|' + current_value + '|' + ((ip_filter_list[2] != undefined) ?ip_filter_list[2]: '');
                    }
                }
            });
        });
    }

    if($('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS_RANGE_START')){
        $('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS_RANGE_START').on('keyup', function(ele){
            var defined_filter = $('module-jdisc-import-filter-data').value, ip_filter_list, ip_filter_list2, ip_filter_range, ip_range, new_ip_range;
            if(defined_filter == '' || defined_filter.indexOf('[') < 0)
            {
                $('module-jdisc-import-filter-data').value =
                        defined_filter + '[' + ele.findElement().value + '-' + $('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS_RANGE_END').value + ']';
            }
            else
            {
                ip_filter_list = $('module-jdisc-import-filter-data').value.split('[');
                ip_filter_list2 = ip_filter_list[1].split(']');
                ip_filter_range = ip_filter_list2[0];
                ip_range = ip_filter_range.split('-');
                new_ip_range = '[' + ele.findElement().value + '-' + ip_range[1] + ']';
                $('module-jdisc-import-filter-data').value = ip_filter_list[0] + new_ip_range + ip_filter_list2[1];
            }
        });
    }

    if($('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS_RANGE_END')){
        $('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS_RANGE_END').on('keyup', function(ele){
            var defined_filter = $('module-jdisc-import-filter-data').value, ip_filter_list, ip_filter_list2, ip_filter_range, ip_range, new_ip_range;
            if(defined_filter == '' || defined_filter.indexOf('[') < 0)
            {
                $('module-jdisc-import-filter-data').value =
                        defined_filter + '[' + $('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS_RANGE_START').value + '-' + ele.findElement().value + ']';
            }
            else
            {
                ip_filter_list = $('module-jdisc-import-filter-data').value.split('[');
                ip_filter_list2 = ip_filter_list[1].split(']');
                ip_filter_range = ip_filter_list2[0];
                ip_range = ip_filter_range.split('-');
                new_ip_range = '[' + ip_range[0] + '-' + ele.findElement().value + ']';
                $('module-jdisc-import-filter-data').value = ip_filter_list[0] + new_ip_range + ip_filter_list2[1];
            }
        });
    }

</script>
