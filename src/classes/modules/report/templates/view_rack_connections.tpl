<style type="text/css">
    #report_view__rack_connections div.box {
        width: 100%;
    }
</style>

<div id="report_view__rack_connections">
    <table class="contentTable">
        <tr>
            <td>
                <table>
                    <tr>
                        <td class="key">
                            [{isys type="f_label" ident="LC_UNIVERSAL__CATEGORY" name="C__VIEW_RACK_CONNECTIONS__SELECTED_CATEGORY"}]
                        </td>
                        <td class="value">
                            [{isys type="f_dialog" name="C__VIEW_RACK_CONNECTIONS__SELECTED_CATEGORY"}]
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            [{isys type="f_label" name="C__VIEW_RACK_CONNECTIONS__OBJECT_FILTER" ident="LC__REPORT__VIEW__RACKS_CONNECTIONS__FILTER_BY_OBJECTS_OPTIONAL"}]
                        </td>
                        <td class="value">
                            [{isys
                                type="f_popup"
                                name="C__VIEW_RACK_CONNECTIONS__OBJECT_FILTER"
                                p_strPopupType="browser_object_ng"}]
                        </td>
                    </tr>
                    <tr>
                        <td class="key"></td>
                        <td>
                            <a id="data-loader" class="btn ml20">
                                <img src="[{$dir_images}]icons/silk/database_table.png" class="mr5" />
                                <span>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</span>
                            </a>

                            <span id="ajax_loading_view" style="display:none;">
                                <img src="[{$dir_images}]ajax-loading.gif" alt="" class="vam"/> [{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]
                            </span>

                            <a type="application/octet-stream" href="[{$download_link}]" id="csv-downloader" class="btn ml20" style="display:none;">
                                <img src="[{$dir_images}]icons/silk/page_white_office.png" class="mr5" />
                                <span>CSV [{isys type="lang" ident="LC__CMDB__CATS__FILE_DOWNLOAD"}]</span>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <fieldset class="overview mt10">
	    <legend><span>[{isys type="lang" ident="LC__UNIVERSAL__RESULT"}]</span></legend>
        <div id="racks" class="p5" style="overflow-x:auto;"></div>
    </fieldset>
</div>
<script type="text/javascript">
    $('data-loader').on('click', function(){
	    $('ajax_loading_view').show();
	    $('report_view__rack_connections').setOpacity(0.7);
        new Ajax.Request('[{$ajax_url}]',
            {
                parameters:{
                    selectedCategory:$('C__VIEW_RACK_CONNECTIONS__SELECTED_CATEGORY').value,
                    objectFilter:$('C__VIEW_RACK_CONNECTIONS__OBJECT_FILTER__HIDDEN').value
                },
                method:'post',
                onSuccess:function(transport)
                {
	                $('report_view__rack_connections').setOpacity(1);
	                $('ajax_loading_view').hide();

                    if($('viewTable')){
                        $('viewTable').remove();
                    }
                    var json = transport.responseJSON;

                    if(json == null){
                        $('racks').update('[{$no_entries_found}]');
                        $('csv-downloader').hide();
                    } else{
                        $('racks').update('');
                        $('csv-downloader').show();
                        window.add_result_header($('C__VIEW_RACK_CONNECTIONS__SELECTED_CATEGORY').value);

                        var counter = 1;

                        json.each(function(ele){

                            var tr_ele = new Element('tr', {'class':((counter % 2 == 0)? 'CMDBListElementsOdd': 'CMDBListElementsEven')});
                            ele.each(function(ele2){
                                var td_ele = new Element('td').insert(ele2);
                                tr_ele.insert(td_ele);
                            });
                            $('viewResultListing').insert(tr_ele);
                            counter++;
                        });
                    }
                }
            }
        );
    });

    window.add_result_header = function(p_category){

        var header_list = [{$header_json}];
        var table_obj = new Element('table', {'class':'mainTable','id':'viewTable','cellspacing':0});
        var tr_header = new Element('tr');

        header_list[p_category].each(function(ele){
            var th_header = new Element('th');
            th_header.insert(ele);
            tr_header.insert(th_header);
        });
        table_obj.insert(tr_header).insert(new Element('tbody', {'id':'viewResultListing'}));
        $('racks').insert(table_obj);
    }

</script>